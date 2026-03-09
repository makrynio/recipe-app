<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Web App</title>

    <link rel="stylesheet" href="/recipe-app/css/styles.css">
</head>

<body>

<header class="site-header">
    <div class="container">

        <h1 class="site-title">
            <a href="index.php">Recipe Web App</a>
        </h1>

        <nav aria-label="Main navigation">
            <ul class="nav-list">
                <li><a href="/recipe-app/index.php">Home</a></li>
                <li><a href="/recipe-app/pages/search.php">Search</a></li>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <li><a href="/recipe-app/pages/account.php">Account</a></li>
                    <li><a href="/recipe-app/pages/logout.php">Log Out</a></li>
                <?php else: ?>
                    <li><a href="/recipe-app/pages/login.php">Login</a></li>
                    <li><a href="/recipe-app/pages/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
</header>