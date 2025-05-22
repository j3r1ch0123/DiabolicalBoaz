<?php
// Connect to the database
include 'db_connect.php';
// Use the header file
include 'header.php';

// Display the payment confirmation page
echo '<h1>Payment Confirmation</h1>';
echo '<p>To complete your order, please make the following payment:</p>';

// If the cart is empty, redirect to the home page
if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

// Else, display the payment details
else {
    echo '<p>Amount: $' . $_POST['amount'] . '</p>';
    echo '<p>Name: ' . $_POST['name'] . '</p>';
    echo '<p>Address: ' . $_POST['address'] . '</p>';
    echo '<p>City: ' . $_POST['city'] . '</p>';
    echo '<p>State: ' . $_POST['state'] . '</p>';
    echo '<p>Zip Code: ' . $_POST['zip'] . '</p>';
}

// Provide the buyer with the owner's Cash App information
echo '<p>Please use the following Cash App information to make the payment:</p>';
echo '<p>Owner: @NXXZE</p>';
echo '<p>We will contact you to confirm the payment.</p>';

?>