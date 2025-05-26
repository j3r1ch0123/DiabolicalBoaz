<?php
include 'db_connect.php';

function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    header("Location: index.php?error=empty_fields");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email detected: $email");
    header("Location: index.php?error=invalid_email");
    exit();
}

// Optional: Check if email already exists
$stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    $stmtCheck->close();
    header("Location: index.php?error=email_exists");
    exit();
}
$stmtCheck->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

function sendConfirmationEmail($email) {
    $apiKey = $_ENV['API_KEY'];
    if (!$apiKey) {
        error_log("Missing API Key");
        return false;
    }

    $url = 'https://api.brevo.com/v3/smtp/email';
    $data = [
        'sender' => [
            'name' => 'DiabolicalBoaz',
            'email' => 'wakinsv@gmail.com',
        ],
        'to' => [
            ['email' => $email]
        ],
        'subject' => 'Account Created',
        'htmlContent' => "<html><body>
            Thank you for registering with DiabolicalBoaz! Your account has been successfully created.
            You can now log in with your username and password.
            <a href='https://diabolicalboaz.com/index.php'>Login</a>
        </body></html>"
    ];

    $headers = [
        'api-key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    error_log("Brevo Response: $response");
    error_log("HTTP Code: $httpCode");

    curl_close($ch);
    return $httpCode === 201;
}

// Start transaction
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    $stmt->execute();

    $emailSent = sendConfirmationEmail($email);

    if ($emailSent) {
        // Commit only if email sent successfully
        $conn->commit();
        header("Location: index.php?registered=1");
        exit();
    } else {
        // Rollback insert if email fails
        $conn->rollback();
        header("Location: index.php?error=email_send_failed");
        exit();
    }
} catch (Exception $e) {
    // Something went wrong — rollback
    $conn->rollback();
    error_log("Transaction failed: " . $e->getMessage());
    header("Location: index.php?error=db_error");
    exit();
}

$stmt->close();
$conn->close();
?>
