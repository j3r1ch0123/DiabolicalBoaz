<?php
session_start();
include 'db_connect.php';  // Your DB connection file

include 'header.php';  // Includes logo and nav
?>

<?php if (isset($_GET['error'])): ?>
    <p style="color:red;">
        <?php
        switch ($_GET['error']) {
            case 'empty':
                echo "Please enter both username and password.";
                break;
            case 'wrong':
                echo "Incorrect username or password.";
                if (isset($_GET['attempts'])) {
                    echo " Attempt #" . (int)$_GET['attempts'];
                }
                break;
            case 'locked':
                echo "Too many failed login attempts. Please try again later.";
                break;
            default:
                echo "An unknown error occurred.";
                break;
        }
        ?>
    </p>
<?php endif; ?>

<?php
// If the user just registered, display a success message
if (isset($_GET['registered'])) {
    echo '<p style="color:green;">Registration successful! You can now log in.</p>';
}
?>

<div style="text-align:center;">
    <h1>Welcome to DiabolicalBoaz</h1>
    <p>DiabolicalBoaz is a website designed to rehome reptiles.</p>

    <?php if (!isset($_SESSION['username'])): ?>
        <div id="login">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required />
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required />
                <input type="submit" value="Login" />
            </form>
        </div>

        <div id="register">
            <p>Don't have an account? Register here</p>
            <h2>Register</h2>
            <form action="register.php" method="post">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required />
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required />
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required />
                <input type="submit" value="Register" />
            </form>
            <p><a href="reset_password.php">Forgot your password?</a></p>
        </div>
    <?php else: ?>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="logout.php">Logout</a></p>
    <?php endif; ?>
</div>
</body>
</html>
