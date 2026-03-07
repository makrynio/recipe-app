<?php
require_once '../config/db.example.php'; // load database credentials (DB_HOST, DB_USER, DB_PASS, DB_NAME)

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME); // open a MySQL connection using the configured credentials

if (!$conn) { // check whether the connection attempt failed
    die("Connection failed: " . mysqli_connect_error()); // halt execution and print the connection error message
}