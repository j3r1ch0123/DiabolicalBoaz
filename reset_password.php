<?php
include 'db_connect.php';
include 'header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

echo '<div style="text-align:center;">';

function sendPasswordResetEmail($email, $token) {
    $apiKey = $_ENV['API_KEY'];
    $url = 'https://api.brevo.com/v3/smtp/email'; // <-- Correct Brevo API endpoint

    $data = [
        'sender' => [
            'name' => 'DiabolicalBoaz',
            'email' => 'wakinsv@gmail.com',
        ],
        'to' => [
            ['email' => $email]
        ],
        'subject' => 'Password Reset Request',
        'htmlContent' => "<html><body>
            Click <a href='https://diabolicalboas.com/reset_password.php?token=$token'>here</a> to reset your password. This link is valid for 1 hour.
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

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Optional: log the response for debugging
    // error_log("Brevo response: $response");

    return $httpCode === 201; // 201 = Created (success)
}

// Helper to sanitize input
function clean($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Case 1: Request password reset email by submitting email
    if (isset($_POST['email']) && !isset($_POST['token'])) {
        $email = clean($_POST['email']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $stmt = $conn->prepare("UPDATE users SET password_reset_token = ?, token_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();

            if (sendPasswordResetEmail($email, $token)) {
                echo "Password reset link sent to $email.";
            } else {
                echo "Failed to send email.";
            }
        } else {
            echo "Email address not found.";
        }
    }

    // Case 2: User submitted new password with token
    elseif (isset($_POST['token'], $_POST['password'], $_POST['confirm_password'])) {
        $token = clean($_POST['token']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            echo "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            echo "Password must be at least 8 characters.";
        } else {
            // Validate token & expiry
            $stmt = $conn->prepare("SELECT email, token_expiry FROM users WHERE password_reset_token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $row = $res->fetch_assoc();
                $expiry = $row['token_expiry'];
                $email = $row['email'];

                if (strtotime($expiry) >= time()) {
                    // Token valid, update password
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("UPDATE users SET password = ?, password_reset_token = NULL, token_expiry = NULL WHERE email = ?");
                    $stmt->bind_param("ss", $hash, $email);
                    $stmt->execute();

                    echo "Password successfully reset! You may now <a href='index.php'>login</a>.";
                } else {
                    echo "Reset link expired. Please request a new one.";
                }
            } else {
                echo "Invalid reset token.";
            }
        }
    }
}
// Case 3: If token is in URL, show password reset form
elseif (isset($_GET['token'])) {
    $token = clean($_GET['token']);

    // Optional: Validate token exists and not expired before showing form
    $stmt = $conn->prepare("SELECT token_expiry FROM users WHERE password_reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (strtotime($row['token_expiry']) >= time()) {
            ?>

            <h1>Reset Password</h1>
            <form method="post" action="reset_password.php">
                <input type="hidden" name="token" value="<?php echo $token; ?>" />
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" required minlength="8" />
                <br />
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" />
                <br />
                <input type="submit" value="Reset Password" />
            </form>

            <?php
        } else {
            echo "Reset link expired. Please request a new one.";
        }
    } else {
        echo "Invalid reset token.";
    }
}
// Case 4: Show email submission form by default
else {
    ?>
    <h1>Forgot Password</h1>
    <form method="post" action="reset_password.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required />
        <input type="submit" name="submit" value="Submit" />
    </form>
    <p><a href="login.php">Login</a></p>
    <p><a href="register.php">Register</a></p>
    <p><a href="index.php">Home</a></p>
    <?php
}

echo '</div>';
?>