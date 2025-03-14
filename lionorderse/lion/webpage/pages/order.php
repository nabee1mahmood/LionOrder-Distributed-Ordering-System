<?php

session_start(); 


// Fetch inventory and warehouse data
$inventory_api_url = "http://lion-inventory-api-1:8000/inventory/all";
$warehouse_api_url = "http://lion-warehouse-api-1:8000/warehouse/all";

$inventory_response = @file_get_contents($inventory_api_url);
$warehouse_response = @file_get_contents($warehouse_api_url);



$inventory_data = json_decode($inventory_response, true);
$warehouse_data = json_decode($warehouse_response, true);



// Merge inventory & warehouse stock
$items = [];
foreach ($inventory_data as $inventory_item) {
    $upc = $inventory_item["UPC"];
    $inventory_qty = $inventory_item["quantity"];

    // Find warehouse stock for matching UPC
    $warehouse_qty = 0;
    foreach ($warehouse_data as $warehouse_item) {
        if ($warehouse_item["upc"] === $upc) {
            $warehouse_qty = $warehouse_item["qty"];
            break;
        }
    }

    $total_qty = $inventory_qty + $warehouse_qty;

    $items[] = [
        "itemName" => $inventory_item["itemName"],
        "UPC" => $upc,
        "available_qty" => $total_qty,
        "price" => $inventory_item["price"]
    ];
}

// order Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $custID = $_SESSION["custID"];
    $orderContents = $_POST['order'] ?? [];

    if (empty($orderContents)) {
        echo "<p style='color:red;'>No items selected.</p>";
        exit();
    }

    $formattedOrderContents = [];
    foreach ($orderContents as $upc => $qty) {
        if ((int)$qty > 0) {
            $formattedOrderContents[] = [
                "UPC" => $upc,
                "quantity" => (int)$qty
            ];
        }
    }

    $api_url = "http://lion-orders-api-1:8000/orders/insert";
    $data = json_encode([
        "custID" => $custID,
        "orderStatus" => "Processing",
        "orderContents" => $formattedOrderContents
    ]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    $orderResponse = json_decode($response, true);

    if (!isset($orderResponse["orderID"])) {
        echo "<p style='color:red;'>Failed to place order.</p>";
        exit();
    }

    echo "<p style='color:green;'>Order placed successfully! Your order ID is: " . $orderResponse["orderID"] . "</p>";

    // Update Inventory & Warehouse Transfers
    foreach ($formattedOrderContents as $item) {
        $upc = $item["UPC"];
        $quantityOrdered = (int)$item["quantity"];

        // Fetch item data by UPC
        $item_lookup_url = "http://lion-inventory-api-1:8000/inventory/getByUPC/" . urlencode($upc);
        $itemData = json_decode(@file_get_contents($item_lookup_url), true);

        if (!$itemData || !isset($itemData["itemID"])) {
            echo "<p style='color:red;'>Item with UPC $upc not found in inventory.</p>";
            continue;
        }

        $itemID = $itemData["itemID"];

        // Check Inventory Level
        $inventory_url = "http://lion-inventory-api-1:8000/inventory/check/" . urlencode($itemID);
        $inventoryData = json_decode(@file_get_contents($inventory_url), true);

        if (!$inventoryData) {
            echo "<p style='color:red;'>Failed to retrieve inventory data for item $itemID.</p>";
            continue;
        }

        $inventoryAvailable = $inventoryData["available_quantity"] ?? 0;
        $newInventory = $inventoryAvailable - $quantityOrdered;

        // Transfer from Warehouse if inventory goes negative
        while ($newInventory < 0) {
            $warehouse_url = "http://lion-warehouse-api-1:8000/warehouse/qty/" . urlencode($upc);
            $warehouseData = json_decode(@file_get_contents($warehouse_url), true);

            if (!$warehouseData) {
                echo "<p style='color:red;'>Failed to retrieve warehouse stock for $upc.</p>";
                break;
            }

            $warehouseQty = $warehouseData["quantity"] ?? 0;
            if ($warehouseQty <= 0) {
                echo "<p style='color:red;'>Not enough stock available in warehouse for $upc.</p>";
                break;
            }

            $transferQty = min(50, $warehouseQty);
            $newInventory += $transferQty;

            // Update Warehouse Stock
            $updateWarehouse = ["qty" => $warehouseQty - $transferQty];
            $ch = curl_init("http://lion-warehouse-api-1:8000/warehouse/updateQty/$upc");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateWarehouse));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_exec($ch);
            curl_close($ch);
        }

        // Final Inventory Update
        $updateInventory = ["quantity" => max(0, $newInventory)];
        $ch = curl_init("http://lion-inventory-api-1:8000/inventory/update/$itemID");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateInventory));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_exec($ch);
        curl_close($ch);
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <img src="farmlogo.png" alt="Logo" class="rounded-circle" width="50">&nbsp;&nbsp;
            <a class="navbar-brand" style="font-size: 23px;" href="#">The Local Farm Company</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
		<ul class="navbar-nav ms-auto">
		    <li class="menu me-3"><a class="nav-link" href="order.php" style="color: white; font-size: 18px;">Reload</a></li>
                    <li class="menu me-3"><a class="nav-link" href="pastorders.php" style="color: white; font-size: 18px;">Past Orders</a></li>
                    <li class="menu me-3"><a class="nav-link" href="manageaccount.php" style="color: white; font-size: 18px;">Manage Account</a></li>
                    <li class="menu me-3"><a class="nav-link" href="login.php" style="color: white; font-size: 18px;">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Order Form -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Build Your Salad!</h1>

        <form action="order.php" method="POST">
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Item</th>
                        <th>UPC</th>
                        <th>Available Quantity</th>
                        <th>Price</th>
                        <th>Quantity to Order</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item["itemName"]); ?></td>
                                <td><?php echo htmlspecialchars($item["UPC"]); ?></td>
                                <td><?php echo $item["available_qty"]; ?></td>
                                <td>$<?php echo number_format($item["price"], 2); ?></td>
                                <td>
                                    <input type="number" name="order[<?php echo htmlspecialchars($item["UPC"]); ?>]" class="form-control" min="0" max="<?php echo $item["available_qty"]; ?>" value="0">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-danger">No items available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-success w-100 mt-3">Place Order</button>
        </form>
    </div>

</body>

</html>
