<?php
session_start();

// Use the existing connection file (Using $conn instead of creating $pdo again)
require_once 'db_connect.php';

// If database connection failed in db_connect.php, stop here
if (!isset($conn) || !$conn) {
    die($db_error ?? 'Database connection failed.');
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            // Check user in database (Assuming table is 'Users')
            $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM Users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login Success
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'Admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (\PDOException $e) {
            $error_message = "An internal server error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CaféEase</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --primary-color: #6F4E37;
            --accent-color: #EBD4B4;
            --text-dark: #2A1F1D;
            --text-light: #FFF;
            --secondary-accent: #A0522D;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            height: 100vh;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            background-color: var(--accent-color);
            /* Coffee-themed background image with overlay */
            background-image: linear-gradient(rgba(42, 31, 29, 0.7), rgba(42, 31, 29, 0.7)), url('https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=2071&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        .login-container { 
            background: rgba(255, 255, 255, 0.95); /* Slight transparency */
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); 
            width: 90%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
        }

        .login-container h2 { 
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            font-size: 2.5em;
            margin-bottom: 10px; 
            margin-top: 0;
        }
        
        .login-container p.subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95em;
        }

        .input-group { 
            margin-bottom: 20px; 
            text-align: left;
        }
        
        .input-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: var(--text-dark);
            font-size: 0.9em;
        }
        
        .input-group input { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #eee; 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-family: 'Inter', sans-serif;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .input-group input:focus {
            border-color: var(--secondary-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(160, 82, 45, 0.1);
        }

        .error { 
            color: #d9534f;
            background-color: #fde8e8;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px; 
            font-size: 0.9em;
            border: 1px solid #fadbd8;
        }

        .login-button { 
            background-color: var(--primary-color); 
            color: white; 
            padding: 14px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1.1em; 
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            margin-top: 10px;
        }
        
        .login-button:hover { 
            background-color: var(--secondary-accent); 
            transform: translateY(-2px);
        }
        
        .create-account {
            margin-top: 25px;
            font-size: 0.95em;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .create-account a {
            color: var(--secondary-accent);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }
        
        .create-account a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>CaféEase</h2>
        <p class="subtitle">Welcome back! Please login to continue.</p>

        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-button">Log In</button>
        </form>
        
        <!-- New Section: Create Account -->
        <div class="create-account">
            New to CaféEase? <a href="register.php">Create an account</a>
        </div>
    </div>
</body>
</html>