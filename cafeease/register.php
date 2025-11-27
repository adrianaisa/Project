<?php
session_start();
require_once 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? ''); // NEW: Capture Email
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 5) {
        $error_message = "Password must be at least 5 characters long.";
    } else {
        // Database Operations
        if (isset($conn) && $conn) {
            try {
                // 1. Check if username OR email already exists
                $checkStmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
                $checkStmt->execute([$username, $email]);
                
                if ($checkStmt->rowCount() > 0) {
                    $error_message = "Username or Email already exists. Please try logging in.";
                } else {
                    // 2. Create new user
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'User'; 
                    
                    // Auto-generate ID (Fallback if AUTO_INCREMENT is off)
                    $new_user_id = mt_rand(100, 100000);

                    // Insert Email
                    $insertStmt = $conn->prepare("INSERT INTO Users (user_id, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                    
                    if ($insertStmt->execute([$new_user_id, $username, $email, $password_hash, $role])) {
                        $success_message = "Account created successfully! You can now login.";
                    } else {
                        $error_message = "Registration failed. Please try again.";
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = "Database connection failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - CaféEase</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    
    <style>
        /* Same beautiful styles as before */
        :root { 
            --primary-color: #6F4E37;
            --accent-color: #EBD4B4;
            --text-dark: #2A1F1D;
            --text-light: #FFF;
            --secondary-accent: #A0522D;
            --success-bg: #4CAF50;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            min-height: 100vh;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            background-image: linear-gradient(rgba(42, 31, 29, 0.7), rgba(42, 31, 29, 0.7)), url('https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=2071&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        .register-container { 
            background: rgba(255, 255, 255, 0.95);
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); 
            width: 90%;
            max-width: 450px;
            text-align: center;
        }

        .register-container h2 { 
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            font-size: 2.2em;
            margin-bottom: 10px; 
            margin-top: 0;
        }
        
        .subtitle { color: #666; margin-bottom: 25px; font-size: 0.95em; }

        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9em; }
        .input-group input { 
            width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 8px; 
            box-sizing: border-box; font-family: 'Inter', sans-serif; font-size: 1em;
            transition: border-color 0.3s;
        }
        .input-group input:focus { border-color: var(--secondary-accent); outline: none; }

        .error { color: #d9534f; background-color: #fde8e8; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fadbd8; }
        .success { color: #155724; background-color: #d4edda; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; }

        .register-button { 
            background-color: var(--secondary-accent); 
            color: white; padding: 14px; border: none; border-radius: 8px; cursor: pointer; 
            width: 100%; font-size: 1.1em; font-weight: 600; margin-top: 10px;
            transition: background-color 0.3s;
        }
        .register-button:hover { background-color: var(--primary-color); }
        
        .login-link { margin-top: 25px; font-size: 0.95em; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
        .login-link a { color: var(--primary-color); text-decoration: none; font-weight: 700; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Join CaféEase</h2>
        <p class="subtitle">Create an account to order your favorites.</p>

        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success">
                <?php echo htmlspecialchars($success_message); ?>
                <br><br>
                <a href="login.php" style="font-weight:bold; color:#155724;">Click here to Login</a>
            </div>
        <?php else: ?>
            <form method="POST" action="register.php">
                <!-- Username Field -->
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>

                <!-- Email Field -->
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="name@example.com" required>
                </div>

                <!-- Password Field -->
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Choose a password" required>
                </div>

                <!-- Confirm Password -->
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="register-button">Create Account</button>
            </form>
        <?php endif; ?>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>
</body>
</html>