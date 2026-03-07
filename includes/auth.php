<?php

// Start a session if one hasn't started yet
function startSession() {
    if (session_status() === PHP_SESSION_NONE) { // only start if no session is active
        session_start(); // initialise the session superglobal $_SESSION
    }
}

// Check if the current user is logged in
function isLoggedIn() {
    startSession(); // ensure session is available before reading it
    return isset($_SESSION['user_id']); // true if user_id was set during login
}

//Redirect to login page if the user is not logged in. Use this at the top of any protected page.
function requireLogin() {
    if (!isLoggedIn()) { // check whether a valid session exists
        header('Location: /recipe-app/pages/login.php'); // send redirect header to browser
        exit(); // stop execution so no further output is sent
    }
}

// Get the current user's ID
function getCurrentUserId() {
    startSession(); // ensure session is available before reading it
    return $_SESSION['user_id'] ?? null; // return user_id or null if not set
}

// Get the current user's name
function getCurrentUserName() {
    startSession(); // ensure session is available before reading it
    return $_SESSION['user_name'] ?? null; // return user_name or null if not set
}
