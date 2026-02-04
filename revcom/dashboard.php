<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$api_key = "1163142a130a2e01a5fb73752ac05995";
$tmdb_url = "https://api.themoviedb.org/3/movie/popular?api_key=$api_key&language=en-US&page=1";

$movies_json = file_get_contents($tmdb_url);
$movies = json_decode($movies_json, true)['results'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>REVCOM - Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css"> </head>
<body class="dashboard-page"> <header>
    </header>

<?php if (!empty($search_query)): ?>
   <?php else: ?>
   <?php endif; ?>

<script src="script.js"></script>
</body>
</html>