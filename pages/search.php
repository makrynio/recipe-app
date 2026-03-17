<?php

// ----------------------
// Include dependencies
// ----------------------

// Include the database connection.
// __DIR__ ensures a reliable absolute path regardless of the script execution location.
require_once __DIR__ . '/../includes/db.php';

// Include authentication helper functions (session handling and login verification).
require_once __DIR__ . '/../includes/auth.php';

// Ensure that the user is logged in before accessing this page.
requireLogin();

// ----------------------
// Fetch recipe categories
// ----------------------

// Retrieve all available categories from the database.
// These will populate the category dropdown in the search form.

$sql = "SELECT id, name FROM categories ORDER BY name ASC";
$categories = $conn->query($sql);

$showInlineResults = isset($_GET['inline_results']);

$recipe_name = $_GET['recipe_name'] ?? '';
$ingredient = $_GET['ingredient'] ?? '';
$category = $_GET['category'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$max_time = $_GET['max_time'] ?? '';
$sort = $_GET['sort'] ?? '';
$order = $_GET['order'] ?? 'desc';
// Track sort direction for aria-pressed state on the sort button (accessibility: announces ascending/descending to screen readers)
$isAscendingSort = strtolower($order) === 'asc';

// Include the website header (navigation bar and layout)
// Set dynamic page title for <title> tag (accessibility: helps users identify page in browser tabs and history)
$pageTitle = 'Search Recipes';
require_once __DIR__ . '/../includes/header.php';

?>

<!-- 
Accessibility: Main content landmark with id="main-content" and tabindex="-1" enables skip-link to focus page content.
This allows keyboard users and screen reader users to jump directly to main content, bypassing navigation.
-->
<main id="main-content" class="container" tabindex="-1">

    <!-- Accessibility: Single <h1> per page (changed from <h2>) establishes clear page hierarchy for screen readers -->
    <h1>Search Recipes</h1>
    <!-- Accessibility: Changed from <h3> to <p> for proper semantic structure (description, not heading) -->
    <p>Find recipes by name, ingredient, category, or cooking time.</p>
    <!-- 
Search form allowing users to filter recipes using multiple criteria.
The more flexible the search functionality, the better the application
fulfills the coursework requirements.
-->

    <form action="search.php" method="GET" class="search-form">

        <input type="hidden" name="inline_results" value="1">

        <div class="search-form-columns">

            <div class="search-form-column">

                <!-- ===================== -->
                <!-- Category filter -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-category">Category</label>
                    <select id="search-category" name="category">
                        <option value="">Any category</option>

                        <?php while ($row = $categories->fetch_assoc()): ?>

                            <option value="<?php echo $row['id']; ?>" <?php if ($category == $row['id'])
                                   echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <!-- ===================== -->
                <!-- Difficulty filter -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-difficulty">Difficulty</label>
                    <select id="search-difficulty" name="difficulty">

                        <option value="">Any difficulty</option>

                        <option value="easy" <?php if ($difficulty == 'easy')
                            echo 'selected'; ?>>Easy</option>
                        <option value="medium" <?php if ($difficulty == 'medium')
                            echo 'selected'; ?>>Medium</option>
                        <option value="hard" <?php if ($difficulty == 'hard')
                            echo 'selected'; ?>>Hard</option>

                    </select>
                </div>

                <!-- ===================== -->
                <!-- Sorting options -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-sort">Sort results</label>
                    <div class="sort-controls">
                        <select id="search-sort" name="sort">

                            <option value="">Default</option>

                            <!-- Sort by highest average rating -->
                            <option value="rating" <?php if ($sort == 'rating')
                                echo 'selected'; ?>>Rating</option>
                            <!-- Sort by shortest cooking time -->
                            <option value="time" <?php if ($sort == 'time')
                                echo 'selected'; ?>>Cooking time</option>
                            <!-- Sort alphabetically -->
                            <option value="recipe_name" <?php if ($sort == 'recipe_name')
                                echo 'selected'; ?>>Alphabetical</option>
                            <!-- Sort by newest recipes -->
                            <option value="newest" <?php if ($sort == 'newest')
                                echo 'selected'; ?>>Newest</option>
                        </select>
                        <!-- 
                        Accessibility improvements for sort toggle button:
                        - aria-pressed: Announces toggle state (active/inactive) to screen readers
                        - aria-label: Provides descriptive button purpose ("Sort order: ascending/descending")
                        - title: Shows tooltip on hover for sighted users
                        - Arrow symbol (↑ ascending / ↓ descending): Visual indicator of current sort direction
                        -->
                        <button
                            type="button"
                            id="sort-direction"
                            class="sort-btn"
                            aria-pressed="<?php echo $isAscendingSort ? 'true' : 'false'; ?>"
                            aria-label="Sort order: <?php echo $isAscendingSort ? 'ascending' : 'descending'; ?>"
                            title="Toggle sort order"
                        ><?php echo $isAscendingSort ? '↑' : '↓'; ?></button>

                        <input type="hidden" name="order" id="sort-order"
                            value="<?php echo htmlspecialchars($order); ?>">
                    </div>
                </div>

            </div>

            <div class="search-form-column">

                <!-- ===================== -->
                <!-- Recipe name search -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-name">Recipe name</label>
                    <input id="search-name" type="text" name="recipe_name" value="<?php echo htmlspecialchars($recipe_name); ?>"
                        placeholder="e.g. pizza" list="recipe-name-suggestions" autocomplete="off">
                    <datalist id="recipe-name-suggestions"></datalist>
                </div>

                <!-- ===================== -->
                <!-- Ingredient search -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-ingredient">Ingredient</label>
                    <input id="search-ingredient" type="text" name="ingredient"
                        value="<?php echo htmlspecialchars($ingredient); ?>" placeholder="e.g. tomato"
                        list="ingredient-suggestions" autocomplete="off">
                    <datalist id="ingredient-suggestions"></datalist>
                </div>

                <!-- ===================== -->
                <!-- Cooking time filter -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-max-time">Max cooking time (minutes)</label>
                    <input id="search-max-time" type="number" name="max_time"
                        value="<?php echo htmlspecialchars($max_time); ?>" placeholder="e.g. 30">
                </div>

            </div>

        </div>

        <!-- Submit search -->
        <button type="submit" class="cta-button">Search</button>

    </form>

    <?php if ($showInlineResults): ?>
        <?php
        // Render results inline below the search form when the page is reloaded with inline_results=1.
        $embedInSearch = true;
        require __DIR__ . '/results.php';
        ?>
    <?php endif; ?>

</main>

<?php

// Include the website footer
require_once __DIR__ . '/../includes/footer.php';

?>