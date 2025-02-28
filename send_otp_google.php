<?php
session_start();
require 'vendor/autoload.php';
use Twilio\Rest\Client;

// Pastikan data dari Google tersedia
if (!isset($_SESSION['google_data'])) {
    header("Location: signup.html");
    exit();
}

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db = "user_db";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Inisialisasi array untuk menyimpan error
$_SESSION['errors'] = [];
$errors = [];

// Ambil nomor telepon dan metode OTP dari form
$phone = trim($_POST['phone']);
$otp_method = $_POST['otp_method'];

// Simpan nomor telepon sementara di sesi
$_SESSION['google_data']['phone'] = $phone;

// Validasi nomor telepon
$stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $errors['phone'] = "Nomor telepon sudah digunakan.";
}
$stmt->close();

// Jika ada error, simpan ke sesi dan redirect kembali
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: verify_phone.php");
    exit();
}

// Jika tidak ada error, lanjutkan proses pengiriman OTP
$otp = rand(100000, 999999);

// Simpan OTP di sesi
$_SESSION['google_data']['otp'] = $otp;

// Kredensial Twilio
$sid = "YOUR_TWILIO_SID"; // Ganti dengan Account SID
$token = "YOUR_TWILIO_AUTH_TOKEN"; // Ganti dengan Auth Token
$twilio = new Client($sid, $token);

// Kirim OTP berdasarkan metode yang dipilih
try {
    if ($otp_method === "sms") {
        $twilio->messages->create(
            $phone,
            [
                "from" => "YOUR_TWILIO_PHONE_NUMBER", // Ganti dengan nomor Twilio
                "body" => "Kode OTP Anda: $otp"
            ]
        );
    } elseif ($otp_method === "call") {
        $twilio->calls->create(
            $phone,
            "YOUR_TWILIO_PHONE_NUMBER",
            [
                "twiml" => "<Response><Say voice='alice'>Your OTP code is $otp</Say><Pause length='1'/><Say voice='alice'>I repeat, your OTP code is $otp</Say></Response>"
            ]
        );
    }
    // Tampilkan form OTP
    echo "<script>showOtpForm();</script>";
} catch (Exception $e) {
    echo "Error mengirim OTP: " . $e->getMessage();
}

exit();
?>