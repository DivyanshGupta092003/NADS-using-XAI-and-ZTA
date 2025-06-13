<?php
session_start();

// Retrieve submitted OTP and session data
$inputOtp = $_POST['otp'];
$actualOtp = $_SESSION['otp'] ?? null;
$user = $_SESSION['username'] ?? null;
$_SESSION['otp_verified'] = true;
$rem = $_SESSION['Remember'] = isset($_POST['remember']) ? true : false;
$_SESSION['Remember'] = $rem;

// If OTP is correct
if ($inputOtp == $actualOtp && $user) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Access Granted</title>
        <link rel='stylesheet' href='login_page.css'>
    </head>
    <body>
        <div class='loginContainer'>
            <div class='loginHeader'>
                <h1 class='loginTitle'>✅ Access Granted</h1>
                <p class='loginDescription'>Welcome, <strong>$user</strong></p>
            </div>
        </div>
        <script>
            setTimeout(() => {
                window.location.href = 'homepage.php';
            }, 2000); // 2000 ms = 2 seconds
        </script>
    </body>
    </html>";
} else {
    // OTP is incorrect → show error and Try Again link
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Incorrect OTP</title>
        <link rel='stylesheet' href='login_page.css'>
    </head>
    <body>
        <div class='loginContainer'>
            <div class='loginHeader'>
                <h1 class='loginTitle'>❌ Incorrect OTP</h1>
                <p class='loginDescription'>The OTP you entered is incorrect.</p>
                <form action='login_page.html' method='get'>
                    <button class='loginButton' style='margin-top: 1rem;'>Try Again</button>
                </form>
            </div>
        </div>
        <script>
            setTimeout(() => {
                window.location.href = 'login_page.php';
            }, 2000); // 2000 ms = 2 seconds
        </script>
    </body>
    </html>";
}
?>
