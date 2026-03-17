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

// Keep server-side validation aligned with the client-side checks for full name and password strength.
// Full name: 2-100 letters, allowing spaces, apostrophes and hyphens between name parts.
$fullNamePattern = "/^(?=.{2,100}$)[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u";
// Password: minimum 8 characters with at least one lowercase letter, uppercase letter and digit.
$passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // only process the form on a POST request
    $name = trim($_POST['name'] ?? ''); // retrieve and strip whitespace from the submitted name
    $email = trim($_POST['email'] ?? ''); // retrieve and strip whitespace from the submitted email
    $password = $_POST['password'] ?? ''; // retrieve the submitted password (no trimming — spaces may be intentional)
    $confirm = $_POST['confirm_password'] ?? ''; // retrieve the confirmation password

    // Server-side validation
    if (empty($name)) { // check that a name was provided
        $errors[] = 'Name is required.'; // validation error message
    } elseif (preg_match($fullNamePattern, $name) !== 1) {
        $errors[] = 'Full name must contain at least 2 letters and can only include letters, spaces, apostrophes, and hyphens.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { // check the email is present and correctly formatted
        $errors[] = 'A valid email address is required.'; // validation error message
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (preg_match($passwordPattern, $password) !== 1) { // enforce a minimum password standard for new accounts
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.';
    }
    if (empty($confirm)) {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirm) { // check that both password fields match
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
<?php // Accessibility: dynamic page title provides clearer context in browser tabs and assistive tech history. ?>
<?php $pageTitle = 'Create Account'; ?>
<?php require_once __DIR__ . '/../includes/header.php'; // include site header - using __DIR__ for absolute path reliability ?>
<!-- Accessibility: id="main-content" + tabindex="-1" allows the skip link to move keyboard focus directly to primary content. -->
<main id="main-content" class="container" tabindex="-1"> <!-- main content area for the registration form -->
   
    <h1>Create your free account</h1> <!-- page heading -->
    

    <?php if (!empty($errors)): ?> <!-- only render the error list if there are errors -->
            <!-- Accessibility: role="alert" announces validation errors immediately to screen readers. -->
        <ul class="error-list" role="alert"> <!-- unordered list to display validation errors -->
            <?php foreach ($errors as $error): ?> <!-- loop through each error message -->
                <li><?php echo htmlspecialchars($error); ?></li> <!-- output each error, escaped to prevent XSS -->
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($success): ?> <!-- only render the success message if registration completed -->
        <!-- Accessibility: role="status" with aria-live="polite" announces success without interrupting current speech. -->
        <p class="success-msg" role="status" aria-live="polite"><?php echo $success; ?></p>
        <!-- display the success message with login link (HTML is intentional here) -->
    <?php endif; ?>
    

<div class="login-layout">   <!-- FLEX WRAPPER -->
 <div class="auth-container"> <!-- container for the registration form -->
    <!-- data-client-validation is used by main.js to attach the register-specific client checks -->
    <form id="register-form" method="POST" action="" novalidate data-client-validation="register">
        <!-- form submits via POST to this same page; novalidate disables browser built-in validation -->
        <label for="name">Full Name</label> <!-- label linked to the name input -->
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required autocomplete="name">
        <!-- name field; repopulates on failed submission, escaped to prevent XSS -->

        <label for="email">Email Address</label> <!-- label linked to the email input -->
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
            required autocomplete="email" inputmode="email" spellcheck="false" autocapitalize="off"> <!-- email field; repopulates on failed submission, escaped to prevent XSS -->

        <label for="password">Password</label> <!-- label linked to the password input -->
        <input type="password" id="password" name="password" required autocomplete="new-password" aria-describedby="register-password-hint">
        <!-- password field; value is never repopulated for security -->
        <!-- Accessibility: aria-describedby links this input to helper text so password rules are announced on focus. -->
        <!-- Helper text explains the password rule before the user submits the form -->
        <p class="field-hint" id="register-password-hint">Use at least 8 characters with an uppercase letter, a lowercase letter, and a number.</p>

        <label for="confirm_password">Confirm Password</label> <!-- label linked to the confirm password input -->
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
        <!-- confirmation field; value is never repopulated for security -->

        <button type="submit">Register</button> <!-- submits the form -->

        <p>Already have an account? <a href="/recipe-app/pages/login.php">Log in</a></p> <!-- link to the login page -->
    </form>
    </div> <!-- end of auth-container -->
    <aside class="auth-sidebar"> <!-- sidebar with additional information -->
       <h2>Why join The Recipe Library?</h2>

        <ul>
            <li>Search recipes easily</li>
            <li>Save your favourite dishes</li>
            <li>Rate recipes you try</li>
            <li>Share feedback with the community</li>
        </ul>

        <img src="/recipe-app/images/register-illustration.jpg"
             alt="fresh ingredients"
             class="sidebar-image">
    </aside>
</div> <!-- end of FLEX WRAPPER -->
    
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; // include site footer - using __DIR__ for absolute path reliability ?>