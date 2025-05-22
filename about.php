<?php
include 'db_connect.php';
include 'header.php';

// Use the style sheet
echo '<link rel="stylesheet" href="style.css">';

// Center everything
echo '<div style="text-align: center;">';

// Output HTML for the About page
echo '<h1>About</h1>';
echo '<p>In order to adopt from DiabolicalBoaz, you must be 18 years or older.</p>';
echo '<p>To adopt, you must provide us with a picture of your enclosure.</p>';
echo '<p>Alternatively, you could purchase an enclosure from us.</p>';
echo '<p>The picture of the enclosure must have you in it, and the enclosure must be in good condition.</p>';
echo '<p>Prices depend on the chosen reptile.</p>';
echo '<p>You can also view the reptiles available for adoption on our <a href="reptiles.php">Reptiles</a> page.</p>';
echo '<p>If you have any more questions, please contact us through the <a href="contact.php">Contact</a> page.</p>';
echo '<p>Payments are to be submitted through the Cash App account $NXXXZE.</p>';
echo '<p>Local pickup only, we do not ship reptiles.</p>';
echo '<p>Thank you for visiting DiabolicalBoaz!</p>';

// Return to the home page
echo '<p><a href="index.php">Return to Home</a></p>';

// Close the center div
echo '</div>';
?>
