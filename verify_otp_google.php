<?php
session_start();

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

// Ambil OTP dari form
$input_otp = trim($_POST['otp']);
$signup_data = $_SESSION['google_data'];

// Verifikasi OTP
if ($input_otp === (string)$signup_data['otp']) {
    // Validasi ulang nomor telepon
    $errors = [];

    // Validasi nomor telepon
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $signup_data['phone']);
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

    // Jika tidak ada error, simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $signup_data['username'], $signup_data['email'], $signup_data['phone'], $password);
    $password = ''; // Kosongkan password karena login dengan Google
    
    if ($stmt->execute()) {
        // Berhasil signup, hapus data sesi
        unset($_SESSION['google_data']);
        unset($_SESSION['errors']);
        // Redirect ke halaman login dengan popup
        echo "<script>";
        echo "alert('Verifikasi nomor telepon berhasil! Selamat datang, " . htmlspecialchars($signup_data['username']) . "');";
        echo "window.location.href = 'welcome.html';";
        echo "</script>";
        exit();
    } else {
        echo "Error saat menyimpan data: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "OTP salah! Silakan coba lagi. <a href='verify_phone.php'>Kembali</a>";
}

mysqli_close($conn);
?>