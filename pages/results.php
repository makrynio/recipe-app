<?php
require_once '../includes/db.php';
require_once '../includes/auth.php'; // load session/auth helper functions

requireLogin();

$name = $_GET['name'] ?? '';
$category = $_GET['category'] ?? '';
$ingredient = $_GET['ingredient'] ?? '';
$sort = $_GET['sort'] ?? '';

$sql = "
SELECT DISTINCT recipes.*, categories.name AS category_name
FROM recipes
LEFT JOIN categories ON recipes.category_id = categories.id
LEFT JOIN recipe_ingredients ON recipes.id = recipe_ingredients.recipe_id
WHERE 1=1
";

$params = [];
$types = "";

if (!empty($name)) {
    $sql .= " AND recipes.title LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

if (!empty($category)) {
    $sql .= " AND recipes.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if (!empty($ingredient)) {
    $sql .= " AND recipe_ingredients.name LIKE ?";
    $params[] = "%$ingredient%";
    $types .= "s";
}

if ($sort == "name") {
    $sql .= " ORDER BY recipes.title ASC";
} elseif ($sort == "time") {
    $sql .= " ORDER BY recipes.cook_time_min ASC";
}

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search Results</title>

    <link rel="stylesheet" href="css/styles.css">

</head>

<body>

    <header>

        <h1>Recipe Web App</h1>

        <nav>
            <a href="index.php">Home</a>
            <a href="search.php">Search</a>
        </nav>

    </header>

    <main>

        <h2>Search Results</h2>

        <?php if ($result->num_rows > 0): ?>

            <div class="recipes">

                <?php while ($row = $result->fetch_assoc()): ?>

                    <div class="recipe-card">

                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                        <p>Category: <?php echo htmlspecialchars($row['category_name']); ?></p>

                        <p>Cooking time: <?php echo $row['cook_time_min']; ?> minutes</p>

                        <a href="recipe.php?id=<?php echo $row['id']; ?>">View Recipe</a>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <p>No recipes found.</p>

        <?php endif; ?>

    </main>

    <footer>

        <p>Recipe Web App © <?php echo date("Y"); ?></p>

    </footer>

</body>

</html>