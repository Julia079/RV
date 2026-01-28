<?php
session_start();
include "db.php";

$message = "";
$message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows == 0){
        $message = "Invalid email or password";
        $message_class = "warning";
    } else {
        $stmt->bind_result($id, $username, $hashed);
        $stmt->fetch();

        if(password_verify($password, $hashed)){
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username; 

            header("Location:dashboard.php");
            exit();
        } else {
            $message = "Invalid email or password";
            $message_class = "warning";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
