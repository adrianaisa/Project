<?php
session_start();
// Include database connection
include_once 'db_connect.php'; 

$featured_products = [];
$product_error = null;

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';

if (isset($conn) && $conn) {
    try {
        $sql = "SELECT product_id, product_name, price, description, image_url, category FROM Products WHERE is_available = TRUE ORDER BY category, product_name";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$featured_products) {
            $product_error = '<p>Sorry, no products are currently available on the menu!</p>';
        }
    } catch (\PDOException $e) {
        $product_error = 'Error fetching menu items: ' . $e->getMessage();
    }
} else {
    $product_error = isset($db_error) ? $db_error : "Warning: Database connection object (\$conn) was not defined. Check db_connect.php."; 
}

function group_by_category($products) {
    $grouped = [];
    foreach ($products as $product) {
        $category = isset($product['category']) ? $product['category'] : 'Uncategorized';
        $grouped[$category][] = $product;
    }
    return $grouped;
}

$grouped_products = group_by_category($featured_products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaféEase - Menu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    
    <style>
        /* --- Color Variables --- */
        :root { 
            --primary-color: #6F4E37;
            --accent-color: #EBD4B4;
            --secondary-accent: #A0522D;
            --text-dark: #2A1F1D;
            --text-light: #FFF;
            --background-light: #FAF8F5;
            --success-bg: #4CAF50;
            --success-text: #FFF;
        }

        /* --- Global Reset & Typography --- */
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: var(--background-light); 
            color: var(--text-dark); 
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }
        
        /* --- Layout Container (Updated to match index.php) --- */
        .container { 
            width: 90%; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }

        /* --- Specific Style for the White Menu Box --- */
        .menu-content-box {
            background-color: var(--text-light); 
            border-radius: 12px; 
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); 
            padding: 40px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        /* --- Header & Navigation --- */
        header { 
            background-color: var(--primary-color); 
            color: var(--text-light); 
            padding: 15px 0; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        header h1 { 
            margin: 0; 
            font-size: 2.2em;
            color: var(--accent-color); 
            font-weight: 900; 
            letter-spacing: 2px;
        }
        nav ul { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            display: flex; 
            align-items: center; 
        }
        nav ul li { margin-left: 25px; }
        nav ul li a { 
            color: var(--text-light); 
            text-decoration: none; 
            font-weight: 600; 
            padding: 8px 12px; 
            border-radius: 4px; 
            transition: background-color 0.3s ease;
        }
        nav ul li a:hover { background-color: var(--secondary-accent); }
        .user-info { 
            color: var(--accent-color); 
            font-weight: 700; 
            padding: 5px 10px; 
            border: 1px solid var(--accent-color); 
            border-radius: 4px; 
            margin-left: 25px; 
            font-size: 0.9em;
        }

        /* --- Menu Specific Styles --- */
        h2 {
            font-size: 3.5em; 
            text-align: center; 
            margin-bottom: 40px;
            padding-bottom: 10px; 
            border-bottom: 3px solid var(--accent-color);
            color: var(--secondary-accent);
        }

        .category-title {
            font-size: 2.5em; 
            margin-top: 40px; 
            margin-bottom: 20px;
            color: var(--primary-color); 
            border-left: 5px solid var(--secondary-accent);
            padding-left: 15px; 
            font-weight: 700;
        }

        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 260px)); 
            gap: 30px; 
            margin-bottom: 50px; 
            justify-content: center;
        }
        
        .product-card { 
            background-color: var(--text-dark);
            border-radius: 10px; 
            overflow: hidden;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative; 
            display: flex; 
            flex-direction: column; 
            min-height: 300px; 
        }
        
        .product-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); 
        }
        
        .card-image-wrapper {
            position: relative; 
            width: 100%; 
            height: 140px; 
            overflow: hidden;
        }

        .product-card img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.5s ease;
        }
        .product-card:hover img { transform: scale(1.05); }

        .card-content {
            padding: 15px; 
            flex-grow: 1; 
            display: flex; 
            flex-direction: column;
            text-align: left; 
            color: var(--text-light);
        }
        
        .card-content h3.card-title { 
            font-size: 1.25em; 
            margin-top: 0; 
            margin-bottom: 5px;
            line-height: 1.2; 
            color: var(--accent-color);
        }

        .card-content p.card-text {
            font-family: 'Inter', sans-serif; 
            font-size: 0.75em; 
            color: #ccc; 
            margin-bottom: 15px;
        }
        
        .price-action-wrapper {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-top: auto;
            padding-top: 10px;
        }

        .price-action-wrapper p.price { 
            font-size: 1.3em; 
            color: var(--accent-color); 
            font-weight: 900; 
            margin: 0;
        }

        .add-to-cart-btn { 
            background-color: var(--secondary-accent); 
            color: var(--text-light); 
            border: none; 
            padding: 8px 15px; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s, transform 0.2s; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 0.8em; 
        }
        .add-to-cart-btn:hover { 
            background-color: var(--primary-color); 
            transform: scale(1.05);
        }
        
        #notification-popup {
            position: fixed; top: 20px; right: 20px; z-index: 1000;
            background-color: var(--success-bg); color: var(--success-text);
            padding: 15px 25px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            opacity: 0; visibility: hidden; transform: translateY(-50px);
            transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out, visibility 0.4s;
            font-weight: bold; font-family: 'Inter', sans-serif;
            border: 2px solid var(--text-light);
        }
        #notification-popup.show {
            opacity: 1; visibility: visible; transform: translateY(0);
        }

        /* --- Footer Styling (Matched to Index) --- */
        footer { 
            text-align: center; 
            padding: 25px; 
            background-color: var(--primary-color); 
            color: var(--text-light); 
            margin-top: auto; /* Pushes footer to bottom */
        }
        footer a { color: var(--accent-color); text-decoration: none; }
        footer a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; }
            nav ul { margin-top: 10px; }
            nav ul li { margin: 0 10px; }
        }
    </style>
</head>
<body>

    <header>
        <div class="container header-content">
            <h1>CaféEase</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="aboutUs.php">About Us</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="user-info">Welcome, <?php echo $username; ?></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div id="notification-popup"></div>

    <div class="container">
        <!-- White Menu Box -->
        <div class="menu-content-box">
            <h2>Our Menu</h2>

            <?php if ($product_error): ?>
                <p style="color: red; background-color: #ffe0e0; padding: 15px; border: 1px solid red; border-radius: 6px; text-align: center;">
                    <?php echo htmlspecialchars($product_error); ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($grouped_products)): ?>
                <?php foreach ($grouped_products as $category => $products): ?>
                    <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                    
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <?php
                                $imageUrl = htmlspecialchars(!empty($product['image_url']) ? $product['image_url'] : 'https://placehold.co/300x140/6F4E37/EBD4B4?text=CaféEase');
                                $productName = htmlspecialchars($product['product_name']);
                                $rawDescription = isset($product['description']) ? $product['description'] : '';
                                $description = htmlspecialchars((strlen($rawDescription) > 55) ? substr($rawDescription, 0, 52) . '...' : $rawDescription);
                                $productId = htmlspecialchars($product['product_id']);
                            ?>

                            <div class="product-card">
                                <div class="card-image-wrapper">
                                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo $productName; ?>" onerror="this.onerror=null;this.src='https://placehold.co/300x140/6F4E37/EBD4B4?text=Image+Missing';">
                                </div>
                                
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo $productName; ?></h3>
                                    <p class="card-text"><?php echo $description; ?></p>
                                    
                                    <div class="price-action-wrapper">
                                        <p class="price">RM<?php echo number_format($product['price'], 2); ?></p>
                                        <button type="button" class="add-to-cart-btn" onclick="addItemToCart(<?php echo $productId; ?>, 1)">Add to Cart</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CaféEase. All rights reserved. | <a href="contact.php">Contact Us</a></p>
        </div>
    </footer>

    <script>
        function showNotification(message, isSuccess = true) {
            const popup = document.getElementById('notification-popup');
            const successBg = 'var(--success-bg)';
            const errorBg = '#D32F2F';

            popup.textContent = message;
            popup.style.backgroundColor = isSuccess ? successBg : errorBg;
            popup.classList.add('show');
            setTimeout(() => { popup.classList.remove('show'); }, 4000); 
        }

        function addItemToCart(itemId, quantity = 1) {
            itemId = parseInt(itemId);
            const data = { item_id: itemId, quantity: quantity, action: 'add' };
            
            showNotification('Adding item to cart...', true);

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    showNotification(result.message, true);
                } else {
                    console.error('Failed to add item:', result.message);
                    showNotification('Error: ' + result.message, false);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('Connection error. Check network.', false);
            });
        }

        const initialMessage = "<?php echo $message; ?>";
        if (initialMessage.trim()) { showNotification(initialMessage, true); }
    </script>
</body>
</html>