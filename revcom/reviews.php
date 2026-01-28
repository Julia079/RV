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
<title>Write Review - <?= htmlspecialchars($movie['title']); ?></title>
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
<style>
    body {
        background: url('assets/background.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Montserrat', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        color: #fff;
    }

    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: -1;
    }

    .container {
        background-color: #fff;
        color: #000;
        width: 450px;
        max-width: 95%;
        padding: 30px 25px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        position: relative;
    }

    h1 {
        font-size: 22px;
        margin-bottom: 15px;
    }

    img {
        width: 200px;
        height: auto;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    form textarea {
        width: 100%;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        font-size: 14px;
        resize: vertical;
        min-height: 120px;
        transition: border 0.3s ease;
    }

    form textarea:focus {
        border-color: #dd353d;
        outline: none;
    }

    button {
        width: 100%;
        padding: 14px;
        border-radius: 25px;
        border: none;
        background: #dd353d;
        color: #fff;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
        margin-top: 20px;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    button:hover {
        background: #ff4b2b;
        transform: translateY(-2px);
    }

    a {
        display: inline-block;
        margin-top: 15px;
        color: #dd353d;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    a:hover {
        color: #ff4b2b;
    }

    .message {
        margin-top: 15px;
        font-size: 15px;
        font-weight: 500;
    }

    .success {
        color: #28a745;
    }

    .warning {
        color: #dd353d;
    }

    a.back-link {
    display: block;       
    text-align: center;   
    margin-top: 25px;      
    color: #dd353d;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

a.back-link:hover {
    color: #ff4b2b;
}

</style>
</head>
<body>
<div class="container">
    <h1>Review for <?= htmlspecialchars($movie['title']); ?></h1>
    <img src="https://image.tmdb.org/t/p/w300<?= $movie['poster_path']; ?>" alt="<?= htmlspecialchars($movie['title']); ?>">


<a href="dashboard.php" class="back-link">Back to Dashboard</a>

</div>
</body>
</html>
