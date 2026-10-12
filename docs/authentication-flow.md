# Authentication and session flow

How registration, login and sessions work in this app. Written by Thomas Makryniotis.

The $_SESSION array is the thread that connects all of these files together. It persists across requests — so when login.php sets $_SESSION['user_id'], account.php can read it on the next request. Without the session, PHP has no memory between page loads and every request starts from scratch. That's what startSession() at the top of every file is for — it reconnects PHP to that persistent session data.

Registration Flow

    1. First page load (GET request)

    PHP runs top to bottom
    require_once loads the DB and auth files
    startSession() starts a session
    isLoggedIn() check runs — assuming false, continues
    $errors = [] and $success = '' are set
    if POST — condition is false, entire block is skipped
    PHP hits the HTML, outputs a blank form with empty fields
    Browser renders it, user sees the registration form

    2. User fills in the form and clicks Register

    Browser collects all field values and sends a POST request to the same URL
    The PHP file starts executing again from the top

    3. Second execution (POST request)

    require_once, startSession(), isLoggedIn() all run again
    $errors = [] and $success = '' reset again
    if POST — condition is true this time, block executes
    Fields are extracted from $_POST and trimmed
    Validation runs — any failures push messages into $errors
    If $errors is empty, the database is queried
    Either $errors gets a message or $success gets set

    4. PHP finishes, HTML is generated

    If there were errors: error list renders, form repopulates with the submitted values
    If success: success message renders, form is still shown
    Finished HTML is sent to the browser

    5. User sees the result

    Errors displayed, fields pre-filled so they can correct and resubmit
    Or success message with the login link


    Browser requests register.php
            ↓
    register.php loads
            ↓
    require_once '../includes/db.php'        ← opens MySQL connection
    require_once '../includes/auth.php'      ← loads helper functions
            ↓
    startSession()                           ← starts PHP session
    isLoggedIn()?                            ← checks $_SESSION for user_id
        YES → redirect to account.php        ← already logged in, no need to register
        NO  → continue
            ↓
    $_SERVER['REQUEST_METHOD'] === 'POST'?
        NO  → just render the HTML form      ← first visit, nothing submitted yet
        YES → validate form inputs
                ↓
            any errors?
                YES → re-render form with error messages
                NO  → check if email exists in DB
                        ↓
                    email already exists?
                        YES → re-render form with error
                        NO  → password_hash() the password
                            INSERT INTO users
                            show success message with link to login.php

Login Flow

    Browser requests login.php
            ↓
    login.php loads
            ↓
    require_once '../includes/db.php'        ← opens MySQL connection
    require_once '../includes/auth.php'      ← loads helper functions
            ↓
    startSession()
    isLoggedIn()?
        YES → redirect to account.php
        NO  → continue
            ↓
    $_SERVER['REQUEST_METHOD'] === 'POST'?
        NO  → just render the HTML login form
        YES → get email and password from $_POST
                ↓
            fields empty?
                YES → re-render form with error
                NO  → SELECT user from DB where email matches
                        ↓
                    user found AND password_verify() passes?
                        NO  → re-render form, "Invalid email or password"
                        YES → $_SESSION['user_id'] = $id
                            $_SESSION['user_name'] = $name
                            redirect to account.php

Account Page Flow

    Browser requests login.php
            ↓
    login.php loads
            ↓
    require_once '../includes/db.php'        ← opens MySQL connection
    require_once '../includes/auth.php'      ← loads helper functions
            ↓
    startSession()
    isLoggedIn()?
        YES → redirect to account.php
        NO  → continue
            ↓
    $_SERVER['REQUEST_METHOD'] === 'POST'?
        NO  → just render the HTML login form
        YES → get email and password from $_POST
                ↓
            fields empty?
                YES → re-render form with error
                NO  → SELECT user from DB where email matches
                        ↓
                    user found AND password_verify() passes?
                        NO  → re-render form, "Invalid email or password"
                        YES → $_SESSION['user_id'] = $id
                            $_SESSION['user_name'] = $name
                            redirect to account.php

Logout Flow

    Browser requests logout.php
            ↓
    require_once '../includes/auth.php'
            ↓
    startSession()
            ↓
    $_SESSION = []                           ← wipe all session data
    setcookie()                              ← destroy the session cookie in browser
    session_destroy()                        ← destroy session on server
            ↓
    redirect to login.php


Security decisions:

- Passwords are hashed with password_hash() / verified with password_verify() — not stored as plain text
- All user output is wrapped in htmlspecialchars() to prevent XSS attacks
- Login gives a generic "Invalid email or password" message — never reveals which field was wrong
- Prepared statements with bound parameters throughout — prevents SQL injection

TODO:

- The favourites section in account.php is stubbed out waiting for Person 1's favourites table
- Client-side JS validation in main.js is Person 4's responsibility — the novalidate attribute on the forms means browser validation is suppressed so your server-side validation runs, and Person 4 replaces that with custom JS