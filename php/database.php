<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$db = getenv('DB_NAME') ?: 'campus_store';

$conn = mysqli_connect($servername, $username, $password, $db);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
?>
