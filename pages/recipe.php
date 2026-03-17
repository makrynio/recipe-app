<?php

// ----------------------
// Include dependencies
// ----------------------

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

    if (isset($_POST['rating'])) {
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
// Accessibility: use the recipe title in <title> so tabs/history and assistive tech provide clear page context.
$pageTitle = $recipe['title'] ?? 'Recipe';
require_once __DIR__ . '/../includes/header.php';

?>

<!-- Accessibility: id="main-content" + tabindex="-1" lets the skip link move keyboard focus directly to main content. -->
<main id="main-content" class="container recipe-page" tabindex="-1">

    <section class="recipe-hero">
        <div class="recipe-intro">
            <!-- Accessibility: main page heading is an <h1> (single primary heading for this document). -->
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>

            <p class="recipe-description"><?php echo htmlspecialchars($recipe['description'] ?? 'Description not specified'); ?></p>

            <?php
            // Compute average rating values for display
            $avgRating    = (float) $recipe['avg_rating'];          // Cast to float for arithmetic
            $ratingCount  = (int)   $recipe['rating_count'];         // Total number of ratings submitted
            $displayRating = number_format($avgRating, 1);           // Format to one decimal place e.g. 4.0

            // Build the star glyph string from the rounded average (clamped to 0–5)
            $filledStars = max(0, min(5, (int) round($avgRating)));   // Filled ★ count
            $emptyStars  = 5 - $filledStars;                         // Remaining ☆ count

            // Concatenate Unicode filled (&#9733; ★) and empty (&#9734; ☆) star characters
            $starVisual = str_repeat('&#9733;', $filledStars) . str_repeat('&#9734;', $emptyStars);
            ?>
            <p class="recipe-average-rating">
                <!-- Accessibility: decorative star glyphs are hidden from screen readers to avoid duplicate announcements. -->
                <span class="rating-stars" aria-hidden="true"><?php echo $starVisual; ?></span>
                <?php echo $displayRating; ?>
                (<?php echo $ratingCount; ?> <?php echo $ratingCount === 1 ? 'rating' : 'ratings'; ?>)
            </p>
        </div>

        <div class="recipe-media">
            <?php if (!empty($recipe['image_url'])): ?>
                <img
                    src="<?php echo htmlspecialchars($recipe['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                    class="recipe-cover"
                >
            <?php else: ?>
                <div class="recipe-cover recipe-cover-placeholder">No image available</div>
            <?php endif; ?>
        </div>

        <div class="recipe-summary">

            <div class="recipe-meta">
                <p><strong>Category:</strong> <?php echo htmlspecialchars($recipe['category_name'] ?? 'Uncategorised'); ?></p>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($recipe['author_name']); ?></p>
                <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($recipe['difficulty']); ?></p>
                <p><strong>Servings:</strong> <?php echo $recipe['servings'] ?? 'N/A'; ?></p>
                <p><strong>Preparation time:</strong> <?php echo $recipe['prep_time_min'] ?? 'N/A'; ?> minutes</p>
                <p><strong>Cooking time:</strong> <?php echo $recipe['cook_time_min'] ?? 'N/A'; ?> minutes</p>
            </div>
             <?php if (!empty($recipe['source_url'])): ?>
                <p class="recipe-source">
                    <strong>Source:</strong>
                    <a href="<?php echo htmlspecialchars($recipe['source_url']); ?>" target="_blank" rel="noopener noreferrer">
                        View original recipe
                    </a>
                </p>
            <?php endif; ?>

            <div class="recipe-form-group">
                <!-- data-auto-submit="true" tells main.js to submit this form immediately when a star is selected -->
                <!-- A <noscript> fallback button is provided for users with JavaScript disabled -->
                <form method="POST" id="rating-form" data-auto-submit="true" class="recipe-rating-form">

                    <fieldset class="star-rating">
                        <!-- Show "Update your rating" if the user has already rated, otherwise invite them to rate -->
                        <legend><?php echo $userRating ? 'Update your rating' : 'Rate this recipe'; ?></legend>
                        <!-- Accessibility: each star radio includes an aria-label so screen readers announce the exact rating value (1-5 stars). -->
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input
                                type="radio"
                                id="star<?php echo $i; ?>"
                                name="rating"
                                value="<?php echo $i; ?>"
                                aria-label="<?php echo $i; ?> <?php echo $i === 1 ? 'star' : 'stars'; ?>"
                                <?php echo ($userRating && (int)$userRating['rating'] === $i) ? 'checked' : ''; ?>
                                required
                            >
                            <label for="star<?php echo $i; ?>">&#9733;</label>
                        <?php endfor; ?>
                    </fieldset>

                    <!-- Fallback submit button for users without JavaScript; with JS enabled, selecting a star auto-submits. -->
                    <noscript>
                        <button type="submit" name="submit_rating">
                            <?php echo $userRating ? 'Update rating' : 'Submit rating'; ?>
                        </button>
                    </noscript>

                </form>

                <div class="stacked-forms recipe-actions">

                    <form method="POST">

                        <?php if ($isFavourite): ?>
                            <button type="submit" name="remove_favourite">Unsave Recipe</button>
                        <?php else: ?>
                            <button type="submit" name="add_favourite">Save Recipe</button>
                        <?php endif; ?>

                    </form>

                </div>
            </div>

        </div>

    </section>

    <section class="recipe-details">

        <div class="recipe-details-column">
            <!-- Accessibility: use <h2> here (not <h3>) so heading order follows <h1> without skipping levels. -->
            <h2>Ingredients</h2>

            <ul class="recipe-ingredients-list">

                <?php while ($row = $ingredients->fetch_assoc()): ?>

                    <li>

                        <?php

                        // Prepare ingredient display with optional quantity and unit
                        // Strip trailing decimal zeros so e.g. 1.00 displays as 1 and 1.50 as 1.5
                        $quantity = $row['quantity'] !== null ? rtrim(rtrim((string)$row['quantity'], '0'), '.') : '';
                        $unit = $row['unit'] ?? '';
                        $note = $row['note'] ?? '';

                        // Only prepend the quantity/unit prefix when at least one value is present;
                        // if both are absent (NULL in the database) show the ingredient name alone
                        $prefix = trim("$quantity $unit");
                        echo $prefix !== '' ? htmlspecialchars($prefix) . ' - ' . htmlspecialchars($row['ingredient_name']) : htmlspecialchars($row['ingredient_name']);

                        // Display optional ingredient note
                        if ($note) {
                            echo " (" . htmlspecialchars($note) . ")";
                        }

                        ?>

                    </li>

                <?php endwhile; ?>

            </ul>
        </div>

        <div class="recipe-details-column">
            <!-- Accessibility: sibling section heading remains <h2> to keep a consistent page outline. -->
            <h2>Steps</h2>

            <ol class="recipe-steps-list">

                <?php while ($row = $steps->fetch_assoc()): ?>

                    <li>

                        <p><?php echo htmlspecialchars($row['instruction']); ?></p>

                        <?php if ($row['step_time_min']): ?>
                            <!-- ⏱ icon replaces the plain-text "Time:" label for a cleaner look -->
                            <p><em>⏱ <?php echo $row['step_time_min']; ?> minutes</em></p>
                        <?php endif; ?>

                    </li>

                <?php endwhile; ?>

            </ol>
        </div>

    </section>

</main>

<?php
// Include the site footer
require_once __DIR__ . '/../includes/footer.php';
?>