<?php
$pageTitle = 'Home';
// Reuse the shared site header (navigation, metadata, opening layout markup).
include 'includes/header.php';
?>

<main id="main-content" class="container" tabindex="-1">
    <!-- Homepage hero section with primary call-to-action. -->
    <section class="hero">
        <h1>Discover recipes you'll love to cook</h1>
        <p>Browse delicious recipes, save your favourites, and cook with confidence.</p>
        <a href="/recipe-app/pages/search.php" class="cta-button">Search Recipes</a>
        <img src="/recipe-app/images/hero.jpg" alt="Fresh vegetable dish" class="hero-image">
    </section>
</main>

<?php
// Reuse the shared site footer and close common layout markup.
include 'includes/footer.php';
?>
