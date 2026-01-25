<?php
session_start();
include "db.php";

$message = "";

// 1. Access Control: Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

// 2. Validate Movie ID: Redirect if no movie is selected
if(!isset($_GET['movie_id'])){
    header("Location: dashboard.php");
    exit();
}

$movie_id = $_GET['movie_id'];

// 3. Handle Form Submission (INSERT Logic)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['review']) && trim($_POST['review']) !== '') {
        $review = trim($_POST['review']);
        $movie_title = $_POST['movie_title']; 
        $user_id = $_SESSION['user_id'];      
        $movie_id = $_POST['movie_id'];        
        
        // Prepare the SQL statement for the activity history table
        $stmt = $conn->prepare("INSERT INTO tbl_movie_review (user_id, movie_id, movie_title, review) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $movie_id, $movie_title, $review);
        
        if($stmt->execute()){
            $message = "Review submitted successfully!";
        } else {
            $message = "Failed to submit review: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Please write a review before submitting.";
    }
}

// 4. Fetch Movie Data from TMDB API
$api_key = "1163142a130a2e01a5fb73752ac05995";
$tmdb_url = "https://api.themoviedb.org/3/movie/$movie_id?api_key=$api_key&language=en-US";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tmdb_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$movie_json = curl_exec($ch);
curl_close($ch);

$movie = json_decode($movie_json, true);

// Fallback if API fails
if(!$movie || isset($movie['status_code'])) {
    $movie = [
        'title' => 'Unknown Movie',
        'poster_path' => null
    ];
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
            min-height: 100vh;
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
        }

        h1 { font-size: 22px; margin-bottom: 15px; }

        img {
            width: 150px;
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
            font-family: inherit;
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
            transition: 0.3s;
        }

        button:hover { background: #ff4b2b; }

        .message {
            margin-top: 15px;
            font-weight: 600;
        }

        .success { color: #28a745; }
        .warning { color: #dd353d; }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #dd353d;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Review for <?= htmlspecialchars($movie['title']); ?></h1>
    
    <?php if($movie['poster_path']): ?>
        <img src="https://image.tmdb.org/t/p/w300<?= $movie['poster_path']; ?>" alt="Poster">
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="movie_id" value="<?= $movie_id; ?>">
        <input type="hidden" name="movie_title" value="<?= htmlspecialchars($movie['title']); ?>">
        
        <textarea name="review" placeholder="Write your thoughts about this movie..." required></textarea>
        <button type="submit">Submit Review</button>
    </form>

    <?php if($message): ?>
        <p class="message <?= (strpos($message, 'successfully') !== false) ? 'success' : 'warning' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>