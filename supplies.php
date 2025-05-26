<?php
// Connect to the database
include("db_connect.php");
// header
include("header.php");

function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

// Prepare to select everything from the supplies table
$stmt = $conn->prepare("SELECT * FROM supplies");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DiabolicalBoaz</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Center container */
        .container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2em;
        }
        th, td {
            border: 1px solid #444;
            padding: 0.5em;
        }
        img {
            max-width: 150px;
            height: auto;
        }

        /* Form styling */
        form {
            text-align: left; /* Align labels and inputs left inside the centered container */
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #444;
            padding: 1em;
            background-color: #111;
            color: #0f0;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-top: 1em;
            font-weight: bold;
        }
        input[type="text"],
        textarea,
        input[type="file"],
        input[type="number"] {
            width: 100%;
            padding: 0.4em;
            margin-top: 0.3em;
            background-color: #222;
            border: 1px solid #0f0;
            color: #0f0;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            margin-top: 1.5em;
            background-color: #0f0;
            color: #000;
            border: none;
            padding: 0.7em 1.2em;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #0c0;
        }
    </style>
</head>
<body>
    <div class="container">

    <h1>Welcome to DiabolicalBoaz</h1>
    <p>DiabolicalBoaz is a website designed to rehome reptiles.</p>

    <!-- Supplies Table -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo sanitizeInput($row['description']); ?></td>
                    <td>
                        <img src="<?php echo sanitizeInput($row['image']); ?>" alt="Supply image">
                    </td>
                    <td><?php echo sanitizeInput($row['price']); ?></td>
                    <td><?php echo sanitizeInput($row['quantity']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>

    </table>

    <!-- Supplies Form -->
    <form action="checkout.php" method="post" enctype="multipart/form-data">
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4" required></textarea>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" min="0" required>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" min="0" required>

        <input type="submit" value="Checkout">
    </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
