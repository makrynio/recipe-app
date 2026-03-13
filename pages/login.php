<?php
require_once __DIR__ . '/../includes/db.php'; // establish the database connection ($conn) - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/auth.php'; // load session/auth helper functions - using __DIR__ for absolute path reliability

startSession(); // ensure a session is active before reading or writing $_SESSION

// Redirect if already logged in
if (isLoggedIn()) { // skip the login page if the user already has a valid session
    header('Location: /recipe-app/pages/account.php'); // send the browser to the account page
    exit(); // stop execution so no further output is sent
}

$errors = []; // initialise an empty array to collect validation errors

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // only process the form on a POST request
    $email = trim($_POST['email'] ?? ''); // retrieve and strip whitespace from the submitted email
    $password = $_POST['password'] ?? ''; // retrieve the submitted password (no trimming — spaces may be intentional)

    if (empty($email) || empty($password)) { // check that both fields were filled in
        $errors[] = 'Please enter your email and password.'; // add a validation error message
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, full_name, password_hash FROM users WHERE email = ?'); // prepare query to avoid SQL injection
        mysqli_stmt_bind_param($stmt, 's', $email); // bind $email as a string parameter
        mysqli_stmt_execute($stmt); // run the query against the database
        mysqli_stmt_bind_result($stmt, $id, $name, $hashedPassword); // map result columns to PHP variables
        mysqli_stmt_fetch($stmt); // fetch the matching row into the bound variables
        mysqli_stmt_close($stmt); // free the statement resource

        if ($id && password_verify($password, $hashedPassword)) { // check the user exists and the password matches the stored hash
            // Credentials valid — start session
            $_SESSION['user_id'] = $id; // store the user's ID in the session
            $_SESSION['user_name'] = $name; // store the user's name in the session
            header('Location: /recipe-app/pages/account.php'); // redirect to the account page on success
            exit(); // stop execution so no further output is sent
        } else {
            // Generic message — don't reveal which field was wrong
            $errors[] = 'Invalid email or password.'; // add a generic error to avoid leaking whether the email exists
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; // include site header - using __DIR__ for absolute path reliability ?>
    <main class="container"> <!-- main content area for the login form -->
        <h1>Log In</h1> <!-- page heading -->

        <?php if (!empty($errors)): ?> <!-- only render the error list if there are errors -->
            <ul class="error-list"> <!-- unordered list to display validation errors -->
                <?php foreach ($errors as $error): ?> <!-- loop through each error message -->
                    <li><?php echo htmlspecialchars($error); ?></li> <!-- output each error, escaped to prevent XSS -->
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
<div class="login-layout">   <!-- FLEX WRAPPER -->
    <div class="auth-container"> <!-- container for the login form -->
        <form id="login-form" method="POST" action="" novalidate> <!-- form submits via POST to this same page; novalidate disables browser built-in validation -->
            <label for="email">Email Address</label> <!-- label linked to the email input -->
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required> <!-- email field; repopulates on failed submission, escaped to prevent XSS -->

            <label for="password">Password</label> <!-- label linked to the password input -->
            <input type="password" id="password" name="password" required> <!-- password field; value is never repopulated for security -->

            <button type="submit">Log In</button> <!-- submits the form -->
             <p>Don't have an account? <a href="/recipe-app/pages/register.php">Register</a></p> <!-- link to the registration page -->
        </form>
    </div> <!-- end of auth-container -->
        
      <aside class="auth-sidebar">
          <h2>Log in to explore recipes, save favourites and rate dishes.</h2>

           <img src="/recipe-app/images/login-illustration.jpg"
               alt="Chef cooking in a kitchen"
               class="sidebar-image">
       </aside>
            
          
</div> <!-- end of FLEX WRAPPER -->        
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; // include site footer - using __DIR__ for absolute path reliability ?>
