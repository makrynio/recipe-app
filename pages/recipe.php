<?php
require_once '../includes/db.php';
require_once '../includes/auth.php'; // load session/auth helper functions

requireLogin();

// Get the recipe ID from the URL
// Example: recipe.php?id=3
$recipe_id = $_GET['id'] ?? 0;


// ----------------------
// Fetch the main recipe data
// ----------------------

$sql = "
SELECT recipes.*, categories.name AS category_name
FROM recipes
LEFT JOIN categories ON recipes.category_id = categories.id
WHERE recipes.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();

$result = $stmt->get_result();

// If no recipe is found, stop execution
if ($result->num_rows == 0) {
    die("Recipe not found");
}

$recipe = $result->fetch_assoc();


// ----------------------
// Fetch recipe_ingredients for the recipe
// ----------------------

$sql_ingredients = "
SELECT ingredient_name, quantity
FROM recipe_ingredients
WHERE recipe_id = ?
";

$stmt = $conn->prepare($sql_ingredients);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();

$recipe_ingredients = $stmt->get_result();


// ----------------------
// Fetch cooking steps
// ----------------------

$sql_steps = "
SELECT step_number, instruction, step_time_min
FROM recipe_steps
WHERE recipe_id = ?
ORDER BY step_number ASC
";

$stmt = $conn->prepare($sql_steps);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();

$steps = $stmt->get_result();

require_once '../includes/header.php';
?>

    <main>

        <!-- ===================== -->
        <!-- Main recipe information -->
        <!-- ===================== -->

        <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>

        <p><strong>Category:</strong> <?php echo htmlspecialchars($recipe['category_name']); ?></p>

        <p><strong>Cooking time:</strong> <?php echo $recipe['cook_time_min']; ?> minutes</p>

        <p><?php echo htmlspecialchars($recipe['description']); ?></p>


        <!-- ===================== -->
        <!-- recipe_ingredients section -->
        <!-- ===================== -->

        <h3>recipe_ingredients</h3>

        <ul>

            <?php while ($row = $recipe_ingredients->fetch_assoc()): ?>

                <li>
                    <?php echo htmlspecialchars($row['quantity']) . " - " . htmlspecialchars($row['ingredient_name']); ?>
                </li>

            <?php endwhile; ?>

        </ul>


        <!-- ===================== -->
        <!-- Cooking steps section -->
        <!-- ===================== -->

        <h3>Steps</h3>

        <ol>

            <?php while ($row = $steps->fetch_assoc()): ?>

                <li>

                    <p><?php echo htmlspecialchars($row['instruction']); ?></p>

                    <p><em>Time: <?php echo $row['step_time_min']; ?> minutes</em></p>

                </li>

            <?php endwhile; ?>

        </ol>

    </main>

<?php require_once '../includes/footer.php'; ?>