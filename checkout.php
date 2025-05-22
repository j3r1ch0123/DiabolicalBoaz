<?php
// Start the session
session_start();

// Connect to the database
include 'db_connect.php';

// Center the website
echo '<div style="text-align: center;">';

// Display the logo on the top of the page
echo '<img src="images/logo.jpeg" alt="DiabolicalBoaz Logo" id="logo">';

// Use the style.css page to style the website
echo '<link rel="stylesheet" href="style.css">';

// Make the text grass green
echo '<style>body { color: green; }</style>';

// Sanitize the input to the database to avoid SQL injections and XSS attacks
function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

// Make sure user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>Please log in to adopt.</p>";
    echo '<p><a href="index.php">Return to Home</a></p>';
    exit();
}

// Get user ID from session username
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "User not found.";
    exit();
}
$user_id = $res->fetch_assoc()['id'];

// Get the form data
$name = sanitizeInput($_POST['name']);
$description = sanitizeInput($_POST['description']);
$image = sanitizeInput($_POST['image']);

// Get the price of the reptile from the database
$stmt = $conn->prepare("SELECT price FROM reptiles WHERE name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
$price = $result->fetch_assoc()['price'] ?? 0.00;

// Add the chosen reptile to the cart with user_id and price
$stmt = $conn->prepare("INSERT INTO cart (user_id, name, description, image, price) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isssd", $user_id, $name, $description, $image, $price);
$stmt->execute();

// Prompt the user to pay for the adoption
echo '<form action="pay.php" method="post">';
echo '<label for="amount">Amount:</label>';
echo '<input type="number" id="amount" name="amount" value="' . htmlspecialchars($price) . '" readonly>';
echo '<input type="submit" value="Pay">';
echo '</form>';

echo '</div>';

$conn->close();
?>
