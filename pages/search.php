<?php
require_once __DIR__ . '/../includes/db.php'; // establish the database connection ($conn) - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/auth.php'; // load session/auth helper functions - using __DIR__ for absolute path reliability

requireLogin();

$categories = $conn->query("SELECT * FROM categories");

require_once __DIR__ . '/../includes/header.php'; // include site header - using __DIR__ for absolute path reliability
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

<?php require_once __DIR__ . '/../includes/footer.php'; // include site footer - using __DIR__ for absolute path reliability ?>