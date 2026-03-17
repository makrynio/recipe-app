<?php

// Purpose: Build and display filtered recipe search results.
// This page can render standalone or be embedded under the search form.

// ----------------------
// Include dependencies
// ----------------------

// Include the database connection file
// __DIR__ ensures a reliable absolute path regardless of script execution context
require_once __DIR__ . '/../includes/db.php';

// Include authentication helper functions
require_once __DIR__ . '/../includes/auth.php';

// Ensure that only authenticated users can access the search results
requireLogin();

$isEmbeddedInSearch = isset($embedInSearch) && $embedInSearch === true;

// ----------------------
// Retrieve search parameters
// ----------------------

// The null coalescing operator ensures that empty values are used
// if parameters are not present in the GET request

$name = $_GET['recipe_name'] ?? '';
$category = $_GET['category'] ?? '';
$ingredient = $_GET['ingredient'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$max_time = $_GET['max_time'] ?? '';
$sort = $_GET['sort'] ?? '';
$order = $_GET['order'] ?? '';

// ----------------------
// Base SQL query
// ----------------------

// This query retrieves recipes along with:
// - category name
// - average rating
// - number of ratings

$sql = "
SELECT 
    recipes.*,
    categories.name AS category_name,
    COALESCE(AVG(ratings.rating),0) AS avg_rating,
    COUNT(ratings.id) AS rating_count
FROM recipes
LEFT JOIN categories ON recipes.category_id = categories.id
LEFT JOIN recipe_ingredients ON recipes.id = recipe_ingredients.recipe_id
LEFT JOIN ratings ON recipes.id = ratings.recipe_id
WHERE recipes.is_published = 1
";

$params = [];
$types = "";

// ----------------------
// Apply filters
// ----------------------

// Filter by recipe name
if (!empty($name)) {
    $sql .= " AND recipes.title LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

// Filter by category
if (!empty($category)) {
    $sql .= " AND recipes.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Filter by ingredient
if (!empty($ingredient)) {
    $sql .= " AND recipe_ingredients.ingredient_name LIKE ?";
    $params[] = "%$ingredient%";
    $types .= "s";
}

// Filter by difficulty
if (!empty($difficulty)) {
    $sql .= " AND recipes.difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

// Filter by maximum cooking time
if (!empty($max_time)) {
    $sql .= " AND recipes.cook_time_min <= ?";
    $params[] = $max_time;
    $types .= "i";
}

// ----------------------
// Group results
// ----------------------

// Required because AVG() and COUNT() are used
$sql .= " GROUP BY recipes.id";

// ----------------------
// Sorting options
// ----------------------

if ($sort === "recipe_name" && $order === "asc") {
    $sql .= " ORDER BY recipes.title ASC";
} elseif ($sort === "recipe_name" && $order === "desc") {
    $sql .= " ORDER BY recipes.title DESC";
} elseif ($sort === "time" && $order === "asc") {
    $sql .= " ORDER BY recipes.cook_time_min ASC";
} elseif ($sort === "time" && $order === "desc") {
    $sql .= " ORDER BY recipes.cook_time_min DESC";
} elseif ($sort === "rating" && $order === "asc") {
    $sql .= " ORDER BY avg_rating ASC";
} elseif ($sort === "rating" && $order === "desc") {
    $sql .= " ORDER BY avg_rating DESC";
} elseif ($sort === "newest" && $order === "asc") {
    $sql .= " ORDER BY recipes.created_at ASC";
} elseif ($sort === "newest" && $order === "desc") {
    $sql .= " ORDER BY recipes.created_at DESC";
}

// ----------------------
// Prepare and execute query
// ----------------------

// Prepared statements protect against SQL injection

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Include page header
// Set dynamic page title for <title> tag (accessibility: helps users identify page in browser tabs and history)
if (!$isEmbeddedInSearch) {
    $pageTitle = 'Search Results';
    require_once __DIR__ . '/../includes/header.php';
}

?>

<?php if (!$isEmbeddedInSearch): ?>
<!-- 
Accessibility: Main content landmark with id="main-content" and tabindex="-1" enables skip-link to focus page content.
This allows keyboard users and screen reader users to jump directly to main content, bypassing navigation.
Only rendered when this page is standalone; replaced by <section> when embedded in search form to avoid nested landmarks.
-->
<main id="main-content" class="container" tabindex="-1">
<?php else: ?>
<!-- Accessibility: When embedded in search page, use <section> instead of <main> to avoid nested landmark issues -->
<section class="search-results-inline">
<?php endif; ?>

    <?php if (!$isEmbeddedInSearch): ?>
        <!-- Accessibility: Single <h1> per page establishes clear hierarchy for screen readers (when standalone) -->
        <h1>Search Results</h1>
    <?php else: ?>
        <!-- Accessibility: <h2> used when embedded in search page (search.php contains the <h1>) -->
        <h2>Search Results</h2>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>

        <ul class="recipe-list">

            <?php while ($row = $result->fetch_assoc()): ?>

                <li>
                    <a href="recipe.php?id=<?php echo $row['id']; ?>">
                        <?php if (!empty($row['image_url'])): ?>
                            <!-- 
                            Accessibility: alt="" (empty alt text) marks image as decorative.
                            The recipe title and category text are already in visible form, so the image doesn't need a separate description.
                            Screen readers will skip this image and read the surrounding text (recipe name, category, difficulty, rating).
                            
                            Performance: loading="lazy" defers image loading until the image is near the viewport.
                            This improves initial page load speed, especially important for recipe list pages with many images.
                            -->
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="" class="recipe-thumbnail" loading="lazy">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($row['title']); ?> — <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorised'); ?>
                        <!-- Star glyphs built from rounded average rating, matching the recipe detail page format -->
                        <small>(<?php echo $row['cook_time_min'] ?? 'N/A'; ?> min · <?php echo htmlspecialchars($row['difficulty']); ?><?php if ($row['rating_count'] > 0):
                            $r = (float) $row['avg_rating'];
                            $filled = max(0, min(5, (int) round($r)));
                            $stars = str_repeat('&#9733;', $filled) . str_repeat('&#9734;', 5 - $filled);
                        ?> · <span class="rating-stars" style="font-size:inherit;"><?php echo $stars; ?></span> <?php echo number_format($r, 1); ?><?php endif; ?>)</small>
                    </a>
                </li>

            <?php endwhile; ?>

        </ul>

    <?php else: ?>

        <p>No recipes found matching your criteria.</p>

    <?php endif; ?>

<!-- 
Accessibility: Conditionally close landmarks based on whether this page is standalone or embedded.
- Standalone (direct access): Close <main id="main-content"> landmark (opened at top of output)
- Embedded (in search.php): Close <section> wrapper to avoid nested landmark issues
Skip-link on search.php (parent page) can still focus this content via the main-content id target.
-->
<?php if (!$isEmbeddedInSearch): ?>
</main>
<?php else: ?>
</section>
<?php endif; ?>

<?php
// Include the website footer
if (!$isEmbeddedInSearch) {
    require_once __DIR__ . '/../includes/footer.php';
}
?>