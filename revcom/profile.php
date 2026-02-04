<?php
session_start();
include "db.php";

// 1. Access Control: Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

// 2. Identify whose profile to view (default to logged-in user if no ID in URL)
$profile_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
$is_owner = ($_SESSION['user_id'] == $profile_id);

// 3. Fetch User Details
$stmt = $conn->prepare("SELECT username, name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 4. Privacy View: Mask sensitive info if the viewer is NOT the owner
$display_name = $is_owner ? $user['name'] : "Private User";
$display_email = $is_owner ? $user['email'] : "********@email.com";

// 5. Fetch Activity History (Reviews)
$activity_stmt = $conn->prepare("SELECT movie_title, review, created_at FROM tbl_movie_review WHERE user_id = ? ORDER BY created_at DESC");
$activity_stmt->bind_param("i", $profile_id);
$activity_stmt->execute();
$activities = $activity_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="profile-page">
    <div class="profile-container"> <h1><?= htmlspecialchars($user['username']); ?>'s Profile</h1>
        
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
</body>
</html>