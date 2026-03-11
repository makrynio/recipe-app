<?php

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


// Include the website header (navigation bar and layout)
require_once __DIR__ . '/../includes/header.php';

?>

<main>

    <h2>Search Recipes</h2>

    <!-- 
Search form allowing users to filter recipes using multiple criteria.
The more flexible the search functionality, the better the application
fulfills the coursework requirements.
-->

    <form action="results.php" method="GET">

        <!-- ===================== -->
        <!-- Recipe name search -->
        <!-- ===================== -->

        <label>Recipe name</label>
        <input type="text" name="name" placeholder="e.g. pizza">


        <!-- ===================== -->
        <!-- Category filter -->
        <!-- ===================== -->

        <label>Category</label>

        <select name="category">

            <option value="">Any category</option>

            <?php while ($row = $categories->fetch_assoc()): ?>

                <option value="<?php echo $row['id']; ?>">
                    <?php echo htmlspecialchars($row['name']); ?>
                </option>

            <?php endwhile; ?>

        </select>


        <!-- ===================== -->
        <!-- Ingredient search -->
        <!-- ===================== -->

        <label>Ingredient</label>
        <input type="text" name="ingredient" placeholder="e.g. tomato">


        <!-- ===================== -->
        <!-- Difficulty filter -->
        <!-- ===================== -->

        <label>Difficulty</label>

        <select name="difficulty">

            <option value="">Any difficulty</option>
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>

        </select>


        <!-- ===================== -->
        <!-- Cooking time filter -->
        <!-- ===================== -->

        <label>Max cooking time (minutes)</label>
        <input type="number" name="max_time" placeholder="e.g. 30">


        <!-- ===================== -->
        <!-- Sorting options -->
        <!-- ===================== -->

        <label>Sort results</label>

        <select name="sort">

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


        <!-- Submit search -->

        <button type="submit">Search</button>

    </form>

</main>

<?php

// Include the website footer
require_once __DIR__ . '/../includes/footer.php';

?>