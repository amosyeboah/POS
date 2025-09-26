<?php
// db_connect.php - Database connection configuration

$host = 'localhost'; // Your database host
$db   = 'mobpos';    // Your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log the error for debugging purposes (e.g., to a file)
    error_log("Database connection failed: " . $e->getMessage());
    // Display a user-friendly error message
    die("Connection to the database failed. Please try again later.");
}
?>
