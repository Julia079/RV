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
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to right, #dd353d, #210b0c);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #fff;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0,0,0,0.5);
        }

        header h1 {
            font-size: 24px;
            font-weight: 800;
        }

        .logout {
            background: #fff;
            color: #dd353d;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .logout:hover {
            background: #dd353d;
            color: #fff;
        }

        .movie-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            padding: 40px 20px;
        }

        .movie-card {
            background: #fff;
            color: #000;
            width: 220px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.3);
        }

        .movie-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .movie-card h3 {
            margin: 10px;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
        }

        .movie-card p {
            margin: 0 10px 10px;
            font-size: 14px;
            text-align: center;
        }

        .review-btn {
            display: block;
            margin: 10px auto 15px;
            background: #dd353d;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .review-btn:hover {
            background: #ff6b5c;
        }

        @media(max-width: 600px){
            .movie-card {
                width: 80%;
            }
        }

    </style>
</head>
<body>

<header>
    <h1>Welcome to REVCOM, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <button class="logout" onclick="window.location.href='logout.php'">Logout</button>
</header>

<div class="movie-container">
    <?php foreach($movies as $movie): ?>
        <div class="movie-card">
            <img src="https://image.tmdb.org/t/p/w500<?php echo $movie['poster_path']; ?>" alt="<?php echo $movie['title']; ?>">
            <h3><?php echo $movie['title']; ?></h3>
            <p>Rating: <?php echo $movie['vote_average']; ?>/10</p>
            <button class="review-btn" onclick="window.location.href='reviews.php?movie_id=<?php echo $movie['id']; ?>'">Write Review</button>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
