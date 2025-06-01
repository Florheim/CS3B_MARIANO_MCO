<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'config.php';

$error = '';
$message = '';
$username_email_prefill = '';

// Display success messages from previous redirects (e.g., from OTP verification)
if (isset($_SESSION['success_message_task_app'])) {
    $message = $_SESSION['success_message_task_app'];
    unset($_SESSION['success_message_task_app']);
}
// Display error messages from previous redirects
if (isset($_SESSION['error_message_task_app'])) {
    $error = $_SESSION['error_message_task_app'];
    unset($_SESSION['error_message_task_app']);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';

    $username_email_prefill = htmlspecialchars($username_email);

    if (empty($username_email) || empty($password)) {
        $error = "Please enter your username/email and password.";
    } else {
        // Try to find user by username OR email
        $stmt = $conn->prepare("SELECT id, username, email, password, is_verified FROM users WHERE username = ? OR email = ?");
        if ($stmt === false) {
            $error = "Database prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username_email, $username_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_verified']) {
                    $_SESSION['user_id_task_app'] = $user['id'];
                    $_SESSION['username_task_app'] = $user['username'];
                    session_regenerate_id(true);
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Your account is not verified. Please check your email for the OTP or try verifying again.";
                }
            } else {
                $error = "Invalid username/email or password.";
            }
            $stmt->close();
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
    <title>Login - Task Management App</title>
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

        .login-container {
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

        .success-message {
            color: green;
            margin-bottom: 15px;
        }

        .register-link {
            margin-top: 15px;
        }
    </style>
</head>

<body style="background-image: url('assets/Task_Management.jpg'); background-size: cover; background-repeat: no-repeat;">
    <div class="login-container">
        <h2>Login to Your Account</h2>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="success-message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="login_account.php" method="post">
            <div class="form-group">
                <label for="username_email">Username or Email:</label>
                <input type="text" name="username_email" id="username_email" value="<?php echo $username_email_prefill; ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        <p class="register-link">Don't have an account? <a href="register_account.php">Create</a></p>
    </div>
</body>

</html>