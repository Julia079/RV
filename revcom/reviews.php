<?php
session_start();
include "db.php";

$message = "";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if(!isset($_GET['movie_id'])){
    header("Location: dashboard.php");
    exit();
}

$movie_id = $_GET['movie_id'];

$api_key = "1163142a130a2e01a5fb73752ac05995";
$tmdb_url = "https://api.themoviedb.org/3/movie/$movie_id?api_key=$api_key&language=en-US";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tmdb_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$movie_json = curl_exec($ch);
curl_close($ch);

$movie = json_decode($movie_json, true);

if(!$movie || isset($movie['status_code'])) {
    $movie = [
        'title' => 'Unknown Movie',
        'poster_path' => null
    ];
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $review_text = trim($_POST['review']);
    $user_id = $_SESSION['user_id'];

    if(!empty($review_text)){
        $stmt = $conn->prepare("INSERT INTO tbl_movie_review (user_id, movie_id, review) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $movie_id, $review_text);
        if($stmt->execute()){
            $message = "Review submitted successfully!";
        } else {
            $message = "Failed to submit review.";
        }
        $stmt->close();
    } else {
        $message = "Please write a review before submitting.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reviews - <?= htmlspecialchars($movie['title']); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css"> </head>
<body class="reviews-page"> <a href="dashboard.php" class="back-link-float">
    <i class="fa-solid fa-house"></i> Dashboard
</a>

<div class="page-wrapper">
    <div class="main-layout">
        <div class="review-form-container"> </div>

        <div class="comments-container">
             </div>
    </div>
    
    <?php if(!empty($similar_movies)): ?>
        <?php endif; ?>
</div>

</body>
</html>