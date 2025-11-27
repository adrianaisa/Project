<?php
session_start();
require_once 'db_connect.php';

// 1. Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: menu.php");
    exit();
}

// 3. Calculate Totals
$subtotal = 0;
$cartItems = [];

if (isset($conn)) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $ids = preg_replace('/[^0-9,]/', '', $ids); 

    if (!empty($ids)) {
        $sql = "SELECT product_id, product_name, price FROM Products WHERE product_id IN ($ids)";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $qty = $_SESSION['cart'][$product['product_id']];
            $lineTotal = $product['price'] * $qty;
            $subtotal += $lineTotal;
            $product['qty'] = $qty;
            $cartItems[] = $product;
        }
    }
}

$serviceFee = $subtotal * 0.05;
$grandTotal = $subtotal + $serviceFee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - CaféEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6F4E37;
            --accent-color: #EBD4B4;
            --secondary-accent: #A0522D;
            --text-dark: #2A1F1D;
            --text-light: #FFF;
            --bg-light: #FAF8F5;
        }
        
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--text-dark); margin: 0; }
        
        header { background-color: var(--primary-color); color: var(--text-light); padding: 15px 0; text-align: center; }
        header h1 { font-family: 'Playfair Display', serif; margin: 0; color: var(--accent-color); }

        .container { width: 90%; max-width: 1100px; margin: 40px auto; display: flex; gap: 40px; flex-wrap: wrap; }
        
        /* Left Column: Form */
        .checkout-form { flex: 2; min-width: 300px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        h2 { font-family: 'Playfair Display', serif; color: var(--primary-color); margin-top: 0; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #555; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; box-sizing: border-box; }
        
        /* Right Column: Summary */
        .order-summary { flex: 1; min-width: 300px; background-color: #2A1F1D; color: #EBD4B4; padding: 30px; border-radius: 12px; height: fit-content; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.95em; }
        .total-row { border-top: 1px dashed #555; margin-top: 20px; padding-top: 20px; font-size: 1.4em; font-weight: 700; color: white; }
        
        .item-list { max-height: 200px; overflow-y: auto; margin-bottom: 20px; padding-right: 10px; border-bottom: 1px solid #444; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9em; color: #ccc; }

        .place-order-btn { display: block; width: 100%; background-color: var(--accent-color); color: var(--primary-color); text-align: center; padding: 15px; border: none; border-radius: 6px; font-size: 1.1em; font-weight: 700; margin-top: 25px; cursor: pointer; transition: 0.3s; }
        .place-order-btn:hover { background-color: #dcbfa0; transform: translateY(-2px); }

        @media (max-width: 768px) { .container { flex-direction: column-reverse; } }
    </style>
</head>
<body>

    <header>
        <h1>Checkout</h1>
    </header>

    <div class="container">
        
        <!-- Left: Customer Details Form -->
        <div class="checkout-form">
            <h2>Order Details</h2>
            <form action="place_order.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required placeholder="John Doe">
                </div>
                
                <!-- Delivery Address -> Table Number -->
                <div class="form-group">
                    <label>Select Your Table</label>
                    <select name="table_number" required style="font-size: 1.1em; color: var(--primary-color); font-weight: bold;">
                        <option value="" disabled selected>-- Choose Table --</option>
                        <option value="Takeaway">Takeaway (To Go)</option>
                        <?php for($i=1; $i<=20; $i++): ?>
                            <option value="Table <?php echo $i; ?>">Table <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <h2>Payment Method</h2>
                <div class="form-group">
                    <label>Choose Payment</label>
                    <select name="payment_method">
                        <option value="fpx">FPX Online Banking</option>
                        <option value="ewallet">E-Wallet (GrabPay, TNG)</option>
                        <option value="credit_card">Credit / Debit Card</option>
                        <option value="cash">Pay at Counter</option>
                    </select>
                </div>
                
                <input type="hidden" name="total_amount" value="<?php echo $grandTotal; ?>">
                
                <button type="submit" class="place-order-btn">Place Order - RM <?php echo number_format($grandTotal, 2); ?></button>
            </form>
        </div>

        <!-- Right: Order Summary -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            
            <div class="item-list">
                <?php foreach ($cartItems as $item): ?>
                <div class="item-row">
                    <span><?php echo $item['qty']; ?>x <?php echo htmlspecialchars($item['product_name']); ?></span>
                    <span>RM <?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>RM <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Service Fee (5%)</span>
                <span>RM <?php echo number_format($serviceFee, 2); ?></span>
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span>RM <?php echo number_format($grandTotal, 2); ?></span>
            </div>
        </div>

    </div>

</body>
</html>