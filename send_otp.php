<?php
session_start();
require 'vendor/autoload.php';
use Twilio\Rest\Client;

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db = "user_db";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Pastikan data dari sesi tersedia
if (!isset($_SESSION['signup_data'])) {
    die("Data sesi tidak ditemukan. Silakan coba lagi dari awal. <a href='signup.html'>Kembali</a>");
}

$signup_data = $_SESSION['signup_data'];
$username = $signup_data['username'];
$email = $signup_data['email'];
$phone = $signup_data['phone'];
$password = $signup_data['password'];
$otp_method = $signup_data['otp_method'];

// Generate OTP
$otp = rand(100000, 999999);

// Simpan OTP di sesi
$_SESSION['signup_data']['otp'] = $otp;

// Kredensial Twilio
$sid = "AC1a7a2cb204d9992d90cb182079199853"; // Ganti dengan Account SID asli
$token = "f483687723101151b9cd5a54dd5b7b20"; // Ganti dengan Auth Token asli
$twilio = new Client($sid, $token);

// Kirim OTP berdasarkan metode yang dipilih
try {
    if ($otp_method === "sms") {
        $twilio->messages->create(
            $phone,
            [
                "from" => "+12513172170", // Ganti dengan nomor Twilio asli
                "body" => "Kode OTP Anda: $otp"
            ]
        );
    } elseif ($otp_method === "call") {
        $twilio->calls->create(
            $phone,
            "+12513172170",
            [
                "twiml" => "<Response><Say voice='alice'>Your OTP code is $otp</Say><Pause length='1'/><Say voice='alice'>I repeat, your OTP code is $otp</Say></Response>"
            ]
        );
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Verify OTP</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .container {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                width: 90%;
                max-width: 350px;
            }
            h2 {
                text-align: center;
                color: #333;
            }
            label {
                font-weight: bold;
                color: #555;
            }
            input[type="text"] {
                width: 100%;
                padding: 8px;
                margin: 8px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            input[type="submit"] {
                width: 100%;
                padding: 10px;
                background-color: #28a745;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            input[type="submit"]:hover {
                background-color: #218838;
            }
            @media (max-width: 600px) {
                .container {
                    width: 90%;
                    padding: 15px;
                }
                h2 {
                    font-size: 1.5rem;
                }
                label, input {
                    font-size: 0.9rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Verify OTP</h2>
            <p>OTP telah dikirim ke <?php echo htmlspecialchars($phone); ?>.</p>
            <form action="verify_otp.php" method="POST">
                <label>Enter OTP:</label><br>
                <input type="text" name="otp" required><br><br>
                <input type="submit" value="Verify & Sign Up">
            </form>
        </div>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    echo "Error mengirim OTP: " . $e->getMessage();
}

exit();
?>