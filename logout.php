<?php
// This is the logout.php page
session_start();
session_destroy();
header("Location: index.php");
?>