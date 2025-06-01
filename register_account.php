<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'config.php';
require_once 'send_otp.php';

$error = '';
$message = '';
$username_prefill = '';
$email_prefill = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $username_prefill = htmlspecialchars($username);
    $email_prefill = htmlspecialchars($email);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $otp_code = rand(100000, 999999); // 6-digit OTP

        // Check for existing username or email
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if ($stmt_check === false) {
            $error = "Database error during check: " . $conn->error;
        } else {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error = "Username or Email already registered.";
            } else {
                $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, otp_code) VALUES (?, ?, ?, ?)");
                if ($stmt_insert === false) {
                    $error = "Database error during registration: " . $conn->error;
                } else {
                    $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $otp_code);

                    if ($stmt_insert->execute()) {
                        if (sendOTP($email, $otp_code)) {
                            $_SESSION['email_for_otp_verification_task_app'] = $email;
                            $_SESSION['success_message_task_app'] = "Registration successful! Please check your email for the 6-digit OTP to verify your account.";
                            header("Location: verify_otp.php");
                            exit;
                        } else {
                            $error = "Registration successful, but failed to send OTP email. Please try again or contact support.";
                        }
                    } else {
                        $error = "Error registering user: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}

if ($conn && !$conn->connect_error) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Task Management App</title>
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

        .register-container {
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

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .form-group button:hover {
            background-color: #218838;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }

        .login-link {
            margin-top: 15px;
        }
    </style>
</head>
<body style="background-image: url('assets/Task_Management.jpg'); background-size: cover; background-repeat: no-repeat;">
    <div class="register-container">
        <h2>Create Your Account</h2>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="success-message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="register_account.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo $username_prefill; ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo $email_prefill; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <div class="form-group">
                <button type="submit">Register</button>
            </div>
        </form>
        <p class="login-link">Already have an account? <a href="login_account.php">Login here</a></p>
    </div>
</body>
</html>