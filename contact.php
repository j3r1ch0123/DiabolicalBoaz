<?php 
// Connect to the database
include 'db_connect.php';
include 'header.php';

// Use the style.css page to style the website
echo '<link rel="stylesheet" href="style.css">';

// Handle form submission BEFORE outputting any HTML
if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['message'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Prepare and execute insertion
    $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    $stmt->execute();

    // Redirect to home page AFTER saving message
    header("Location: index.php");
    exit();
}

// Output the form and other HTML
echo '<div style="text-align: center;">';
echo '<form action="contact.php" method="post"><br>';
echo '<label for="name">Name:</label><br>';
echo '<input type="text" id="name" name="name" required><br>';
echo '<label for="email">Email:</label><br>';
echo '<input type="email" id="email" name="email" required><br>';
echo '<label for="message">Message:</label><br>';
echo '<textarea id="message" name="message" rows="4" required></textarea><br>';
echo '<input type="submit" value="Submit"><br>';
echo '</form>';
echo '<p><a href="index.php">Return to Home</a></p>';
echo '</div>';
?>
