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

// Include the website header (navigation bar and layout)
require_once __DIR__ . '/../includes/header.php';

?>

<main class="container">

    <h2>Search Recipes</h2>
    <h3>Find recipes by name, ingredient, category or cooking time.</h3>
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

                            <option value="<?php echo $row['id']; ?>">
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
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>

                <!-- ===================== -->
                <!-- Sorting options -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-sort">Sort results</label>
                    <select id="search-sort" name="sort">
                        <option value="">Default</option>

                        <!-- Sort by highest average rating -->
                        <option value="rating">Rating</option>

                        <!-- Sort by shortest cooking time -->
                        <option value="time">Cooking time</option>

                        <!-- Sort alphabetically -->
                        <option value="name">Alphabetical</option>

                        <!-- Sort by newest recipes -->
                        <option value="newest">Newest</option>
                    </select>
                </div>

            </div>

            <div class="search-form-column">

                <!-- ===================== -->
                <!-- Recipe name search -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-name">Recipe name</label>
                    <input id="search-name" type="text" name="name" placeholder="e.g. pizza" list="recipe-name-suggestions" autocomplete="off">
                    <datalist id="recipe-name-suggestions"></datalist>
                </div>

                <!-- ===================== -->
                <!-- Ingredient search -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-ingredient">Ingredient</label>
                    <input id="search-ingredient" type="text" name="ingredient" placeholder="e.g. tomato" list="ingredient-suggestions" autocomplete="off">
                    <datalist id="ingredient-suggestions"></datalist>
                </div>

                <!-- ===================== -->
                <!-- Cooking time filter -->
                <!-- ===================== -->
                <div class="search-field">
                    <label for="search-max-time">Max cooking time (minutes)</label>
                    <input id="search-max-time" type="number" name="max_time" placeholder="e.g. 30">
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