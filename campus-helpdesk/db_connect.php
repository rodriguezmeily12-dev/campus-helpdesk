<?php
// db_connect.php
// Connects to the MySQL database using PDO.
// This file is included at the top of every page that needs the database.

// Database settings (XAMPP defaults). Change these if your MySQL login is different.
$host     = 'localhost';
$dbname   = 'helpdesk_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Throw an exception if a query fails, so errors don't go unnoticed
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Return rows as associative arrays, e.g. $row['full_name']
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Use real prepared statements (the data is sent separately from the SQL)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Save the real error to the PHP error log, but don't show database details on the page
    error_log('Database connection failed: ' . $e->getMessage());
    die('Could not connect to the database. Make sure MySQL is running and the settings in db_connect.php are correct.');
}
