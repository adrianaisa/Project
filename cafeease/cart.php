<?php
session_start();
include_once 'db_connect.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch user data for the header
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
$isLoggedIn = isset($_SESSION['user_id']);

$cartItems = [];
$subtotal = 0;
$serviceFeeRate = 0.05; // 5% Service Fee
$message = "";

// Handle Quantity Updates or Removals
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['product_id'])) {
        $pId = intval($_POST['product_id']);
        
        if ($_POST['action'] === 'remove') {
            unset($_SESSION['cart'][$pId]);
            $message = "Item removed from cart.";
        } elseif ($_POST['action'] === 'update') {
            $qty = intval($_POST['quantity']);
            if ($qty > 0) {
                $_SESSION['cart'][$pId] = $qty;
            } else {
                unset($_SESSION['cart'][$pId]);
            }
            $message = "Cart updated.";
        }
    }
}

// Fetch Cart Data from Database
if (!empty($_SESSION['cart']) && isset($conn)) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $ids = preg_replace('/[^0-9,]/', '', $ids); 
    
    if (!empty($ids)) {
        try {
            $sql = "SELECT product_id, product_name, price, image_url, category FROM Products WHERE product_id IN ($ids)";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                $pId = $product['product_id'];
                $quantity = $_SESSION['cart'][$pId];
                $lineTotal = $product['price'] * $quantity;
                
                $product['qty'] = $quantity;
                $product['line_total'] = $lineTotal;
                
                // Initials generation
                $words = explode(" ", $product['product_name']);
                $initials = "";
                foreach ($words as $w) { $initials .= $w[0]; }
                $product['initials'] = strtoupper(substr($initials, 0, 2));

                $cartItems[] = $product;
                $subtotal += $lineTotal;
            }
        } catch (PDOException $e) {
            $message = "Error loading cart: " . $e->getMessage();
        }
    }
}

$serviceFee = $subtotal * $serviceFeeRate;
$total = $subtotal + $serviceFee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaféEase - Shopping Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    
    <style>
        /* --- Styles (Same as before) --- */
        :root { 
            --primary-color: #6F4E37;
            --accent-color: #EBD4B4;
            --secondary-accent: #A0522D;
            --text-dark: #2A1F1D;
            --text-light: #FFF;
            --background-light: #FAF8F5;    
            --item-bg: #FFFFFF;
        }
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: var(--background-light); color: var(--text-dark); }
        
        header { background-color: var(--primary-color); color: var(--text-light); padding: 15px 0; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); }
        .header-content { display: flex; justify-content: space-between; align-items: center; width: 90%; max-width: 1200px; margin: 0 auto; }
        header h1 { font-family: 'Playfair Display', serif; margin: 0; font-size: 2.2em; color: var(--accent-color); font-weight: 900; letter-spacing: 2px; }
        
        nav ul { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        nav ul li { margin-left: 25px; }
        nav ul li a { color: var(--text-light); text-decoration: none; font-weight: 600; transition: 0.3s; }
        nav ul li a:hover { color: var(--accent-color); }
        
        .user-info { color: var(--accent-color); font-weight: 700; padding: 5px 10px; border: 1px solid var(--accent-color); border-radius: 4px; margin-left: 25px; font-size: 0.9em; }

        .page-header { text-align: center; padding: 40px 20px; }
        .page-header h2 { font-family: 'Playfair Display', serif; font-size: 3.5em; color: var(--primary-color); margin: 0 0 10px 0; }
        .page-header p { color: #666; font-size: 1.1em; }

        .cart-container { width: 90%; max-width: 1200px; margin: 0 auto 60px auto; display: flex; gap: 40px; flex-wrap: wrap; }
        
        .cart-items-section { flex: 2; background-color: var(--item-bg); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 20px; min-width: 300px; }
        .cart-item { display: flex; align-items: center; justify-content: space-between; padding: 25px 0; border-bottom: 1px solid #eee; }
        .cart-item:last-child { border-bottom: none; }
        
        .item-left { display: flex; align-items: center; gap: 20px; }
        .item-image-box { width: 80px; height: 80px; background-color: var(--primary-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--accent-color); font-family: 'Playfair Display', serif; font-size: 1.8em; font-weight: bold; overflow: hidden; }
        .item-image-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .item-details h4 { margin: 0 0 5px 0; font-size: 1.1em; color: var(--text-dark); }
        .item-unit-price { color: var(--secondary-accent); font-size: 0.9em; font-weight: 600; }
        
        .qty-controls { display: flex; align-items: center; gap: 10px; background-color: #f5f5f5; padding: 5px 10px; border-radius: 20px; }
        .qty-btn { background: none; border: none; font-size: 1.2em; color: #888; cursor: pointer; padding: 0 5px; }
        .qty-btn:hover { color: var(--primary-color); }
        .qty-display { font-weight: 600; min-width: 20px; text-align: center; }
        
        .item-right { display: flex; align-items: center; gap: 30px; }
        .item-total-price { font-weight: 700; font-size: 1.1em; color: var(--text-dark); min-width: 80px; text-align: right; }
        .remove-btn { color: #cc4444; background: none; border: none; font-size: 1.2em; cursor: pointer; padding: 5px; }
        .remove-btn:hover { color: #ff0000; }

        .summary-section { flex: 1; min-width: 300px; }
        .summary-card { background-color: #2A1F1D; color: #EBD4B4; padding: 30px; border-radius: 12px; position: sticky; top: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .summary-card h3 { font-family: 'Playfair Display', serif; font-size: 1.8em; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #443330; padding-bottom: 15px; }
        
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 1em; }
        .summary-row.total { margin-top: 25px; padding-top: 15px; border-top: 1px dashed #443330; font-size: 1.4em; font-weight: 700; color: #FFF; }

        .checkout-btn { display: block; width: 100%; background-color: var(--accent-color); color: var(--primary-color); text-align: center; padding: 15px 0; border: none; border-radius: 6px; font-size: 1.1em; font-weight: 700; margin-top: 25px; cursor: pointer; transition: transform 0.2s, background-color 0.2s; text-decoration: none; }
        .checkout-btn:hover { background-color: #dcbfa0; transform: translateY(-2px); }
        
        /* Different style for the "Login to Checkout" button */
        .login-checkout-btn { background-color: var(--secondary-accent); color: white; }
        .login-checkout-btn:hover { background-color: #8C3E2F; }

        .tax-note { display: block; text-align: center; font-size: 0.8em; color: #888; margin-top: 15px; }
        .empty-cart-msg { text-align: center; padding: 40px; font-size: 1.2em; color: #888; }
        .continue-shopping { display: inline-block; margin-top: 20px; color: var(--secondary-accent); text-decoration: none; font-weight: 600; }

        @media (max-width: 768px) {
            .cart-container { flex-direction: column; }
            .item-right { flex-direction: column; align-items: flex-end; gap: 10px; }
            .cart-item { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <h1>CaféEase</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="cart.php" style="color: var(--accent-color);">Cart</a></li>
                    <li><a href="aboutUs.php">About Us</a></li>
                    
                    <!-- Dynamic Login Logic in here -->
                    <?php if ($isLoggedIn): ?>
                        <li class="user-info">Welcome, <?php echo $username; ?></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <h2>Your Shopping Cart</h2>
        <p>Review your items before checking out.</p>
    </div>

    <div class="cart-container">
        
        <!-- Left Side: Items -->
        <div class="cart-items-section">
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="item-left">
                            <div class="item-image-box">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span style="display:none"><?php echo $item['initials']; ?></span>
                                <?php else: ?>
                                    <?php echo $item['initials']; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <div class="item-unit-price">
                                    RM <?php echo number_format($item['price'], 2); ?> each
                                </div>
                            </div>
                        </div>

                        <div class="item-right">
                            <form action="cart.php" method="POST" class="qty-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <div class="qty-controls">
                                    <button type="submit" name="quantity" value="<?php echo $item['qty'] - 1; ?>" class="qty-btn">-</button>
                                    <span class="qty-display"><?php echo $item['qty']; ?></span>
                                    <button type="submit" name="quantity" value="<?php echo $item['qty'] + 1; ?>" class="qty-btn">+</button>
                                </div>
                            </form>

                            <div class="item-total-price">
                                RM <?php echo number_format($item['line_total'], 2); ?>
                            </div>

                            <form action="cart.php" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" class="remove-btn" title="Remove Item">&times;</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-cart-msg">
                    <p>Your cart is currently empty.</p>
                    <a href="menu.php" class="continue-shopping">Browse Menu &rarr;</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Side: Order Summary -->
        <?php if (!empty($cartItems)): ?>
        <div class="summary-section">
            <div class="summary-card">
                <h3>Order Summary</h3>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>RM <?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Service Fee (5%)</span>
                    <span>RM <?php echo number_format($serviceFee, 2); ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total (Incl. Tax)</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>

                <!-- CHECKOUT BUTTON -->
                <?php if ($isLoggedIn): ?>
                    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="login.php" class="checkout-btn login-checkout-btn">Login to Checkout</a>
                <?php endif; ?>
                
                <span class="tax-note">Tax included where applicable.</span>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <footer style="text-align: center; padding: 20px; color: #888; font-size: 0.9em;">
        &copy; <?php echo date('Y'); ?> CaféEase. All rights reserved.
    </footer>

</body>
</html>