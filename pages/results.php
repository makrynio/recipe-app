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

$name = $_GET['name'] ?? '';
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

if ($sort === "name" && $order === "asc") {
    $sql .= " ORDER BY recipes.title ASC";
} elseif ($sort === "name" && $order === "desc") {
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
if (!$isEmbeddedInSearch) {
    require_once __DIR__ . '/../includes/header.php';
}

?>

<?php if (!$isEmbeddedInSearch): ?>
<main class="container">
<?php else: ?>
<section class="search-results-inline">
<?php endif; ?>

    <h2>Search Results</h2>

    <?php if ($result->num_rows > 0): ?>

        <ul class="recipe-list">

            <?php while ($row = $result->fetch_assoc()): ?>

                <li>
                    <a href="recipe.php?id=<?php echo $row['id']; ?>">
                        <?php if (!empty($row['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Recipe thumbnail" class="recipe-thumbnail">
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