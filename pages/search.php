<?php
require_once '../includes/db.php';
require_once '../includes/auth.php'; // load session/auth helper functions

requireLogin();

$categories = $conn->query("SELECT * FROM categories");

require_once '../includes/header.php';
?>

    <main>

        <h2>Search Recipes</h2>

        <form action="results.php" method="GET">

            <label>Recipe name</label>
            <input type="text" name="name" placeholder="e.g. pizza">

            <label>Category</label>
            <select name="category">

                <option value="">Any category</option>

                <?php while ($row = $categories->fetch_assoc()): ?>

                    <option value="<?php echo $row['id']; ?>">
                        <?php echo $row['name']; ?>
                    </option>

                <?php endwhile; ?>

            </select>

            <label>Ingredient</label>
            <input type="text" name="ingredient" placeholder="e.g. tomato">

            <label>Sort results</label>

            <select name="sort">

                <option value="">Default</option>
                <option value="rating">Rating</option>
                <option value="time">Cooking time</option>
                <option value="name">Alphabetical</option>

            </select>

            <button type="submit">Search</button>

        </form>

    </main>

<?php require_once '../includes/footer.php'; ?>