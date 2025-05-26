<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'csrf.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DiabolicalBoaz</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* --- Hamburger Menu Styles --- */

        #menu-toggle {
            display: none;
        }

        #menu-toggle:focus {
            outline: none;
        }

        #menu-toggle-label {
            display: block;
            width: 30px;
            height: 25px;
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1001;
            outline: none;
        }

        #menu-toggle-label span,
        #menu-toggle-label span::before,
        #menu-toggle-label span::after {
            display: block;
            background: #0f0;
            height: 4px;
            width: 100%;
            border-radius: 2px;
            position: absolute;
            transition: all 0.3s ease;
        }

        #menu-toggle-label span {
            top: 50%;
            margin-top: -2px;
            position: relative;
        }

        #menu-toggle-label span::before {
            content: '';
            top: -10px;
        }

        #menu-toggle-label span::after {
            content: '';
            top: 10px;
        }

        nav {
            display: none;
            position: absolute;
            top: 60px;
            right: 20px;
            background: #111;
            border-radius: 6px;
            box-shadow: 0 0 10px #0f0;
            padding: 10px 0;
            width: 200px;
            z-index: 1000;
        }

        #menu-toggle:checked + #menu-toggle-label + nav {
            display: block;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        nav ul li {
            display: block;
            margin: 0;
        }

        nav ul li a {
            display: block;
            color: #0f0;
            padding: 12px 20px;
            text-decoration: none;
            font-weight: bold;
            border-bottom: 1px solid #0f0;
        }

        nav ul li:last-child a {
            border-bottom: none;
        }

        nav ul li a:hover {
            background: #0f0;
            color: #000;
        }

        /* --- Header Layout Fixes --- */
        .header-container {
            position: relative;
            padding: 10px 20px;
            text-align: center;
        }

        #logo {
            display: block;
            margin: 0 auto;
            max-height: 120px; /* Increased logo size */
        }

        @media (max-width: 600px) {
            #logo {
                max-height: 90px;
            }
        }
    </style>
</head>
<body>
    <div class="header-container">
        <img src="images/logo.jpeg" alt="DiabolicalBoaz Logo" id="logo" />

        <input type="checkbox" id="menu-toggle" />
        <label for="menu-toggle" id="menu-toggle-label"><span></span></label>

        <nav>
            <ul id="nav">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="reptiles.php">Reptiles</a></li>
                <li><a href="enclosures.php">Enclosures</a></li>
                <li><a href="supplies.php">Products</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>
