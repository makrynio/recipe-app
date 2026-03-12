<?php

// Include the database connection file
// __DIR__ ensures a reliable absolute path regardless of script execution context
require_once __DIR__ . '/../includes/db.php';

// Include authentication helper functions
require_once __DIR__ . '/../includes/auth.php';

// Ensure that only authenticated users can access the search results
requireLogin();


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

if ($sort === "name") {
    $sql .= " ORDER BY recipes.title ASC";
} elseif ($sort === "time") {
    $sql .= " ORDER BY recipes.cook_time_min ASC";
} elseif ($sort === "rating") {
    $sql .= " ORDER BY avg_rating DESC";
} elseif ($sort === "newest") {
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
require_once __DIR__ . '/../includes/header.php';

?>

<main>

    <h2>Search Results</h2>

    <?php if ($result->num_rows > 0): ?>

        <div class="recipes">

            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="recipe-card">

                    <!-- Display recipe title -->
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                    <!-- Display recipe category -->
                    <p>
                        <strong>Category:</strong>
                        <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorised'); ?>
                    </p>

                    <!-- Display cooking time -->
                    <p>
                        <strong>Cooking time:</strong>
                        <?php echo $row['cook_time_min'] ?? 'N/A'; ?> minutes
                    </p>

                    <!-- Display difficulty -->
                    <p>
                        <strong>Difficulty:</strong>
                        <?php echo htmlspecialchars($row['difficulty']); ?>
                    </p>

                    <!-- Display average rating only once at least one rating exists -->
                    <?php if ($row['rating_count'] > 0): ?>
                        <p>
                            <strong>Rating:</strong>
                            <?php echo round($row['avg_rating'], 1); ?> / 5 ⭐
                        </p>
                    <?php endif; ?>

                    <!-- Link to the recipe details page -->
                    <a href="recipe.php?id=<?php echo $row['id']; ?>">View Recipe</a>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <p>No recipes found matching your criteria.</p>

    <?php endif; ?>

</main>

<?php
// Include the website footer
require_once __DIR__ . '/../includes/footer.php';
?>