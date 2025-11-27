<?php
session_start();
$orderId = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : '#';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - CaféEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #FAF8F5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            text-align: center; 
            margin: 0;
        }
        .success-card { 
            background: white; 
            padding: 50px; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            max-width: 500px;
            width: 90%;
        }
        h1 { color: #4CAF50; font-family: 'Playfair Display', serif; font-size: 3em; margin: 0; }
        p { color: #555; font-size: 1.1em; margin: 20px 0; line-height: 1.6; }
        .order-id { font-weight: bold; color: #6F4E37; background: #eee; padding: 5px 10px; border-radius: 4px; }
        .btn { display: inline-block; background-color: #6F4E37; color: white; text-decoration: none; padding: 12px 25px; border-radius: 50px; font-weight: bold; transition: 0.3s; margin-top: 20px; }
        .btn:hover { background-color: #A0522D; transform: translateY(-3px); }
    </style>
</head>
<body>
    <div class="success-card">
        <div style="font-size: 4em; margin-bottom: 10px;">🎉</div>
        <h1>Thank You!</h1>
        <p>Your order has been placed successfully.<br>We are brewing your coffee now!</p>
        <p>Order ID: <span class="order-id">#<?php echo $orderId; ?></span></p>
        <a href="index.php" class="btn">Back to Home</a>
    </div>
</body>
</html>