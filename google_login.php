<?php
session_start();
require 'vendor/autoload.php';

// Inisialisasi Google Client
$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID'); // Ganti dengan Client ID
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET'); // Ganti dengan Client Secret
$client->setRedirectUri('http://localhost/login/google_login.php');
$client->addScope("email");
$client->addScope("profile");

if (!isset($_GET['code'])) {
    // Redirect ke Google untuk autentikasi
    $auth_url = $client->createAuthUrl();
    header("Location: $auth_url");
    exit();
} else {
    try {
        // Proses callback dari Google
        $client->authenticate($_GET['code']);
        $access_token = $client->getAccessToken();
        $client->setAccessToken($access_token);
        
        $google_service = new Google_Service_Oauth2($client);
        $user_info = $google_service->userinfo->get();

        $email = $user_info->email;
        $username = $user_info->givenName;

        // Simpan data sementara di sesi untuk verifikasi nomor telepon
        $_SESSION['google_data'] = [
            'username' => $username,
            'email' => $email
        ];

        // Arahkan ke halaman untuk memasukkan nomor telepon
        header("Location: verify_phone.php");
        exit();
    } catch (Exception $e) {
        echo "Error saat login dengan Google: " . $e->getMessage();
    }
}
?>