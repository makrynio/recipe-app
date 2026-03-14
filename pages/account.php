<?php
// ----------------------
// Include dependencies
// ----------------------

require_once __DIR__ . '/../includes/db.php'; // establish the database connection ($conn) - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/auth.php'; // load session/auth helper functions - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/favourites.php'; // load favourites helper functions - using __DIR__ for absolute path reliability

requireLogin(); // redirect to login page if the user is not logged in

$userId = getCurrentUserId(); // retrieve the logged-in user's ID from the session

// ----------------------
// Handle profile updates
// ----------------------

// Handle profile update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $newName = trim($_POST['full_name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    if ($newName !== '' && filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $updateStmt = mysqli_prepare($conn, 'UPDATE users SET full_name = ?, email = ? WHERE id = ?');
        mysqli_stmt_bind_param($updateStmt, 'ssi', $newName, $newEmail, $userId);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    }
}

// ----------------------
// Fetch account data
// ----------------------

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
        <p>Manage your profile and revisit your favourite recipes anytime.</p> <!-- brief description of the page's purpose -->

        <div class="account-sections">
        <section class="account-info"> <!-- section displaying the user's profile details -->
            <div class="profile-text">
                <h2>Profile</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name); // escape output to prevent XSS ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); // escape output to prevent XSS ?></p>
                <p><strong>Member since:</strong> <?php echo date('d F Y', strtotime($createdAt)); // format the stored timestamp as a readable date ?></p>

                <button id="edit-profile-btn" type="button" class="button-secondary">Edit My Profile</button>

                <form id="edit-profile-form" method="post" class="hidden">
                    <input type="hidden" name="edit_profile" value="1">
                    <label>
                        Name
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </label>
                    <button type="submit" class="button-secondary">Save Profile</button>
                    <button id="cancel-edit-btn" type="button" class="button-secondary">Cancel</button>
                </form>
            </div>
            <img src="/recipe-app/images/profile-picture-placeholder.jpg" alt="Profile Picture" class="profile-picture">
        </section>

        <section class="account-favourites"> <!-- section that will list the user's saved recipes -->
            <h2>Saved Recipes</h2>
            <?php if ($favourites->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $favourites->fetch_assoc()): ?>
                        <li>
                            <a href="/recipe-app/pages/recipe.php?id=<?php echo $row['id']; ?>">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Recipe thumbnail" class="recipe-thumbnail">
                                <?php echo htmlspecialchars($row['title']); ?> — <?php echo htmlspecialchars($row['category_name']); ?> <small>(<?php echo $row['cook_time_min']; ?> min)</small>
                            </a>
                                
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no saved recipes yet.</p>
            <?php endif; ?>
        </section>
        </div>
         <a href="/recipe-app/pages/search.php" class="cta-button center-button">
        Browse more recipes 
    </a>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; // include site footer - using __DIR__ for absolute path reliability ?>