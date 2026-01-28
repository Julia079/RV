<?php
session_start();
include "db.php";

$message = "";
$message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($username) || empty($name) || empty($email) || empty($password)){
        $message = "All fields are required";
        $message_class = "warning";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $message = "Username or Email already exists";
            $message_class = "warning";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(username, name, email, password) VALUES(?,?,?,?)");
            $stmt->bind_param("ssss", $username, $name, $email, $hashed);

            if($stmt->execute()){
                $message = "Sign Up successful! You can now Sign In.";
                $message_class = "success";
            } else {
                $message = "Sign Up failed. Try again.";
                $message_class = "warning";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - REVCOM</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" id="container">
    <div class="form-container sign-up-container">
        <form action="" method="POST">
            <h1>Create Account</h1>
            <input type="text" placeholder="Username" name="username" required
                   pattern="[A-Za-z0-9_]{3,20}" 
                   title="3-20 characters: letters, numbers, underscores"/>
            <input type="text" placeholder="Name" name="name" required
                   pattern="[A-Za-z\s]{3,50}" title="3-50 letters and spaces only"/>
            <input type="email" placeholder="Email" name="email" required/>
            <input type="password" placeholder="Password" name="password" required/>

            <?php if(!empty($message)): ?>
                <div class="message <?= $message_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <button type="submit">Sign Up</button>
        </form>
        <a href="index.php">Back to Sign In</a>
    </div>
</div>
</body>
</html>
