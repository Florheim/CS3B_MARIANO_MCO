<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'config.php';

$error = '';
$message = '';
$email_for_verification = $_SESSION['email_for_otp_verification_task_app'] ?? '';

if (empty($email_for_verification)) {
    // If email is not in session, redirect back to registration or login
    header("Location: register_account.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_otp = trim($_POST['otp_code'] ?? '');

    if (empty($user_otp)) {
        $error = "Please enter the OTP code.";
    } elseif (strlen($user_otp) != 6 || !ctype_digit($user_otp)) {
        $error = "OTP must be a 6-digit number.";
    } else {
        // Fetch user and OTP from DB
        $stmt = $conn->prepare("SELECT otp_code FROM users WHERE email = ? AND is_verified = 0");
        if ($stmt === false) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email_for_verification);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && $user_otp == $user['otp_code']) {
                // OTP matched, update user as verified
                $stmt_update = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL WHERE email = ?");
                if ($stmt_update === false) {
                    $error = "Database error during verification: " . $conn->error;
                } else {
                    $stmt_update->bind_param("s", $email_for_verification);
                    if ($stmt_update->execute()) {
                        $_SESSION['success_message_task_app'] = "Email successfully verified! You can now log in.";
                        unset($_SESSION['email_for_otp_verification_task_app']); // Clear email from session
                        header("Location: login_account.php"); // Redirect to login page
                        exit;
                    } else {
                        $error = "Error updating verification status: " . $stmt_update->error;
                    }
                    $stmt_update->close();
                }
            } else {
                $error = "Invalid OTP or account already verified.";
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Task Management App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .otp-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            text-align: center;
            font-size: 1.5em;
            letter-spacing: 5px;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        .info-message {
            color: #007bff;
            margin-bottom: 15px;
        }
    </style>
</head>

<body style="background-image: url('assets/Task_Management.jpg'); background-size: cover; background-repeat: no-repeat;">
    <div class="otp-container">
        <h2>OTP Verification</h2>
        <p class="info-message">A 6-digit OTP has been sent to <strong><?= htmlspecialchars($email_for_verification) ?></strong>. Please enter it below.</p>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="verify_otp.php" method="post">
            <div class="form-group">
                <label for="otp_code">Enter OTP:</label>
                <input type="text" name="otp_code" id="otp_code" maxlength="6" pattern="\d{6}" title="Please enter a 6-digit OTP" required autofocus>
            </div>
            <div class="form-group">
                <button type="submit">Verify Account</button>
            </div>
        </form>
        <p style="font-size: 0.9em; color: #666;">Didn't receive the OTP? Check your spam folder.</p>
    </div>
</body>

</html>