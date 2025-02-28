<?php
session_start();
require 'vendor/autoload.php';

$fb = new Facebook\Facebook([
    'app_id' => 'YOUR_FB_APP_ID', // Ganti dengan App ID
    'app_secret' => 'YOUR_FB_APP_SECRET', // Ganti dengan App Secret
    'default_graph_version' => 'v18.0',
]);

$helper = $fb->getRedirectLoginHelper();

if (!isset($_GET['code'])) {
    $permissions = ['email'];
    $loginUrl = $helper->getLoginUrl('http://localhost/login/facebook_login.php', $permissions);
    header("Location: $loginUrl");
    exit();
} else {
    $accessToken = $helper->getAccessToken();
    $response = $fb->get('/me?fields=id,name,email', $accessToken);
    $user = $response->getGraphUser();

    $email = $user['email'];
    $username = $user['name'];

    $conn = mysqli_connect("localhost", "root", "", "user_db");
    if (!$conn) die("Koneksi gagal: " . mysqli_connect_error());

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $phone = '';
        $password = '';
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $phone, $password);
        $stmt->execute();
    }

    header("Location: login.html");
    exit();
}
?>