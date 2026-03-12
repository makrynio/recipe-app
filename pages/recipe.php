<?php

// Include the database connection
// __DIR__ ensures the correct absolute path regardless of execution context
require_once __DIR__ . '/../includes/db.php';

// Include authentication helper functions
require_once __DIR__ . '/../includes/auth.php';

// Include helper functions related to favourite recipes
require_once __DIR__ . '/../includes/favourites.php';

// Include helper functions for recipe ratings
require_once __DIR__ . '/../includes/ratings.php';

// Ensure the user is authenticated before accessing this page
requireLogin();


// Retrieve recipe ID from the URL query parameter
// Example: recipe.php?id=3
$recipe_id = $_GET['id'] ?? 0;


// ----------------------
// Fetch main recipe information
// ----------------------

// This query retrieves the recipe along with:
// - its category
// - the author's name
// - the average rating
// - the total number of ratings

$sql = "
SELECT 
    recipes.*,
    categories.name AS category_name,
    users.full_name AS author_name,
    COALESCE(AVG(ratings.rating),0) AS avg_rating,
    COUNT(ratings.id) AS rating_count
FROM recipes
LEFT JOIN categories ON recipes.category_id = categories.id
LEFT JOIN users ON recipes.user_id = users.id
LEFT JOIN ratings ON recipes.id = ratings.recipe_id
WHERE recipes.id = ?
GROUP BY recipes.id
";

// Prepared statement prevents SQL injection attacks
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();

$result = $stmt->get_result();

// Stop execution if the recipe does not exist
if ($result->num_rows === 0) {
    die("Recipe not found");
}

// Store recipe data
$recipe = $result->fetch_assoc();


// ----------------------
// Fetch ingredients
// ----------------------

// Retrieve all ingredients belonging to the recipe
// Ordered by position to maintain the correct display order

$sql_ingredients = "
SELECT ingredient_name, quantity, unit, note
FROM recipe_ingredients
WHERE recipe_id = ?
ORDER BY position ASC
";

$stmt = $conn->prepare($sql_ingredients);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();

$ingredients = $stmt->get_result();


// ----------------------
// Fetch cooking steps
// ----------------------

// Retrieve all preparation steps for the recipe
// Ordered by step_number to maintain the correct sequence

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


// ----------------------
// Favourite recipe logic
// ----------------------

// Retrieve the current logged-in user ID
$userId = getCurrentUserId();

// Handle form submission for saving or removing a favourite
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_favourite'])) {
        addFavourite($conn, $userId, $recipe_id);
    }

    if (isset($_POST['remove_favourite'])) {
        removeFavourite($conn, $userId, $recipe_id);
    }

    if (isset($_POST['submit_rating'])) {
        $rating = (int) ($_POST['rating'] ?? 0);

        if ($rating >= 1 && $rating <= 5) {
            saveRating($conn, $userId, $recipe_id, $rating, '');
        }
    }

    // Redirect back to the same page to prevent duplicate submissions
    header("Location: recipe.php?id=" . $recipe_id);
    exit();
}

// Check whether this recipe is already saved by the user
$isFavourite = isFavourite($conn, $userId, $recipe_id);

// Fetch the current user's existing rating for this recipe (if any)
$userRating = getUserRating($conn, $userId, $recipe_id);


// Include the website header (navigation and layout)
require_once __DIR__ . '/../includes/header.php';

?>

<main>

    <!-- ===================== -->
    <!-- Recipe main information -->
    <!-- ===================== -->

    <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>

    <p><strong>Category:</strong> <?php echo htmlspecialchars($recipe['category_name'] ?? 'Uncategorised'); ?></p>

    <p><strong>Author:</strong> <?php echo htmlspecialchars($recipe['author_name']); ?></p>

    <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($recipe['difficulty']); ?></p>

    <p><strong>Servings:</strong> <?php echo $recipe['servings'] ?? 'N/A'; ?></p>

    <p><strong>Preparation time:</strong> <?php echo $recipe['prep_time_min'] ?? 'N/A'; ?> minutes</p>

    <p><strong>Cooking time:</strong> <?php echo $recipe['cook_time_min'] ?? 'N/A'; ?> minutes</p>


    <!-- Display recipe image if available -->
    <?php if (!empty($recipe['image_url'])): ?>
        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" style="max-width:400px;">
    <?php endif; ?>


    <!-- Display recipe description -->
    <p><?php echo htmlspecialchars($recipe['description'] ?? 'Description not specified'); ?></p>


    <!-- Display original source link if provided -->
    <?php if (!empty($recipe['source_url'])): ?>
        <p>
            Source:
            <a href="<?php echo htmlspecialchars($recipe['source_url']); ?>" target="_blank">
                View original recipe
            </a>
        </p>
    <?php endif; ?>


    <!-- Display average rating only once at least one rating exists -->
    <?php if ($recipe['rating_count'] > 0): ?>
        <p>
            <strong>Average rating:</strong>
            <?php echo round($recipe['avg_rating'], 1); ?> / 5 ⭐
            (<?php echo $recipe['rating_count']; ?> <?php echo $recipe['rating_count'] === 1 ? 'rating' : 'ratings'; ?>)
        </p>
    <?php endif; ?>


    <!-- ===================== -->
    <!-- Rate this recipe -->
    <!-- ===================== -->

    <h3><?php echo $userRating ? 'Update your rating' : 'Rate this recipe'; ?></h3>

    <div class="stacked-forms">

        <form method="POST" id="rating-form">

            <fieldset class="star-rating">
                <legend>Your rating</legend>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input
                        type="radio"
                        id="star<?php echo $i; ?>"
                        name="rating"
                        value="<?php echo $i; ?>"
                        <?php echo ($userRating && (int)$userRating['rating'] === $i) ? 'checked' : ''; ?>
                        required
                    >
                    <label for="star<?php echo $i; ?>">&#9733;</label>
                <?php endfor; ?>
            </fieldset>

            <button type="submit" name="submit_rating">
                <?php echo $userRating ? 'Update rating' : 'Submit rating'; ?>
            </button>

        </form>


        <!-- ===================== -->
        <!-- Favourite button -->
        <!-- ===================== -->

        <form method="POST">

            <?php if ($isFavourite): ?>
                <button type="submit" name="remove_favourite">Unsave Recipe</button>
            <?php else: ?>
                <button type="submit" name="add_favourite">Save Recipe</button>
            <?php endif; ?>

        </form>

    </div>


    <!-- ===================== -->
    <!-- Ingredients section -->
    <!-- ===================== -->

    <h3>Ingredients</h3>

    <ul>

        <?php while ($row = $ingredients->fetch_assoc()): ?>

            <li>

                <?php

                // Prepare ingredient display with optional quantity and unit
                $quantity = $row['quantity'] !== null ? $row['quantity'] : '';
                $unit = $row['unit'] ?? '';
                $note = $row['note'] ?? '';

                echo htmlspecialchars(trim("$quantity $unit")) . " - " . htmlspecialchars($row['ingredient_name']);

                // Display optional ingredient note
                if ($note) {
                    echo " (" . htmlspecialchars($note) . ")";
                }

                ?>

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

                <?php if ($row['step_time_min']): ?>
                    <p><em>Time: <?php echo $row['step_time_min']; ?> minutes</em></p>
                <?php endif; ?>

            </li>

        <?php endwhile; ?>

    </ol>

</main>

<?php
// Include the site footer
require_once __DIR__ . '/../includes/footer.php';
?>