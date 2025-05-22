<?php
    // Load Dotenv
    require_once __DIR__ . '/vendor/autoload.php';
    Dotenv\Dotenv::createImmutable(__DIR__)->load();

    // Use environment variables
    define('DB_SERVER', $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_USERNAME', $_ENV['DB_USER'] ?? '');
    define('DB_PASSWORD', $_ENV['DB_PASS'] ?? '');
    define('DB_DATABASE', $_ENV['DB_NAME'] ?? '');

    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
