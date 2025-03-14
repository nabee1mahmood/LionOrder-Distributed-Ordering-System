<?php
session_start();

$custID = $_SESSION["custID"];
$api_url = "http://lion-customers-api-1:8000/customers/update/$custID";

// Fetch customer data
$customer_url = "http://lion-customers-api-1:8000/customers/$custID";
$customer_response = @file_get_contents($customer_url);
$customer_data = json_decode($customer_response, true);



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_data = [
        "fname" => $_POST["fname"] ?? $customer_data["fname"],
        "lname" => $_POST["lname"] ?? $customer_data["lname"],
        "user" => $_POST["user"] ?? $customer_data["user"],
        "email" => $_POST["email"] ?? $customer_data["email"],
   	
    ];
    if (!empty($_POST["password1"])) {
        $update_data["pw"] = $_POST["password1"];
    }
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);
    $api_response = json_decode($response, true);

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <img src="farmlogo.png" alt="Logo" class="rounded-circle" width="50">&nbsp;&nbsp;
            <a class="navbar-brand" href="#">The Local Farm Company</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
		<ul class="navbar-nav ms-auto">
		    <li class="menu me-3"><a class="nav-link" href="manageaccount.php" style="color: white; font-size: 18px;">Reload</a></li>
		    <li class="menu me-3"><a class="nav-link" href="order.php" style="color: white;">Return to Orders</a></li>
                    <li class="menu me-3"><a class="nav-link" href="login.php" style="color: white;">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Account Management Form -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Manage Your Account</h1>
        <p class="text-muted text-center">Updating account for <strong><?php echo htmlspecialchars($customer_data["user"]); ?></strong></p>

        <form action="manageaccount.php" method="POST" class="p-4 border rounded bg-white">
            <fieldset>
                <legend class="mb-3">Account Information</legend>

                <div class="mb-3">
                    <label for="fname" class="form-label">First Name:</label>
                    <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($customer_data["fname"]); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="lname" class="form-label">Last Name:</label>
                    <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($customer_data["lname"]); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="user" class="form-label">Username:</label>
                    <input type="text" id="user" name="user" class="form-control" value="<?php echo htmlspecialchars($customer_data["user"]); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer_data["email"]); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="pw" class="form-label">Password:</label>
                    <input type="password" id="pw" name="pw" class="form-control" placeholder="Enter new password">
                    <small class="text-muted">Leave blank if you do not want to change the password.</small>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3">Save Changes</button>
            </fieldset>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

