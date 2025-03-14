<?php
session_start();

$custID = $_SESSION["custID"];

// Fetch past orders from the API
$orders_api_url = "http://lion-orders-api-1:8000/orders/customer/" . urlencode($custID);
$orders_response = @file_get_contents($orders_api_url);

if ($orders_response === false) {
    echo "<p style='color:red;'>Error fetching past orders from API.</p>";
    exit();
}

$orders_data = json_decode($orders_response, true);

$item_names = [];


if (!empty($orders_data)) {
    foreach ($orders_data as $order) {
        if (is_array($order["orderContents"])) {
            foreach ($order["orderContents"] as $item) {
                $upc = $item["UPC"];

                // Fetch item name only if not already retrieved
                if (!isset($item_names[$upc])) {
                    $inventory_url = "http://lion-inventory-api-1:8000/inventory/getByUPC/" . urlencode($upc);
                    $inventory_response = @file_get_contents($inventory_url);
                    $inventory_data = json_decode($inventory_response, true);

                    if ($inventory_data && isset($inventory_data["itemName"])) {
                        $item_names[$upc] = htmlspecialchars($inventory_data["itemName"]);
                    } else {
                        $item_names[$upc] = "Unknown Item";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Local Farm Company - Past Orders</title>
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
                    <li class="menu me-3"><a class="nav-link" href="order.php" style="color: white; font-size: 18px;">Order Screen</a></li>
                    <li class="menu me-3"><a class="nav-link" href="manageaccount.php" style="color: white; font-size: 18px;">Manage Account</a></li>
                    <li class="menu me-3"><a class="nav-link" href="login.php" style="color: white; font-size: 18px;">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <header class="text-center mb-4">
            <h1>Your Past Orders</h1>
        </header>

        <?php if (empty($orders_data)): ?>
            <p class="text-center text-danger">No past orders found.</p>
        <?php else: ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Ordered Date</th>
                        <th>Order Number</th>
                        <th>Order Contents</th>
                        <th>Order Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders_data as $order): ?>
                        <tr>
                            <td><?php echo date("F j, Y", strtotime($order["orderDate"])); ?></td>
                            <td><?php echo htmlspecialchars($order["orderID"]); ?></td>
                            <td>
                                <ul>
                                    <?php 
                                    if (is_array($order["orderContents"])) {
                                        foreach ($order["orderContents"] as $item) {
                                            $upc = htmlspecialchars($item["UPC"]);
                                            $qty = htmlspecialchars($item["quantity"]);
                                            $item_name = $item_names[$upc] ?? "Unknown Item";
                                            echo "<li>$item_name | Qty: $qty</li>";
                                        }
                                    } else {
                                        echo "Invalid order contents format.";
                                    }
                                    ?>
                                </ul>
                            </td>
                            <td><?php echo htmlspecialchars($order["orderStatus"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

