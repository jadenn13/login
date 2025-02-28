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

// Inisialisasi array untuk menyimpan error
$errors = [];

// Ambil data dari form (jika ada)
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$otp_method = isset($_POST['otp_method']) ? $_POST['otp_method'] : 'sms';

// Simpan data sementara di sesi untuk ditampilkan kembali di form
$_SESSION['signup_data'] = [
    'username' => $username,
    'email' => $email,
    'phone' => $phone,
    'password' => $password,
    'otp_method' => $otp_method
];

// Jika form dikirim, lakukan validasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['username'] = "Username sudah digunakan.";
    }
    $stmt->close();

    // Validasi email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['email'] = "Email sudah digunakan.";
    }
    $stmt->close();

    // Validasi nomor telepon
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors['phone'] = "Nomor telepon sudah digunakan.";
    }
    $stmt->close();

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    } else {
        // Jika tidak ada error, lanjutkan ke proses pengiriman OTP
        header("Location: send_otp.php");
        exit();
    }
}

// Simpan error ke sesi untuk ditampilkan
$_SESSION['errors'] = $errors;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
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
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
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
        .social-btn {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .google-btn {
            background-color: #4285f4;
            color: white;
        }
        .google-btn:hover {
            background-color: #3267d6;
        }
        .login-link {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            font-size: 0.9rem;
            margin-top: -5px;
            margin-bottom: 5px;
            display: block;
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
            .social-btn {
                padding: 8px;
                font-size: 0.9rem;
            }
            .error {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form id="signup-form" action="signup.php" method="POST">
            <label>Username:</label><br>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>
            <?php if (isset($errors['username'])): ?>
                <span class="error"><?php echo $errors['username']; ?></span>
            <?php endif; ?>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?php echo $errors['email']; ?></span>
            <?php endif; ?>

            <label>Phone Number:</label><br>
            <input type="tel" name="phone" placeholder="+628123456789" value="<?php echo htmlspecialchars($phone); ?>" required><br>
            <?php if (isset($errors['phone'])): ?>
                <span class="error"><?php echo $errors['phone']; ?></span>
            <?php endif; ?>

            <label>Password:</label><br>
            <input type="password" name="password" required><br>

            <label>Send OTP via:</label><br>
            <input type="radio" name="otp_method" value="sms" <?php if ($otp_method === 'sms') echo 'checked'; ?>> SMS<br>
            <input type="radio" name="otp_method" value="call" <?php if ($otp_method === 'call') echo 'checked'; ?>> Call<br><br>
            <input type="submit" value="Send OTP">
        </form>

        <!-- Tombol Login Sosial -->
        <p style="text-align: center; margin: 10px 0;">atau</p>
        <a href="google_login.php"><button class="social-btn google-btn">Sign Up with Google</button></a>

        <!-- Tautan ke Login -->
        <div class="login-link">
            Sudah punya akun? <a href="login.html">Login di sini</a>
        </div>
    </div>
</body>
</html>