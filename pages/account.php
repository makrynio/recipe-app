<?php
require_once __DIR__ . '/../includes/db.php'; // establish the database connection ($conn) - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/auth.php'; // load session/auth helper functions - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/favourites.php'; // load favourites helper functions - using __DIR__ for absolute path reliability

requireLogin(); // redirect to login page if the user is not logged in

$userId = getCurrentUserId(); // retrieve the logged-in user's ID from the session

// Fetch user info
$stmt = mysqli_prepare($conn, 'SELECT full_name, email, created_at FROM users WHERE id = ?'); // prepare query to avoid SQL injection
mysqli_stmt_bind_param($stmt, 'i', $userId); // bind $userId as an integer parameter
mysqli_stmt_execute($stmt); // run the prepared statement against the database
mysqli_stmt_bind_result($stmt, $name, $email, $createdAt); // map result columns to PHP variables
mysqli_stmt_fetch($stmt); // fetch the single matching row into the bound variables
mysqli_stmt_close($stmt); // free the statement resource

$favourites = getUserFavourites($conn, $userId);
?>
<?php require_once __DIR__ . '/../includes/header.php'; // include site header - using __DIR__ for absolute path reliability ?>

    <main class="account-container container"> <!-- main content area for the account page -->
        <h1>My Account</h1> <!-- page heading -->

        <section class="account-info"> <!-- section displaying the user's profile details -->
            <h2>Profile</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); // escape output to prevent XSS ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); // escape output to prevent XSS ?></p>
            <p><strong>Member since:</strong> <?php echo date('d F Y', strtotime($createdAt)); // format the stored timestamp as a readable date ?></p>
        </section>

        <section class="account-favourites"> <!-- section that will list the user's saved recipes -->
            <h2>Saved Recipes</h2>
            <?php if ($favourites->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $favourites->fetch_assoc()): ?>
                        <li>
                            <a href="/recipe-app/pages/recipe.php?id=<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </a>
                            — <?php echo htmlspecialchars($row['category_name']); ?>
                            (<?php echo $row['cook_time_min']; ?> min)
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no saved recipes yet.</p>
            <?php endif; ?>
        </section>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; // include site footer - using __DIR__ for absolute path reliability ?>