<?php
session_start();
include_once 'db_connect.php'; 

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaféEase - Our Story</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

    <style>
        /* --- Shared Styles (Matches index.php) --- */
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

        h1, h2, h3, h4 {
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

        /* --- About Page Specific Styles --- */
        
        /* About Hero Section */
        .about-hero {
            background-color: var(--accent-color);
            padding: 80px 0;
            text-align: center;
            margin-bottom: 40px;
            background-image: linear-gradient(rgba(235, 212, 180, 0.9), rgba(235, 212, 180, 0.9)), url('https://images.unsplash.com/photo-1447933601400-b8a9dc8da731?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
        
        .about-hero h2 {
            font-size: 3.5em;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .about-hero p {
            font-size: 1.3em;
            max-width: 700px;
            margin: 0 auto;
            color: #5a4a42;
            font-weight: 500;
        }

        /* Story Section */
        .story-section {
            background-color: #fff;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 60px;
            border-left: 5px solid var(--primary-color);
        }
        
        .story-content h3 {
            font-size: 2em;
            margin-top: 40px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .story-content h3:first-child { margin-top: 0; }
        
        .story-content p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.8;
        }

        footer { 
            text-align: center; 
            padding: 25px; 
            background-color: var(--primary-color); 
            color: var(--text-light); 
            margin-top: auto; 
        }
        footer a { color: var(--accent-color); text-decoration: none; }
        footer a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; }
            nav ul { margin-top: 10px; }
            nav ul li { margin: 0 10px; }
            .about-hero h2 { font-size: 2.5em; }
            .story-section { padding: 25px; }
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
    
    <div class="about-hero">
        <div class="container">
            <h2>More Than Just Coffee</h2>
            <p>Crafting moments of ease and flavor for our community since 2023.</p>
        </div>
    </div>

    <div class="container">
        
        <!-- Story Section -->
        <div class="story-section">
            <div class="story-content">
                <h3>Our Mission</h3>
                <p>
                    At <strong>CaféEase</strong>, we believe that great coffee should be simple, accessible, and ethically sourced. Our mission is to provide high-quality beverages and delicious pastries delivered right to your table, ensuring every sip and bite brings a moment of ease to your busy day.
                </p>
                
                <h3>Our History</h3>
                <p>
                    Founded in 2023, CaféEase started as a small virtual idea aimed at simplifying the busy urban lifestyle. We noticed the need for a seamless ordering experience without compromising the quality of artisanal coffee. Today, we continue to blend tradition with technology to serve our community better.
                </p>
                
                <h3>Our Promise</h3>
                <p>
                    We are committed to sustainability and community. From our locally roasted beans to our eco-friendly packaging, every decision we make is designed to leave a positive impact on our planet and our neighborhood.
                </p>
            </div>
        </div>

    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CaféEase. All rights reserved. | <a href="contact.php">Contact Us</a></p>
        </div>
    </footer>

</body>
</html>