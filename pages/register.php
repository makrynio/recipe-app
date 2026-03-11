<?php
require_once __DIR__ . '/../includes/db.php'; // establish the database connection ($conn) - using __DIR__ for absolute path reliability
require_once __DIR__ . '/../includes/auth.php'; // load session/auth helper functions - using __DIR__ for absolute path reliability

startSession(); // ensure a session is active before reading or writing $_SESSION

// Redirect if already logged in
if (isLoggedIn()) { // skip registration if the user already has a valid session
    header('Location: /recipe-app/pages/account.php'); // send the browser to the account page
    exit(); // stop execution so no further output is sent
}

$errors = []; // initialise an empty array to collect validation errors
$success = ''; // initialise an empty success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // only process the form on a POST request
    $name = trim($_POST['name'] ?? ''); // retrieve and strip whitespace from the submitted name
    $email = trim($_POST['email'] ?? ''); // retrieve and strip whitespace from the submitted email
    $password = $_POST['password'] ?? ''; // retrieve the submitted password (no trimming — spaces may be intentional)
    $confirm = $_POST['confirm_password'] ?? ''; // retrieve the confirmation password

    // Server-side validation
    if (empty($name)) { // check that a name was provided
        $errors[] = 'Name is required.'; // validation error message
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { // check the email is present and correctly formatted
        $errors[] = 'A valid email address is required.'; // validation error message
    }
    if (strlen($password) < 8) { // enforce a minimum password length of 8 characters
        $errors[] = 'Password must be at least 8 characters.'; // validation error message
    }
    if ($password !== $confirm) { // check that both password fields match
        $errors[] = 'Passwords do not match.'; // validation error message
    }

    if (empty($errors)) { // only query the database if all validation passed
        // Check if email already exists
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?'); // prepare query to check for a duplicate email
        mysqli_stmt_bind_param($stmt, 's', $email); // bind $email as a string parameter
        mysqli_stmt_execute($stmt); // run the query against the database
        mysqli_stmt_store_result($stmt); // buffer the result so num_rows can be read

        if (mysqli_stmt_num_rows($stmt) > 0) { // if a row was returned the email is already registered
            $errors[] = 'An account with that email already exists.'; // duplicate-email error message
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // hash the password using bcrypt
            $stmt = mysqli_prepare($conn, 'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)'); // prepare the insert query
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashedPassword); // bind name, email, and hashed password as strings

            if (mysqli_stmt_execute($stmt)) { // try to insert the new user row
                $success = 'Account created successfully. <a href="/recipe-app/pages/login.php">Log in here</a>.'; // set a success message with a login link
            } else {
                $errors[] = 'Something went wrong. Please try again.'; // generic error if the insert failed
            }
        }
        mysqli_stmt_close($stmt); // free the statement resource
    }
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; // include site header - using __DIR__ for absolute path reliability ?>
    <main class="auth-container"> <!-- main content area for the registration form -->
        <h1>Create an Account</h1> <!-- page heading -->

        <?php if (!empty($errors)): ?> <!-- only render the error list if there are errors -->
            <ul class="error-list"> <!-- unordered list to display validation errors -->
                <?php foreach ($errors as $error): ?> <!-- loop through each error message -->
                    <li><?php echo htmlspecialchars($error); ?></li> <!-- output each error, escaped to prevent XSS -->
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($success): ?> <!-- only render the success message if registration completed -->
            <p class="success-msg"><?php echo $success; ?></p> <!-- display the success message with login link (HTML is intentional here) -->
        <?php endif; ?>

        <form id="register-form" method="POST" action="" novalidate> <!-- form submits via POST to this same page; novalidate disables browser built-in validation -->
            <label for="name">Full Name</label> <!-- label linked to the name input -->
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required> <!-- name field; repopulates on failed submission, escaped to prevent XSS -->

            <label for="email">Email Address</label> <!-- label linked to the email input -->
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required> <!-- email field; repopulates on failed submission, escaped to prevent XSS -->

            <label for="password">Password</label> <!-- label linked to the password input -->
            <input type="password" id="password" name="password" required> <!-- password field; value is never repopulated for security -->

            <label for="confirm_password">Confirm Password</label> <!-- label linked to the confirm password input -->
            <input type="password" id="confirm_password" name="confirm_password" required> <!-- confirmation field; value is never repopulated for security -->

            <button type="submit">Register</button> <!-- submits the form -->
        </form>

        <p>Already have an account? <a href="/recipe-app/pages/login.php">Log in</a></p> <!-- link to the login page -->
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; // include site footer - using __DIR__ for absolute path reliability ?>
