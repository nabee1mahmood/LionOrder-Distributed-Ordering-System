<?php
session_start();  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['pass'] ?? '';

    // API call to validate login
    $api_url = "http://lion-customers-api-1:8000/customers/authenticate";
    $data = json_encode(["email" => $email, "password" => $password]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Get HTTP status code
    $curl_error = curl_error($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_status == 200 && $result && isset($result["user_id"])) {  
        $_SESSION["custID"] = $result["user_id"];  // Store user_id in session
        header("Location: order.php");  
        exit();
    } else {
        echo '<p style="color: red;">Invalid email or password. Please try again.</p>';

        
    }
}
?>



<!-- HTML CODE -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Local Farming Company</title>

    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style CSS Connection -->
    <link rel="stylesheet" href="login.css">
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <h1 class="title">The Local Farming Company</h1>

        <form action="login.php" method="post" class="p-4 border rounded bg-white shadow-sm" style="width: 100%; max-width: 420px;">
            <div class="text-center mb-3">
                <img src="farmlogo.png" alt="Logo" class="rounded-circle" width="150">
            </div>

            <div class="email">
                <label for="email" class="emaillabel"><b>Email</b></label>
                <input type="email" class="form-control" placeholder="Enter your Email" name="email" required>
            </div>

            <div class="password">
                <label for="pass" class="passlabel"><b>Password</b></label>
                <input type="password" class="form-control" placeholder="Enter your Password" name="pass" required>
            </div>

            <?php if (!empty($error_message)): ?>
                <p style="color: red;"><?= $error_message ?></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 mb-2" style="background-color: green; color: white;">Login</button>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" checked name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary">Cancel</button> <a href="createaccount.php" class="text-decoration-none">Create account</a>
                <a href="forgotpassword.php" class="text-decoration-none">Forgot password?</a>
            </div>
        </form>
    </div>
</body>

</html>
