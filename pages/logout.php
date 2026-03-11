<?php
require_once __DIR__ . '/../includes/auth.php'; // load session/auth helper functions - using __DIR__ for absolute path reliability

startSession(); // ensure the session is active so it can be read and destroyed

// Clear all session data
$_SESSION = []; // overwrite the session array with an empty one, removing all stored values

// Destroy the session cookie
if (ini_get('session.use_cookies')) { // check whether the session is stored in a cookie
    $params = session_get_cookie_params(); // retrieve the current cookie's path, domain, and security settings
    setcookie(
        session_name(), '', time() - 42000, // set the cookie value to empty with an expiry in the past to delete it
        $params['path'], $params['domain'], // use the same path and domain the cookie was originally set on
        $params['secure'], $params['httponly'] // preserve the original secure and httponly flags
    );
}

session_destroy(); // delete the session data from the server

header('Location: /recipe-app/pages/login.php'); // redirect the browser to the login page
exit(); // stop execution so no further output is sent
