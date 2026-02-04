<?php
session_start();
include "db.php";

$signup_message = "";
$signup_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($username) || empty($name) || empty($email) || empty($password)){
        $signup_message = "All fields are required";
        $signup_class = "warning";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $signup_message = "Username or Email already exists";
            $signup_class = "warning";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(username, name, email, password) VALUES(?,?,?,?)");
            $stmt->bind_param("ssss", $username, $name, $email, $hashed);

        if($stmt->execute()){
                $signup_message = "Sign Up successful! You can now Sign In.";
                $signup_class = "success";
        } else {
                $signup_message = "Sign Up failed. Try again.";
                $signup_class = "warning";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In - REVCOM</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page"> <div class="container-login" id="container"> </div>

<script src="script.js"></script>
</body>
</html>