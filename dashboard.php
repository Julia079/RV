<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$api_key = "1163142a130a2e01a5fb73752ac05995"; // Your API Key
$search_query = isset($_GET['search']) ? urlencode($_GET['search']) : '';

// Helper function to fetch data
function fetchTmdbMovies($url) {
    $json = @file_get_contents($url);
    if ($json === FALSE) return [];
    $data = json_decode($json, true);
    return isset($data['results']) ? $data['results'] : [];
}

// Initialize arrays
$now_playing_movies = []; 
$top_rated_movies = [];   
$upcoming_movies = [];    
$search_results = [];

if (!empty($search_query)) {
    // 1. SEARCH MODE
    $url = "https://api.themoviedb.org/3/search/movie?api_key=$api_key&query=$search_query&language=en-US&page=1";
    $search_results = fetchTmdbMovies($url);
} else {
    // 2. DASHBOARD MODE
    
    // In Theaters Now
    $now_playing_movies = fetchTmdbMovies("https://api.themoviedb.org/3/movie/now_playing?api_key=$api_key&language=en-US&page=1");

    // Top Rated
    $top_rated_movies = fetchTmdbMovies("https://api.themoviedb.org/3/movie/top_rated?api_key=$api_key&language=en-US&page=1");

    // ============================================================
    // FIXED: UPCOMING MOVIES (Using Official /movie/upcoming)
    // ============================================================
    
    // 1. Use the Official Endpoint
    // We add region=US because it usually has the most accurate 'future' dates. 
    // Without a region, the dates can be very mixed.
    $url_upcoming = "https://api.themoviedb.org/3/movie/upcoming?api_key=$api_key&language=en-US&page=1&region=US";
    $raw_upcoming = fetchTmdbMovies($url_upcoming);

    // 2. Set Timezone to Philippines
    date_default_timezone_set('Asia/Manila'); 
    $today_timestamp = strtotime(date('Y-m-d')); // Today at 00:00:00

    // 3. Filter: STRICTLY remove movies released before today
    $upcoming_movies = array_filter($raw_upcoming, function($movie) use ($today_timestamp) {
        if (!isset($movie['release_date']) || empty($movie['release_date'])) return false;
        
        $movie_date = strtotime($movie['release_date']);
        return $movie_date >= $today_timestamp;
    });

    // 4. Sort: Ensure the closest release dates appear first
    usort($upcoming_movies, function($a, $b) {
        return strtotime($a['release_date']) - strtotime($b['release_date']);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>REVCOM - Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to right, #210b0c, #dd353d, #210b0c);
            margin: 0;
            padding-top: 100px;
            padding-bottom: 50px;
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
            background: rgba(0, 0, 0, 0.95);
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        header h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
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
            text-shadow: 0 0 10px rgba(221, 53, 61, 0.4);
        }

        .logout {
            background: #fff;
            color: #dd353d;
            border: 2px solid transparent;
            padding: 10px 24px;
            border-radius: 50px;
            font-family: 'Quicksand', sans-serif;
            font-weight: 800;
            font-size: 13px;
            text-transform: capitalize;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .logout:hover {
            background: #dd353d;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(221,53,61,0.5);
        }

        /* --- CATEGORY HEADERS --- */
        .category-title {
            margin-left: 40px;
            margin-top: 40px;
            font-size: 28px;
            font-weight: 800;
            border-left: 5px solid #fff;
            padding-left: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* --- FIXED SIZE CARDS --- */
        .movie-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            padding: 20px 40px;
        }

        .movie-card {
            background: #fff;
            color: #000;
            width: 220px;       /* Fixed Compact Width */
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
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

<?php if (!empty($search_query)): ?>
    
    <h2 class="category-title">Search Results for "<?php echo htmlspecialchars($_GET['search']); ?>"</h2>
    <div class="movie-container">
        <?php if(empty($search_results)): ?>
            <p style="margin-left: 40px;">No movies found.</p>
        <?php else: ?>
            <?php foreach($search_results as $movie): ?>
                <div class="movie-card">
                    <?php $image = $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500".$movie['poster_path'] : "https://via.placeholder.com/500x750?text=No+Image"; ?>
                    <img src="<?php echo $image; ?>" alt="<?php echo $movie['title']; ?>">
                    <h3><?php echo $movie['title']; ?></h3>
                    <p>⭐Rating: <b><?php echo number_format($movie['vote_average'], 1); ?></b>/10</p>
                    <button class="review-btn" onclick="window.location.href='reviews.php?movie_id=<?php echo $movie['id']; ?>'">Write Review ✎</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php else: ?>

    <h2 class="category-title">In Theaters Now 🎟️</h2>
    <div class="movie-container">
        <?php foreach($now_playing_movies as $movie): ?>
            <div class="movie-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $movie['poster_path']; ?>" alt="<?php echo $movie['title']; ?>">
                <h3><?php echo $movie['title']; ?></h3>
                <p>⭐Rating: <b><?php echo number_format($movie['vote_average'], 1); ?></b>/10</p>
                <button class="review-btn" onclick="window.location.href='reviews.php?movie_id=<?php echo $movie['id']; ?>'">Write Review ✎</button>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="category-title">All-Time Best 🏆</h2>
    <div class="movie-container">
        <?php foreach($top_rated_movies as $movie): ?>
            <div class="movie-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $movie['poster_path']; ?>" alt="<?php echo $movie['title']; ?>">
                <h3><?php echo $movie['title']; ?></h3>
                <p>⭐Rating: <b><?php echo number_format($movie['vote_average'], 1); ?></b>/10</p>
                <button class="review-btn" onclick="window.location.href='reviews.php?movie_id=<?php echo $movie['id']; ?>'">Write Review ✎</button>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="category-title">Upcoming Releases 🍿</h2>
<div class="movie-container">
    <?php foreach($upcoming_movies as $up): 
        // --- LOGIC: Determine the Label ---
        $release_time = strtotime($up['release_date']);
        $current_month = date('Ym'); // e.g., 202602
        $release_month = date('Ym', $release_time);
        $current_year = date('Y');
        $release_year = date('Y', $release_time);

        $label = "COMING SOON";
        $badge_style = "background: #555; color: white;"; // Default Grey

        if ($release_month == $current_month) {
            $label = "THIS MONTH";
            $badge_style = "background: #dd353d; color: white; box-shadow: 0 0 10px #dd353d;"; // Glowing Red
        } elseif ($release_year == $current_year && $release_time > time()) {
            $label = "THIS YEAR";
            $badge_style = "background: #fff; color: #dd353d; border: 1px solid #dd353d;"; // White with Red Text
        }
    ?>

        <div class="movie-card">
            <?php 
            // Fix for missing images
            $poster = $up['poster_path'] ? "https://image.tmdb.org/t/p/w500".$up['poster_path'] : "https://via.placeholder.com/500x750?text=No+Image"; 
            ?>
            <img src="<?php echo $poster; ?>" alt="<?php echo $up['title']; ?>">
            
            <h3><?php echo $up['title']; ?></h3>
            
            <p style="color: #888; font-size: 13px; margin-bottom: 5px;">
                📅 <?= date("M d, Y", $release_time); ?>
            </p>

            <div style="text-align: center; margin-bottom: 15px;">
                <span style="font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 20px; letter-spacing: 1px; <?php echo $badge_style; ?>">
                    <?php echo $label; ?>
                </span>
            </div>

        </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<script src="script.js"></script>
</body>
</html>