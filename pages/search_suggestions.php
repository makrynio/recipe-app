<?php

// ----------------------
// Include dependencies
// ----------------------

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Suggestions endpoint is available to authenticated users only.
requireLogin();

// ----------------------
// Configure JSON response
// ----------------------

header('Content-Type: application/json; charset=utf-8');

// ----------------------
// Read and validate request input
// ----------------------

$query = trim($_GET['q'] ?? '');
$type  = $_GET['type'] ?? 'recipe';

// Empty query returns an empty array quickly (no database query needed).
if ($query === '') {
    echo json_encode([]);
    exit;
}

// Hard cap the query length to keep LIKE searches bounded.
if (strlen($query) > 80) {
    $query = substr($query, 0, 80);
}

// ----------------------
// Build query by suggestion type
// ----------------------

if ($type === 'ingredient') {
    // Ingredient mode: suggest distinct ingredient names from published recipes.
    $sql = "
        SELECT DISTINCT recipe_ingredients.ingredient_name AS value
        FROM recipe_ingredients
        INNER JOIN recipes ON recipes.id = recipe_ingredients.recipe_id
        WHERE recipes.is_published = 1
          AND recipe_ingredients.ingredient_name LIKE ? ESCAPE '\\\\'
        ORDER BY recipe_ingredients.ingredient_name ASC
        LIMIT 8
    ";
} else {
    // Default mode: suggest distinct recipe titles from published recipes.
    $sql = "
        SELECT DISTINCT title AS value
        FROM recipes
        WHERE is_published = 1
          AND title LIKE ? ESCAPE '\\\\'
        ORDER BY title ASC
        LIMIT 8
    ";
}

// ----------------------
// Execute prepared statement
// ----------------------

$stmt = mysqli_prepare($conn, $sql);

// Return a safe JSON payload even on server-side prepare failures.
if (!$stmt) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

// Escape SQL LIKE wildcard characters so user input is matched literally.
$escapedQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
$contains = '%' . $escapedQuery . '%';
mysqli_stmt_bind_param($stmt, 's', $contains);
mysqli_stmt_execute($stmt);

// ----------------------
// Collect and return suggestions
// ----------------------

$result = mysqli_stmt_get_result($stmt);
$suggestions = [];

// Flatten result rows into a plain JSON string array consumed by the client datalist.
while ($row = mysqli_fetch_assoc($result)) {
    $suggestions[] = $row['value'];
}

mysqli_stmt_close($stmt);

echo json_encode($suggestions);
