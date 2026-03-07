<?php
require_once '../includes/db.php'; // establish the database connection ($conn)
require_once '../includes/auth.php'; // load session/auth helper functions

requireLogin(); // redirect to login page if the user is not logged in

$userId = getCurrentUserId(); // retrieve the logged-in user's ID from the session

// Fetch user info
$stmt = mysqli_prepare($conn, 'SELECT name, email, created_at FROM users WHERE id = ?'); // prepare query to avoid SQL injection
mysqli_stmt_bind_param($stmt, 'i', $userId); // bind $userId as an integer parameter
mysqli_stmt_execute($stmt); // run the prepared statement against the database
mysqli_stmt_bind_result($stmt, $name, $email, $createdAt); // map result columns to PHP variables
mysqli_stmt_fetch($stmt); // fetch the single matching row into the bound variables
mysqli_stmt_close($stmt); // free the statement resource

// TODO: Fetch favourite recipes once Edon's favourites table is ready
// $favourites = getFavouriteRecipes($conn, $userId);
?>
<!DOCTYPE html> <!-- declares the document as HTML5 -->
<html lang="en"> <!-- sets the page language for accessibility and search engines -->
<head>
    <meta charset="UTF-8"> <!-- ensures correct character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- makes the layout responsive on mobile devices -->
    <title>My Account – Recipe App</title> <!-- text shown in the browser tab -->
    <link rel="stylesheet" href="/recipe-app/css/style.css"> <!-- loads the shared stylesheet -->
</head>
<body>
    <header> <!-- site-wide header containing navigation -->
        <nav> <!-- navigation bar with links -->
            <a href="/recipe-app/index.php">Home</a> <!-- link back to the homepage -->
            <a href="/recipe-app/pages/logout.php">Log Out</a> <!-- link to end the user's session -->
        </nav>
    </header>

    <main class="account-container"> <!-- main content area for the account page -->
        <h1>My Account</h1> <!-- page heading -->

        <section class="account-info"> <!-- section displaying the user's profile details -->
            <h2>Profile</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); // escape output to prevent XSS ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); // escape output to prevent XSS ?></p>
            <p><strong>Member since:</strong> <?php echo date('d F Y', strtotime($createdAt)); // format the stored timestamp as a readable date ?></p>
        </section>

        <section class="account-favourites"> <!-- section that will list the user's saved recipes -->
            <h2>Saved Recipes</h2>
            <!-- TODO: Wire up once favourites table is ready (Person 1) -->
            <p>Your saved recipes will appear here.</p> <!-- placeholder until favourites feature is implemented -->
        </section>
    </main>

    <script src="/recipe-app/js/main.js"></script> <!-- loads the shared JavaScript file -->
</body>
</html>