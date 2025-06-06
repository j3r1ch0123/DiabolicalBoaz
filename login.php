<?php
session_start();
include 'db_connect.php';

function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

$maxAttempts = 3;
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= $maxAttempts) {
    header("Location: index.php?error=locked");
    exit();
}

$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: index.php?error=empty");
    exit();
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['login_attempts']++;
    header("Location: index.php?error=wrong&attempts=" . $_SESSION['login_attempts']);
    exit();
}

$row = $result->fetch_assoc();
$hashed_password = $row['password'];
$user_id = $row['id'];  // get user id here

if (password_verify($password, $hashed_password)) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['username'] = $username;
    $_SESSION['user_id'] = $user_id;  // store user id in session
    header("Location: index.php");
    exit();
}

$conn->close();
?>
