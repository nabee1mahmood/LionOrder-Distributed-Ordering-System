<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $data = json_encode([
        "fname" => $fname,
        "lname" => $lname,
        "email" => $email,
        "user" => $username,
        "pw" => $password
    ]);

    $api_url = "http://lion-customers-api-1:8000/customers/";
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {

        header("Location: login.php");
        exit();
    } else {
        echo "<p style='color:red;'>Failed to create account. Please try again.</p>";
    }


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Sign Up</h1>

        <form action="createaccount.php" method="post">
            <div class="mb-3">
                <label for="fname" class="form-label">First Name:</label>
                <input type="text" name="fname" class="form-control" id="fname" required>
            </div>

            <div class="mb-3">
                <label for="lname" class="form-label">Last Name:</label>
                <input type="text" name="lname" class="form-control" id="lname" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" id="email" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php">Already have an account? Login here.</a>
        </div>
    </div>
</body>
</html>

