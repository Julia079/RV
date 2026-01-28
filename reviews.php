<?php
session_start();
include "db.php";

$message = "";

// 1. Access Control
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

// 2. Validate Movie ID
if(!isset($_GET['movie_id'])){
    header("Location: dashboard.php");
    exit();
}

$movie_id = $_GET['movie_id'];
$user_id = $_SESSION['user_id'];

// 3. Handle Form Submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['review']) && trim($_POST['review']) !== '') {
        $review = trim($_POST['review']);
        $movie_title = $_POST['movie_title']; 
        
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

// 4. Fetch Reviews (JOIN Users Table)
$sql = "SELECT r.review, r.created_at, u.username 
        FROM tbl_movie_review r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.movie_id = ? 
        ORDER BY r.created_at DESC";

$stmt_fetch = $conn->prepare($sql);
$stmt_fetch->bind_param("i", $movie_id);
$stmt_fetch->execute();
$result_reviews = $stmt_fetch->get_result();
$existing_reviews = [];
while($row = $result_reviews->fetch_assoc()) {
    $existing_reviews[] = $row;
}
$stmt_fetch->close();

// 5. Fetch Movie Data
$api_key = "1163142a130a2e01a5fb73752ac05995";
$tmdb_url = "https://api.themoviedb.org/3/movie/$movie_id?api_key=$api_key&language=en-US";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tmdb_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$movie_json = curl_exec($ch);
curl_close($ch);

$movie = json_decode($movie_json, true);

if(!$movie || isset($movie['status_code'])) {
    $movie = ['title' => 'Unknown Movie', 'poster_path' => null];
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
    
    <style>
        body {
            background: url('assets/background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            color: #fff;
            padding: 40px;
            box-sizing: border-box;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: -1;
        }

        /* --- SIDE-BY-SIDE LAYOUT --- */
        .wrapper {
            display: flex;
            gap: 30px;
            max-width: 1100px;
            width: 100%;
            align-items: flex-start; /* Aligns top edges */
        }

        /* LEFT SIDE: Write Review Form */
        .form-container {
            flex: 1; /* Takes up 1 part of space */
            background-color: #fff;
            color: #000;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: sticky;
            top: 20px; /* Keeps it visible if page is tall */
        }

        /* RIGHT SIDE: Community Reviews List */
        .comments-container {
            flex: 1.5; /* Takes up 1.5 parts (Wider) */
            background-color: rgba(255, 255, 255, 0.95);
            color: #000;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            max-height: 80vh; /* Limits height to screen size */
            overflow-y: auto; /* Adds scrollbar inside this box only */
            display: flex;
            flex-direction: column;
        }

        h1 { 
            font-family: 'Quicksand', sans-serif;
            font-size: 22px; 
            font-weight: 800;
            margin: 10px 0;
            color: #210b0c;
        }
        
        img.poster {
            width: 120px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            margin-bottom: 15px;
        }

        form textarea {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            border: 2px solid #eee;
            background: #f9f9f9;
            box-sizing: border-box;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
            font-family: 'Quicksand', sans-serif;
            outline: none;
            transition: 0.3s;
        }

        form textarea:focus {
            border-color: #dd353d;
            background: #fff;
        }

        button {
            width: 100%;
            padding: 12px;
            border-radius: 50px;
            border: none;
            background: linear-gradient(135deg, #dd353d 0%, #b02a30 100%);
            color: #fff;
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(221, 53, 61, 0.4);
        }

        button:hover { 
            background: linear-gradient(135deg, #ff4d55 0%, #dd353d 100%);
            transform: translateY(-2px);
        }

        .message { margin-top: 15px; font-weight: 600; font-size: 14px; }
        .success { color: #28a745; }
        .warning { color: #dd353d; }

        /* COMMENTS STYLING */
        .comments-header {
            font-family: 'Quicksand', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #dd353d;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            position: sticky;
            top: 0;
            background: rgba(255,255,255,0.95); /* Keeps header readable over scrolling text */
            z-index: 10;
        }

        .single-review {
            background: #f4f4f4;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
            border-left: 4px solid #dd353d;
        }

        .user-name {
            font-weight: 800;
            color: #210b0c;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .review-date {
            color: #888;
            font-weight: 400;
            font-size: 11px;
        }

        .empty-state {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 40px;
        }

        /* BACK BUTTON (Floating Top Left) */
        .back-link-float {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.6);
            padding: 10px 20px;
            border-radius: 50px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            backdrop-filter: blur(5px);
            transition: 0.3s;
            z-index: 100;
        }
        .back-link-float:hover { background: #dd353d; }

        /* SCROLLBAR STYLING */
        .comments-container::-webkit-scrollbar { width: 8px; }
        .comments-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .comments-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        .comments-container::-webkit-scrollbar-thumb:hover { background: #dd353d; }

        /* RESPONSIVE: Stack on small screens */
        @media (max-width: 850px) {
            .wrapper { flex-direction: column; align-items: stretch; }
            .form-container { position: static; }
            .comments-container { max-height: 500px; }
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-link-float">← Dashboard</a>

<div class="wrapper">
    
    <div class="form-container">
        <?php if($movie['poster_path']): ?>
            <img class="poster" src="https://image.tmdb.org/t/p/w200<?= $movie['poster_path']; ?>" alt="Poster">
        <?php endif; ?>

        <h1><?= htmlspecialchars($movie['title']); ?></h1>
        
        <form method="POST">
            <input type="hidden" name="movie_id" value="<?= $movie_id; ?>">
            <input type="hidden" name="movie_title" value="<?= htmlspecialchars($movie['title']); ?>">
            
            <textarea name="review" placeholder="Write your review here..." required></textarea>
            
            <button type="submit">Publish Review <i class="fa-solid fa-paper-plane"></i></button>
        </form>

        <?php if($message): ?>
            <p class="message <?= (strpos($message, 'successfully') !== false) ? 'success' : 'warning' ?>">
                <?= $message ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="comments-container">
        <div class="comments-header">
            <i class="fa-solid fa-comments"></i> Community Reviews
        </div>

        <?php if(count($existing_reviews) > 0): ?>
            <?php foreach($existing_reviews as $rev): ?>
                <div class="single-review">
                    <div class="user-name">
                        <span><i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($rev['username']); ?></span>
                        <span class="review-date"><?= date("M d, Y • h:i A", strtotime($rev['created_at'])); ?></span>
                    </div>
                    <?= nl2br(htmlspecialchars($rev['review'])); ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-comment-dots" style="font-size: 40px; margin-bottom: 15px;"></i><br>
                No reviews yet.<br>Be the first to share your thoughts!
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>