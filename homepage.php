<?php
session_start();

// Redirect if OTP was not verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    session_unset();
    session_destroy();
    header("Location: login_page.html");
    exit();
}

// Optional DB connection
$username = "root";
$database = "mydb";
$password = "";
$hostname = "localhost";
$conn = new mysqli($hostname, $username, $password, $database);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>This is the secured main page after OTP verification.</p>

    <!-- Logout button posts back to this page -->
    <form method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
</body>
</html>

<?php
// Handle logout after form is submitted
if ($_SESSION['Remember']) {
    if (isset($_POST['logout'])) {
        session_unset();
        header("Location: homepage.php");
        exit();
    }
}
else{
    session_unset();
}
?>
