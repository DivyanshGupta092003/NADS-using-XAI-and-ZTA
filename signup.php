<?php
// Start session (optional if needed later)
session_start();

// DB connection
$hostname = "localhost";
$password = "";
$root = "root";
$database = "mydb";

$conn = new mysqli($hostname, $root, $password, $database);
if ($conn->connect_error) {
    die("Database Error");
}

// Get POST data
$user = $_POST["usernameInput"];
$pass = $_POST["passwordInput"];
$deviceId = $_POST["deviceId"] ?? "unknown_device";
$ip = $_SERVER['REMOTE_ADDR'];

// Prepared statement to insert with ZTA fields
$query = $conn->prepare("INSERT INTO MYTAB (username, password_user, device_id, ip_address) VALUES (?, ?, ?, ?)");
$query->bind_param("ssss", $user, $pass, $deviceId, $ip);

// Execute query and handle result
if ($query->execute()) {
    header("Location: ./login_page.html");
} else {
    echo "Something went wrong during signup.";
}
?>
