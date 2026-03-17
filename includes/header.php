<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helper.php';

$name = getCurrentUserName();
$greeting = getGreeting();

// Build a contextual page title when a page-specific title is provided.
// Accessibility: clearer <title> text improves orientation in tabs, history, and screen reader page lists.
$documentTitle = 'The Recipe Library';
if (isset($pageTitle) && is_string($pageTitle) && trim($pageTitle) !== '') {
    $documentTitle = trim($pageTitle) . ' | The Recipe Library';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($documentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/recipe-app/css/styles.css">
</head>

<body>
    <!-- Accessibility: keyboard users can skip repeated navigation and jump directly to #main-content. -->
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <header class="site-header">
        <div class="container">
            <!-- Use a non-heading element here; each page defines its own <h1> in main content for proper hierarchy. -->
            <p class="site-title">
                <a href="/recipe-app/index.php">The Recipe Library</a>
            </p>
            <div class="header-nav-group">
                <!-- Accessibility: explicit nav landmark label helps screen reader users identify this region quickly. -->
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
                </nav>
                <p class="greeting">
                    <?php if ($name): ?>
                        <?php echo htmlspecialchars($greeting . ', ' . $name); // personalized greeting if logged in ?>
                    <?php else: ?>
                        Welcome, food lover! <!-- generic greeting if not logged in -->
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </header>