<?php
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Connect to the database
include 'db_connect.php';
include 'header.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: index.php?error=Please log in first.');
    exit;
}

// Check if user is admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if ($is_admin != 1) {
    header('Location: index.php?error=You do not have permission to access this page.');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $name = $_POST['name'] ?? ''; // Optional for enclosure

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedTypes)) {
            $newFileName = uniqid('img_', true) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagePath = $destPath;
            } else {
                echo "Error moving uploaded file.";
            }
        } else {
            echo "Invalid file type.";
        }
    }

    if (isset($_POST['add_reptile'])) {
        if (empty($name)) {
            die("Reptile name is required.");
        }
        $stmt = $conn->prepare("INSERT INTO reptile (name, description, image, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $name, $description, $imagePath, $price);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['add_enclosure'])) {
        $stmt = $conn->prepare("INSERT INTO enclosure (description, image, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $name, $description, $imagePath, $price);
        $stmt->execute();
        $stmt->close();
    }

    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle message deletion
if (isset($_GET['delete'])) {
    $message_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Delete reptile by name
if (isset($_POST['delete_reptile'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid CSRF token');

    $name = $_POST['name'];
    $stmt = $conn->prepare("DELETE FROM reptile WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
}

// Delete enclosure by description
if (isset($_POST['delete_enclosure'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Invalid CSRF token');

    $description = $_POST['description'];
    $stmt = $conn->prepare("DELETE FROM enclosure WHERE description = ?");
    $stmt->bind_param("s", $description);
    $stmt->execute();
    $stmt->close();
}

// Fetch messages from the database
$stmt = $conn->prepare("SELECT * FROM messages");
$stmt->execute();
$messages = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3rem;
            font-family: Arial, sans-serif;
            background: #000000;
            color: white;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
        }

        form {
            background: #111;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 0 24px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        label {
            font-weight: bold;
            font-size: 1.1rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1.05rem;
            background-color: #222;
            color: white;
            width: 100%;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.85rem;
            font-size: 1.2rem;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        input[type="submit"] + input[type="submit"] {
            margin-top: 0.75rem;
        }
    </style>
</head>
<body>
    <h1>Admin Page</h1>

    <h2>Add Reptile</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label for="name_reptile">Name:</label>
            <input type="text" id="name_reptile" name="name" required>

            <label for="description_reptile">Description:</label>
            <textarea id="description_reptile" name="description" required></textarea>

            <label for="image_reptile">Image:</label>
            <input type="file" id="image_reptile" name="image" accept="image/*" required>

            <label for="price_reptile">Price:</label>
            <input type="number" step="0.01" id="price_reptile" name="price" required>

            <input type="submit" name="add_reptile" value="Add Reptile">
        </form>

        <h2>Add Enclosure</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <label for="description_enclosure">Description:</label>
            <textarea id="description_enclosure" name="description" required></textarea>

            <label for="image_enclosure">Image:</label>
            <input type="file" id="image_enclosure" name="image" accept="image/*" required>

            <label for="price_enclosure">Price:</label>
            <input type="number" step="0.01" id="price_enclosure" name="price" required>

            <input type="submit" name="add_enclosure" value="Add Enclosure">
        </form>

    <h2>Messages</h2>
    <ul>
        <?php foreach ($messages as $message): ?>
            <li>
                <strong>ID:</strong> <?php echo htmlspecialchars($message['id']); ?><br>
                <strong>Name:</strong> <?php echo htmlspecialchars($message['name']); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?><br>
                <strong>Message:</strong> <?php echo nl2br(htmlspecialchars($message['message'])); ?><br>
                <strong>Submitted at:</strong> <?php echo htmlspecialchars($message['submitted_at']); ?><br>
            </li>
        <?php endforeach; ?>
    </ul>

    <form method="get">
        <label for="delete">Delete Message by ID:</label>
        <input type="number" id="delete" name="delete" required>
        <input type="submit" value="Delete">
    </form>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label for="name">Delete Reptile by name:</label>
        <input type="text" id="name" name="name" required>
        <input type="submit" name="delete_reptile" value="Delete Reptile">
    </form>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label for="description">Delete Enclosure by description:</label>
        <input type="text" id="description" name="description" required>
        <input type="submit" name="delete_enclosure" value="Delete Enclosure">
    </form>
</body>
</html>
