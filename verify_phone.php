<?php
session_start();

// Pastikan data dari Google tersedia
if (!isset($_SESSION['google_data'])) {
    header("Location: signup.html");
    exit();
}

// Tampilkan pesan error dari sesi jika ada
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
unset($_SESSION['errors']); // Hapus error setelah ditampilkan
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify Phone Number</title>
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
        input[type="tel"], input[type="text"] {
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
            .error {
                font-size: 0.8rem;
            }
        }
    </style>
    <script>
        function showOtpForm() {
            document.getElementById("phone-form").style.display = "none";
            document.getElementById("otp-form").style.display = "block";
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Verify Phone Number</h2>
        <form id="phone-form" action="send_otp_google.php" method="POST">
            <label>Phone Number:</label><br>
            <input type="tel" name="phone" placeholder="+628123456789" value="<?php echo isset($_SESSION['google_data']['phone']) ? htmlspecialchars($_SESSION['google_data']['phone']) : ''; ?>" required><br>
            <?php if (isset($errors['phone'])): ?>
                <span class="error"><?php echo $errors['phone']; ?></span>
            <?php endif; ?>
            <label>Send OTP via:</label><br>
            <input type="radio" name="otp_method" value="sms" checked> SMS<br>
            <input type="radio" name="otp_method" value="call"> Call<br><br>
            <input type="submit" value="Send OTP">
        </form>

        <!-- Form OTP -->
        <div id="otp-form" style="display: none;">
            <h2>Enter OTP</h2>
            <form action="verify_otp_google.php" method="POST">
                <label>Enter OTP:</label><br>
                <input type="text" name="otp" required><br><br>
                <input type="submit" value="Verify Phone">
            </form>
        </div>
    </div>
</body>
</html>