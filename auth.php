<?php
session_start();

function detectAnomaly($features) {
    $jsonData = json_encode($features, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $escaped = '"' . addcslashes($jsonData, '"') . '"';
    $command = "python nads_predict.py $escaped";


    file_put_contents("shap_debug.log", date('Y-m-d H:i:s') . " CMD: $command" . PHP_EOL, FILE_APPEND);

    $result = shell_exec($command);

    file_put_contents("shap_debug.log", date('Y-m-d H:i:s') . " → Output: $result" . PHP_EOL, FILE_APPEND);

    $decoded = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents("shap_debug.log", "⚠️ JSON decode error: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
        return ["error" => "Model response was invalid JSON."];
    }

    return $decoded;
}

function sendOTPEmail($toEmail, $otp) {
    $command = escapeshellcmd("python send_otp_email.py " . escapeshellarg($toEmail) . " " . escapeshellarg($otp));
    $output = shell_exec($command);
    echo "<!-- Mail Output: $output -->";
}

function fetchEmail($user, $pass, $conn) {
    $query = "SELECT EMAIL FROM MYTAB WHERE USERNAME='" . $user . "' AND PASSWORD_USER='" . $pass . "'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['EMAIL'];
    }
    return null;
}

$hostname = "localhost";
$password = "";
$root = "root";
$database = "mydb";
$conn = new mysqli($hostname, $root, $password, $database);
if ($conn->connect_error) {
    die("Database Error");
}

$user = $_POST["usernameInput"];
$pass = $_POST["passwordInput"];
$deviceId = $_POST["deviceId"] ?? "unknown";
$ip = $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION['fail_count'])) {
    $_SESSION['fail_count'] = 0;
}

$failCount = $_SESSION['fail_count'];

$features = [
    "duration" => 0.1,
    "protocol_type" => 1,
    "service" => 3,
    "flag" => 2,
    "src_bytes" => 0.01,
    "dst_bytes" => -0.01,
    "land" => 0.0,
    "wrong_fragment" => 0.0,
    "urgent" => 0.0,
    "hot" => ($failCount >= 3 ? 5.0 : 0.0),
    "num_failed_logins" => $failCount,
    "logged_in" => 0.0,
    "num_compromised" => 0.0,
    "root_shell" => 0.0,
    "su_attempted" => 0.0,
    "num_root" => 0.0,
    "num_file_creations" => 0.0,
    "num_shells" => 0.0,
    "num_access_files" => 0.0,
    "num_outbound_cmds" => 0,
    "is_host_login" => 0,
    "is_guest_login" => 0.0,
    "count" => $failCount,
    "srv_count" => 10.0,
    "serror_rate" => ($failCount >= 3 ? 1.0 : 0.0),
    "srv_serror_rate" => ($failCount >= 3 ? 1.0 : 0.0),
    "rerror_rate" => ($failCount >= 3 ? 0.9 : 0.0),
    "srv_rerror_rate" => ($failCount >= 3 ? 0.9 : 0.0),
    "same_srv_rate" => 0.5,
    "diff_srv_rate" => ($failCount >= 3 ? 1.0 : 0.1),
    "srv_diff_host_rate" => 0.0,
    "dst_host_count" => 20.0,
    "dst_host_srv_count" => 10.0,
    "dst_host_same_srv_rate" => 0.6,
    "dst_host_diff_srv_rate" => 0.2,
    "dst_host_same_src_port_rate" => 0.5,
    "dst_host_srv_diff_host_rate" => 0.0,
    "dst_host_serror_rate" => ($failCount >= 3 ? 1.0 : 0.0),
    "dst_host_srv_serror_rate" => ($failCount >= 3 ? 1.0 : 0.0),
    "dst_host_rerror_rate" => ($failCount >= 3 ? 1.0 : 0.0),
    "dst_host_srv_rerror_rate" => ($failCount >= 3 ? 1.0 : 0.0)
];

$response = detectAnomaly($features);

if ($_SESSION['fail_count'] >= 3) {
    echo "<h3>⚠️ Brute-force pattern detected by session tracking! Access denied.</h3>";
    if (is_array($response)) {
        if (isset($response["prediction"])) {
            echo "<p><strong>ML Model Verdict:</strong> " . ($response["prediction"] == 1 ? "Anomalous" : "Normal") . "</p>";
        }
        if (isset($response["explanation"]) && is_array($response["explanation"])) {
            echo "<p><strong>Top contributing features:</strong> <em>" . implode(", ", $response["explanation"]) . "</em></p>";
        }
        if (isset($response["error"])) {
            echo "<p>⚠️ ML Error: " . htmlspecialchars($response["error"]) . "</p>";
        }
    } else {
        echo "<p>⚠️ Invalid model response.</p>";
    }
    exit();
}

if ($response && isset($response["prediction"]) && $response["prediction"] == 1) {
    echo "<h3>⚠️ Anomalous login detected!</h3>";
    echo "<p><strong>Explanation:</strong> <em>" . implode(", ", $response["explanation"]) . "</em></p>";
    exit();
}

$query = "SELECT * FROM MYTAB WHERE USERNAME='" . $user . "' AND PASSWORD_USER='" . $pass . "'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $trustedDevices = ["device_123abc", "device_known_456"];
    $isTrusted = in_array($deviceId, $trustedDevices);

    $_SESSION['username'] = $user;
    $_SESSION['deviceId'] = $deviceId;
    $_SESSION['ip'] = $ip;
    $_SESSION['fail_count'] = 0;

    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;

    $email = fetchEmail($user, $pass, $conn);
    if ($email) {
        sendOTPEmail($email, $otp);
    } else {
        echo "<h3>❌ Failed to retrieve email.</h3>";
        exit();
    }

    echo "<h2>✅ Login Verified. OTP sent to your registered email.</h2>";
    echo "<a href='verify_otp.html'>Verify OTP</a>";
} else {
    $_SESSION['fail_count'] += 1;
    echo "<h3>❌ Invalid Credentials</h3>";
}
?>
