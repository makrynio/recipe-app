<!--
    Purpose: Shared site header for the Recipe Library application.
    Contains: Site title and main navigation links.
    Notes: Navigation changes depending on whether the user is logged in.
-->

<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helper.php';

$name = getCurrentUserName();
$greeting = getGreeting();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Recipe Library</title>
    <link rel="stylesheet" href="/recipe-app/css/styles.css">
</head>

<body>
    <header class="site-header">
        <div class="container">
            <h1 class="site-title">
                <a href="/recipe-app/index.php">The Recipe Library</a>
            </h1>
            <nav aria-label="Main navigation">
                <ul class="nav-list">
                    <li><a href="/recipe-app/index.php">Home</a></li>
                    <li><a href="/recipe-app/pages/search.php">Search</a></li>

                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li><a href="/recipe-app/pages/account.php">Account</a></li>
                        <li><a href="/recipe-app/pages/logout.php">Log Out</a></li>
                    <?php else: ?>
                        <li><a href="/recipe-app/pages/login.php">Log In</a></li>
                        <li><a href="/recipe-app/pages/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
                <p class="greeting">
                <?php if ($name): ?>
                    <?php echo htmlspecialchars($greeting . ', ' . $name); // personalized greeting if logged in ?>
                <?php else: ?>
                    Welcome, food lover! <!-- generic greeting if not logged in -->
                <?php endif; ?>
            </p>
            </nav>
        </div>
    </header>