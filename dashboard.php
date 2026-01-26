<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$api_key = "1163142a130a2e01a5fb73752ac05995"; // Your API Key
$search_query = isset($_GET['search']) ? urlencode($_GET['search']) : '';

if (!empty($search_query)) {
    // URL for searching specific movies
    $tmdb_url = "https://api.themoviedb.org/3/search/movie?api_key=$api_key&query=$search_query&language=en-US&page=1";
} else {
    // Default URL for popular movies
    $tmdb_url = "https://api.themoviedb.org/3/movie/popular?api_key=$api_key&language=en-US&page=1";
}

$movies_json = file_get_contents($tmdb_url);
$movies_data = json_decode($movies_json, true);
$movies = isset($movies_data['results']) ? $movies_data['results'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>REVCOM - Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to right, #210b0c, #dd353d, #210b0c);
            margin: 0;
            padding: 100px;
            min-height: 100vh;
            color: #fff;
        }

        header {
            position: fixed;
            top: 0%;
            left: 0%;
            width: 100%;
            z-index: 1000;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0,0,0,0.5);
            transition: background 0.3s ease;
        }

        header.scrolled {
            background: rgba(0, 0, 0, 0.90);
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        header h1 {
            font-size: 24px;
            font-weight: 800;
        }

        header h1 a {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        header h1 a:hover {
            color: #dd353d;
            opacity: 1;
            text-shadow:
            0 0 10px rgba(221, 53, 61, 0.4);

        }

        .logout {
            background: #fff;
            color: #dd353d;
            border: 2px solid transparent;
            border-radius: 50px;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 13px;
            text-transform: capitalize;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .logout:hover {
            background: #dd353d;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(221,53,61,0.5);
        }

        .movie-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            padding: 40px 20px;
            justify-content: center;
        }

        .movie-card {
            background: #fff;
            color: #000;
            width: auto;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            height: 100%;
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
            height: 44px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .movie-card p {
            margin: 0 10px 10px;
            font-size: 14px;
            text-align: center;
        }

        .review-btn {
            display: block;
            margin-top: auto;
            margin-bottom: 20px;
            align-self: center;
            background: linear-gradient(135deg, #dd353d 0%, #b02a30 100%);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(221, 53, 61, 0.4);
            transition: all 0.3s ease;
        }
        .review-btn:hover {
            background: linear-gradient(135deg, #ff4d55 0%, #dd353d 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(221, 53, 61, 0.6);
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
    <h1>
        Welcome to <a href="dashboard.php">REVCOM</a>,
        <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    
    <div style="display: flex; align-items: center; gap: 15px;">
        <form action="dashboard.php" method="GET" style="position: relative; display: flex; align-items: center;">
            <input type="text" name="search" placeholder="Find a movie..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   style="padding: 12px 20px; border-radius: 50px; border: none; width: 250px; outline: none; font-family: 'Quicksand', sans-serif;">
            
            <button type="submit" style="background: none; border: none; position: absolute; right: 15px; cursor: pointer; color: #dd353d; font-size: 18px;">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <button class="logout" onclick="window.location.href='profile.php'">
            <i class="fa-solid fa-user"></i> Profile
        </button>

        <button class="logout" onclick="window.location.href='logout.php'">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
    </div>
</header>

<div class="movie-container">
    <?php foreach($movies as $movie): ?>
        <div class="movie-card">
            <img src="https://image.tmdb.org/t/p/w500<?php echo $movie['poster_path']; ?>" alt="<?php echo $movie['title']; ?>">
            <h3><?php echo $movie['title']; ?></h3>
            <p>⭐Rating: <b><?php echo number_format($movie['vote_average'], 1); ?></b>/10</p>
            <button class="review-btn" onclick="window.location.href='reviews.php?movie_id=<?php echo $movie['id']; ?>'">Write Review ✎</button>
        </div>
    <?php endforeach; ?>
</div>

<script src="script.js"></script>
</body>
</html>
