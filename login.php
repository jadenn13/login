<?php
// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db = "user_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data dari form
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Query dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Bandingkan password langsung (tanpa hash)
    if ($password === $row['password']) {
        // Login berhasil, tampilkan popup dan redirect
        echo "<script>";
        echo "alert('Login berhasil! Selamat datang, " . htmlspecialchars($row['username']) . "');";
        echo "window.location.href = 'welcome.html';";
        echo "</script>";
    } else {
        echo "Password salah!";
    }
} else {
    echo "Username tidak ditemukan!";
}

$stmt->close();
mysqli_close($conn);
?>