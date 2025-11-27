<?php
session_start();
include_once 'db_connect.php'; 
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';

// --- OUR POPULAR ITEMS ---
$popular_items = [
    [
        'id' => 301,
        'name' => 'Chocolate Croissant',
        'price' => 8.00,
        'description' => 'Flaky pastry filled with dark chocolate.',
        'image_url' => 'images/choccroi.png',
    ],
    [
        'id' => 402,
        'name' => 'Iced Matcha Latte',
        'price' => 9.00,
        'description' => 'Rich and earthy matcha with creamy milk and ice for refreshing and smooth drink.',
        'image_url' => 'images/matcha latte.png',
    ],
    [
        'id' => 418,
        'name' => 'Egg Croissant Sandwich',
        'price' => 9.00,
        'description' => 'Fluffy scrambled egg inside buttery croissant.',
        'image_url' => 'images/egg croissant sandwich.png',
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaféEase - Brewed For You</title>
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
            --dark-card-bg: #3A2B27;        
            --hero-overlay: rgba(30, 30, 30, 0.4); 
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
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }
        
        .container { 
            width: 90%; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px; 
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

        /* --- Hero Section --- */
        .hero-section {
            height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative; 
            text-align: center;
            background-image: url('https://images.unsplash.com/photo-1541167760455-816d86016335?q=80&w=2070&auto=format&fit=crop'); 
            background-size: cover;
            background-position: center;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(0deg, var(--text-dark) 0%, rgba(111, 78, 55, 0.3) 100%);
            opacity: 0.85;
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2; 
            color: var(--text-light);
            padding: 40px;
            max-width: 800px;
        }

        .hero-content p:first-child { 
            font-size: 1.4em;
            font-style: italic;
            letter-spacing: 1px;
            margin-bottom: 10px;
            color: var(--accent-color);
        }

        .hero-content h2 {
            font-size: 6em;
            margin-bottom: 20px;
            color: var(--text-light);
            font-weight: 900;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        .hero-content .tagline {
            font-size: 1.5em;
            margin-bottom: 40px;
            font-weight: 400;
        }

        .hero-button {
            display: inline-block;
            background-color: var(--accent-color); 
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1em;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .hero-button:hover {
            background-color: var(--text-light);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        }

        /* --- Popular Items Section (Matched to aboutUs.php) --- */
        .info-section {
            padding: 80px 0;
            background-color: var(--accent-color);
            text-align: center;
        }
        .info-section h3 {
            font-size: 3em;
            margin-bottom: 10px;
        }
        
        /* Using popular-grid class style to match aboutUs.php */
        .popular-grid {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 60px;
            margin-top: 40px;
        }
        
        /* --- CARD STYLING (Matches aboutUs.php) --- */
        .menu-card {
            background-color: var(--dark-card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            flex: 1;
            min-width: 280px;
            max-width: 350px; 
            overflow: hidden; 
            transition: transform 0.3s ease;
            text-align: left;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.4);
        }

        .menu-card-image {
            width: 100%;
            height: 200px; 
            background-color: #4F3420; 
            display: block;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            opacity: 0.9;
        }
        
        .menu-card-content {
            padding: 20px;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .menu-card h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8em;
            margin: 0 0 5px 0;
            color: var(--accent-color);
        }
        .menu-card .description {
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        
        .add-to-cart-form {
            display: flex;
            justify-content: space-between; 
            margin-top: 15px;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 15px;
        }
        
        .price {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--accent-color);
        }

        .add-to-cart-button {
            background-color: var(--secondary-accent);
            color: var(--text-light);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
            text-transform: uppercase;
        }
        .add-to-cart-button:hover {
            background-color: #8B4513; 
        }

        /* Badge Styling (Kept for Homepage Flair) */
        .popular-badge-new {
            position: absolute;
            top: 10px; 
            right: 10px;
            background-color: #D3A469; 
            color: var(--text-dark);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        /* --- Notification Popup --- */
        #notification-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background-color: var(--success-bg);
            color: var(--success-text);
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-50px);
            transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out, visibility 0.4s;
            font-weight: bold;
            font-family: 'Inter', sans-serif;
            border: 2px solid var(--text-light);
        }
        #notification-popup.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        footer { 
            text-align: center; 
            padding: 25px; 
            background-color: var(--primary-color); 
            color: var(--text-light); 
        }
        footer a { color: var(--accent-color); text-decoration: none; }
        footer a:hover { text-decoration: underline; }

        .db-error {
            color: var(--text-light);
            background-color: rgba(255, 0, 0, 0.7);
            padding: 15px;
            margin-top: 25px;
            border: 2px solid var(--text-light);
            border-radius: 8px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; }
            nav ul { margin-top: 10px; }
            nav ul li { margin: 0 10px; }
            .hero-content h2 { font-size: 4em; }
            .popular-grid { flex-direction: column; align-items: center; }
            .menu-card { max-width: 100%; width: 100%; margin-bottom: 20px; }
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
    
    <!-- Notification Popup -->
    <div id="notification-popup"></div>

    <section id="home" class="hero-section">
        <div class="hero-content">
            <p>Welcome to Your Daily Retreat</p>
            <h2>Brewed For Your Moment</h2>
            <p class="tagline">Sip, Relax, and Savor the Moment. Hand-crafted excellence in every cup.</p>
            <a href="menu.php" class="hero-button">Order Your Coffee Now</a>
            
            <?php 
            if (isset($conn) && $conn instanceof mysqli && mysqli_connect_error()) {
                echo '<div class="db-error">Database Connection Error: ' . mysqli_connect_error() . '</div>';
            }
            ?>
        </div>
    </section>

    <section id="popular-items" class="info-section">
        <div class="container">
            <h3>Our Popular Items</h3>
            <p>Customer favorites and trending items you simply must try!</p>
            
            <div class="popular-grid">
                <?php foreach ($popular_items as $item): ?>
                
                <!-- CARD STRUCTURE -->
                <div class="menu-card">
                    
                    <!-- Popular Badge -->
                    <div class="popular-badge-new">POPULAR</div>

                    <!-- Image Wrapper -->
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                         onerror="this.onerror=null; this.src='https://placehold.co/300x200/4F3420/EBD4B4?text=<?php echo urlencode($item['name']); ?>';"
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         class="menu-card-image">
                    
                    <!-- Card Content -->
                    <div class="menu-card-content">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p class="description"><?php echo htmlspecialchars($item['description']); ?></p>
                        
                        <div class="add-to-cart-form">
                            <!-- Price -->
                            <span class="price">RM <?php echo number_format($item['price'], 2); ?></span>
                            
                            <!-- Button (AJAX) -->
                            <button type="button" 
                                    class="add-to-cart-button" 
                                    onclick="addItemToCart(<?php echo $item['id']; ?>, 1)">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CaféEase. All rights reserved. | <a href="contact.php">Contact Us</a></p>
        </div>
    </footer>

    <script>
        // Custom function to show the notification popup
        function showNotification(message, isSuccess = true) {
            const popup = document.getElementById('notification-popup');
            const successBg = 'var(--success-bg)';
            const errorBg = '#D32F2F';

            popup.textContent = message;
            popup.style.backgroundColor = isSuccess ? successBg : errorBg;
            popup.classList.add('show');
            
            setTimeout(() => {
                popup.classList.remove('show');
            }, 4000); 
        }

        // --- AJAX Function to Add Item ---
        function addItemToCart(itemId, quantity = 1) {
            itemId = parseInt(itemId);

            const data = {
                item_id: itemId,
                quantity: quantity,
                action: 'add' 
            };
            
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
                showNotification('Could not connect to server.', false);
            });
        }
    </script>
</body>
</html>