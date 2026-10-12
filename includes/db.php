<?php
// load database credentials (DB_HOST, DB_USER, DB_PASS, DB_NAME): config/db.php if present, otherwise the example template
$dbConfig = file_exists(__DIR__ . '/../config/db.php') ? __DIR__ . '/../config/db.php' : __DIR__ . '/../config/db.example.php';
require_once $dbConfig;

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME); // open a MySQL connection using the configured credentials

if (!$conn) { // check whether the connection attempt failed
    die("Connection failed: " . mysqli_connect_error()); // halt execution and print the connection error message
}