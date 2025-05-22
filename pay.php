<?php
session_start();  // Make sure session is started to get logged-in user

include 'db_connect.php';
include 'header.php';

// Use the style sheet
echo '<link rel="stylesheet" href="style.css">';

// Center everything
echo '<div style="text-align: center;">';

// Basic checks
if (!isset($_SESSION['username'])) {
    echo "Please log in to pay.";
    exit();
}

// Get user id from username
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Fetch cart items for this user
// Assuming you have a user_id column in your cart table to relate cart to users
// If not, you'll need to adjust this accordingly
$stmt = $conn->prepare("SELECT id, name, description, image, price FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

if ($cart_items->num_rows === 0) {
    echo "Your cart is empty.";
    exit();
}

// Process each cart item: insert into orders and remove from cart
while ($item = $cart_items->fetch_assoc()) {
    $reptile_name = $item['name'];

    // Get reptile id and price from reptiles table by name (assuming name is unique)
    $stmt2 = $conn->prepare("SELECT id, price FROM reptiles WHERE name = ?");
    $stmt2->bind_param("s", $reptile_name);
    $stmt2->execute();
    $res = $stmt2->get_result();
    if ($res->num_rows === 0) {
        continue;  // Skip if reptile not found
    }
    $reptile = $res->fetch_assoc();
    $reptile_id = $reptile['id'];
    $price = $reptile['price'];

    // Insert into orders (quantity=1 as no quantity in cart table)
    $status = 'pending';
    $quantity = 1;
    $total_price = $price * $quantity;
    $stmt3 = $conn->prepare("INSERT INTO orders (user_id, reptile_id, quantity, total_price, status) VALUES (?, ?, ?, ?, ?)");
    $stmt3->bind_param("iiids", $user_id, $reptile_id, $quantity, $total_price, $status);
    $stmt3->execute();

    // Remove from cart
    $stmt4 = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt4->bind_param("i", $item['id']);
    $stmt4->execute();
}

// Order placed
echo "<p>Order placed. Please pay $" . $total_price . " to the Cash App account @NXXXZE.</p>";

// Add a link so the user could pay through the cash app
echo '<br><a href="https://cash.app/$NXXXZE">Pay Now</a>';

$conn->close();
?>
