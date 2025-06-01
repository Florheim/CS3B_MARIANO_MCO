<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Task Management App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e0f2f7, #c1e4f3);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }
        .welcome-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 90%;
            max-width: 500px;
            animation: fadeIn 1s ease-out;
        }
        h1 {
            font-size: 2.5em;
            color: black;
            margin-bottom: 20px;
            font-weight: 700;
        }
        p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #555;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        .btn-custom {
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1.1em;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .btn-primary-custom {
            background-color: #00C923;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }
        .btn-primary-custom:hover {
            background-color: #274B07;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4);
        }
        .btn-secondary-custom {
            background-color: #6c757d;
            color: white;
            box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary-custom:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.4);
        }
        .btn-custom i {
            margin-right: 8px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body style="background-image: url('assets/Task_Management.jpg'); background-size: cover; background-repeat: no-repeat;">
    <div class="welcome-container">
        <h1>Welcome to Task Management System</h1>
        <p>
            Organize your life and boost your productivity.
            Log in to manage your tasks or create a new account to get started.
        </p>

        <div class="button-group">
            <a href="login_account.php" class="btn-custom btn-primary-custom"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="register_account.php" class="btn-custom btn-primary-custom"><i class="fas fa-user-plus"></i> Create Account</a>
            <a href="about.html" class="btn-custom btn-secondary-custom"><i class="fas fa-info-circle"></i> About This App</a>
        </div>
    </div>
</body>
</html>