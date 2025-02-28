<?php
session_start();

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db = "user_db";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Pastikan data sesi tersedia
if (!isset($_SESSION['signup_data'])) {
    die("Data sesi tidak ditemukan. Silakan coba lagi dari awal.");
}

// Ambil OTP dari form
$input_otp = trim($_POST['otp']);
$signup_data = $_SESSION['signup_data'];

// Verifikasi OTP
if ($input_otp === (string)$signup_data['otp']) {
    // Validasi ulang sebelum menyimpan
    $errors = [];

    // Validasi username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $signup_data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['username'] = "Username sudah digunakan.";
    }
    $stmt->close();

    // Validasi email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $signup_data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['email'] = "Email sudah digunakan.";
    }
    $stmt->close();

    // Validasi nomor telepon
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $signup_data['phone']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['phone'] = "Nomor telepon sudah digunakan.";
    }
    $stmt->close();

    // Jika ada error, simpan ke sesi dan redirect kembali ke signup.php
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: signup.php");
        exit();
    }

    // Jika tidak ada error, simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $signup_data['username'], $signup_data['email'], $signup_data['phone'], $signup_data['password']);
    
    if ($stmt->execute()) {
        // Berhasil signup, hapus data sesi
        unset($_SESSION['signup_data']);
        unset($_SESSION['errors']);
        // Redirect ke halaman login dengan popup
        echo "<script>";
        echo "alert('Sign up berhasil! Anda akan diarahkan ke halaman login.');";
        echo "window.location.href = 'login.html';";
        echo "</script>";
    } else {
        echo "Error saat menyimpan data: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "OTP salah! Silakan coba lagi. <a href='signup.html'>Kembali</a>";
}

mysqli_close($conn);
?>