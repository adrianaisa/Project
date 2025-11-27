<?php
    session_start();

    // 1. Check if user is logged in and is an Admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        header("Location: login.php");
        exit();
    }

    require_once 'db_connect.php';

    if (!isset($conn) || !$conn) {
        die("<h1>Database Connection Error</h1><p>Could not connect to database.</p>");
    }

    $metrics = ['total_orders' => 0, 'total_customers' => 0, 'unique_products_sold' => 0];
    $menuItems = [];
    $recentOrders = [];
    $success_msg = "";
    $error_message = "";

    // --- ACTION HANDLERS ---

    // A. Update Order Status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['status'];
        
        try {
            $stmt = $conn->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $success_msg = "Order #$orderId updated to $newStatus!";
        } catch (PDOException $e) {
            $error_message = "Error updating order: " . $e->getMessage();
        }
    }

    // B. Handle Add/Edit Product (Same as before)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
        // ... (Existing Add Logic)
        $name = $_POST['product_name']; $cat = $_POST['category']; $price = $_POST['price']; $desc = $_POST['description']; $img = $_POST['image_url'];
        $conn->prepare("INSERT INTO Products (product_name, category, price, description, image_url, is_available) VALUES (?, ?, ?, ?, ?, 1)")->execute([$name, $cat, $price, $desc, $img]);
        $success_msg = "Product added!";
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
        // ... (Existing Edit Logic)
        $id = $_POST['product_id']; $name = $_POST['product_name']; $cat = $_POST['category']; $price = $_POST['price']; $desc = $_POST['description']; $img = $_POST['image_url'];
        $conn->prepare("UPDATE Products SET product_name=?, category=?, price=?, description=?, image_url=? WHERE product_id=?")->execute([$name, $cat, $price, $desc, $img, $id]);
        $success_msg = "Product updated!";
    }

    // C. Toggle Product Status
    if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $conn->prepare("UPDATE Products SET is_available = NOT is_available WHERE product_id = ?")->execute([$id]);
        header("Location: dashboard.php");
        exit();
    }

    // --- FETCH DATA ---
    try {
        // Metrics
        $metrics['total_orders'] = $conn->query("SELECT COUNT(*) FROM OrderItems")->fetchColumn();
        $metrics['total_customers'] = $conn->query("SELECT COUNT(*) FROM Users WHERE role = 'User'")->fetchColumn();
        $metrics['unique_products_sold'] = $conn->query("SELECT COUNT(DISTINCT product_id) FROM Products")->fetchColumn();
        
        // Menu List
        $menuItems = $conn->query("SELECT * FROM Products ORDER BY category, product_name")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Recent Orders with Items ---
        // This query joins Orders, Users, OrderItems, and Products to get a full summary
        $orderSql = "
            SELECT o.order_id, o.table_number, o.total_amount, o.status, o.created_at, u.username,
                   GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.product_name) SEPARATOR '<br>') as items_summary
            FROM Orders o
            LEFT JOIN Users u ON o.user_id = u.user_id
            LEFT JOIN OrderItems oi ON o.order_id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.product_id
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
            LIMIT 50";
            
        $recentOrders = $conn->query($orderSql)->fetchAll(PDO::FETCH_ASSOC);

    } catch (\PDOException $e) {
        $error_message = "Error loading data: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CaféEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #6F4E37;
            --accent-color: #EBD4B4;
            --secondary-accent: #A0522D;
            --text-dark: #2A1F1D;
            --text-light: #FFF;
            --bg-light: #F4F4F9;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        body { font-family: 'Inter', sans-serif; margin: 0; background-color: var(--bg-light); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--primary-color); color: var(--text-light); padding: 20px; position: fixed; height: 100%; left: 0; top: 0; z-index: 100; }
        .sidebar-header h2 { font-family: 'Playfair Display', serif; color: var(--accent-color); font-size: 1.8em; margin-bottom: 40px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .nav-links { list-style: none; padding: 0; }
        .nav-links li { margin-bottom: 15px; }
        .nav-links a { color: rgba(255,255,255,0.8); text-decoration: none; display: flex; align-items: center; padding: 12px 15px; border-radius: 8px; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { background-color: rgba(255,255,255,0.1); color: var(--text-light); }
        .nav-links i { margin-right: 15px; width: 20px; text-align: center; }

        /* Content */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header-bar h1 { font-family: 'Playfair Display', serif; font-size: 2.5em; margin: 0; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 40px; height: 40px; background-color: var(--secondary-accent); color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }

        /* Metrics */
        .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px; }
        .metric-card { background: white; padding: 30px; border-radius: 16px; box-shadow: var(--card-shadow); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid transparent; }
        .metric-card:nth-child(1) { border-left-color: #4CAF50; }
        .metric-card:nth-child(2) { border-left-color: #2196F3; }
        .metric-card:nth-child(3) { border-left-color: #FF9800; }
        .metric-info h3 { margin: 0 0 10px 0; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; color: #888; }
        .metric-info .value { font-size: 2.5em; font-weight: 700; margin: 0; }
        .metric-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.5em; background: #eee; }

        /* Tables */
        .table-container { background: white; border-radius: 16px; box-shadow: var(--card-shadow); overflow: hidden; margin-bottom: 40px; }
        .section-header { padding: 20px 30px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .section-header h2 { margin: 0; font-size: 1.5em; }
        .add-btn { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; color: #666; font-weight: 600; text-align: left; padding: 15px 30px; font-size: 0.9em; text-transform: uppercase; }
        td { padding: 15px 30px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover { background-color: #fafafa; }
        
        .product-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; display: inline-block; }
        .status-active { background: #E8F5E9; color: #2E7D32; }
        .status-inactive { background: #FFEBEE; color: #C62828; }
        
        /* Order Status Colors */
        .status-Pending { background: #FFF3E0; color: #E65100; }
        .status-Completed { background: #E8F5E9; color: #2E7D32; }
        .status-Cancelled { background: #FFEBEE; color: #C62828; }

        /* Status Select Dropdown */
        .status-select { padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 0.9em; }
        .update-btn { padding: 5px 10px; background: var(--secondary-accent); color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 30px; border-radius: 12px; width: 500px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .submit-btn { width: 100%; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }

        .msg { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success-msg { background: #E8F5E9; color: #2E7D32; }
        .error-msg { background: #FFEBEE; color: #C62828; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .nav-links span { display: none; }
            .nav-links i { margin: 0; font-size: 1.2em; }
            .main-content { margin-left: 70px; padding: 20px; }
            .table-container { overflow-x: auto; }
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header"><h2>CaféEase</h2></div>
        <ul class="nav-links">
            <li><a href="#" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="index.php"><i class="fas fa-globe"></i> <span>View Website</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="header-bar">
            <div><h1>Dashboard</h1><p style="color: #666;">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p></div>
            <div class="user-profile"><span>Admin</span><div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div></div>
        </div>

        <?php if ($success_msg): ?><div class="msg success-msg"><?php echo htmlspecialchars($success_msg); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="msg error-msg"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <!-- Metrics -->
        <div class="metric-grid">
            <div class="metric-card"><div class="metric-info"><h3>Items Sold</h3><p class="value"><?php echo number_format($metrics['total_orders']); ?></p></div><div class="metric-icon" style="background:#E8F5E9; color:#4CAF50"><i class="fas fa-shopping-cart"></i></div></div>
            <div class="metric-card"><div class="metric-info"><h3>Customers</h3><p class="value"><?php echo number_format($metrics['total_customers']); ?></p></div><div class="metric-icon" style="background:#E3F2FD; color:#2196F3"><i class="fas fa-users"></i></div></div>
            <div class="metric-card"><div class="metric-info"><h3>Menu Items</h3><p class="value"><?php echo number_format($metrics['unique_products_sold']); ?></p></div><div class="metric-icon" style="background:#FFF3E0; color:#FF9800"><i class="fas fa-coffee"></i></div></div>
        </div>

        <!-- SECTION: INCOMING ORDERS -->
        <div class="table-container">
            <div class="section-header">
                <h2>Incoming Orders</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Table / Customer</th>
                        <th>Order Details</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 30px;">No orders yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td>
                                    <div style="font-weight:bold; color:var(--primary-color);"><?php echo htmlspecialchars($order['table_number']); ?></div>
                                    <div style="font-size:0.85em; color:#888;"><?php echo htmlspecialchars($order['username']); ?></div>
                                    <div style="font-size:0.85em; color:#aaa;"><?php echo date('h:i A', strtotime($order['created_at'])); ?></div>
                                </td>
                                <td style="font-size: 0.9em; line-height: 1.4;">
                                    <?php echo $order['items_summary']; ?>
                                </td>
                                <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="dashboard.php" style="display:flex; align-items:center;">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="status" class="status-select">
                                            <option value="Pending" <?php if($order['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Completed" <?php if($order['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                            <option value="Cancelled" <?php if($order['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="update-btn"><i class="fas fa-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- SECTION: MENU MANAGEMENT -->
        <div class="table-container">
            <div class="section-header">
                <h2>Menu Management</h2>
                <button class="add-btn" onclick="openModal('addModal')"><i class="fas fa-plus"></i> Add Product</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th width="80">Image</th>
                        <th>Product Details</th>
                        <th>Price</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menuItems)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px;">No items found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="product-img" onerror="this.src='https://placehold.co/50x50/eee/999?text=IMG'">
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div style="color: #888; font-size: 0.9em;"><?php echo htmlspecialchars($item['category']); ?></div>
                                </td>
                                <td>RM <?php echo number_format($item['price'], 2); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($item['is_available']): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="?action=toggle_status&id=<?php echo $item['product_id']; ?>" style="color:#F57C00; margin-right:10px;" title="Toggle Availability"><i class="fas fa-power-off"></i></a>
                                    <a href="#" onclick='openEditModal(<?php echo json_encode($item); ?>)' style="color:#1976D2;" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2 style="margin-top: 0; color: var(--primary-color);">Add New Product</h2>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="add_product" value="1">
                <div class="form-group"><label>Product Name</label><input type="text" name="product_name" required></div>
                <div class="form-group"><label>Category</label><select name="category"><option value="Coffee">Coffee</option><option value="Pastry">Pastry</option><option value="Beverage">Beverage</option><option value="Dessert">Dessert</option></select></div>
                <div class="form-group"><label>Price (RM)</label><input type="number" step="0.01" name="price" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
                <div class="form-group"><label>Image URL</label><input type="text" name="image_url"></div>
                <button type="submit" class="submit-btn">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2 style="margin-top: 0; color: var(--primary-color);">Edit Product</h2>
            <form method="POST" action="dashboard.php">
                <input type="hidden" name="edit_product" value="1">
                <input type="hidden" id="edit_id" name="product_id">
                <div class="form-group"><label>Product Name</label><input type="text" id="edit_name" name="product_name" required></div>
                <div class="form-group"><label>Category</label><select id="edit_category" name="category"><option value="Coffee">Coffee</option><option value="Pastry">Pastry</option><option value="Beverage">Beverage</option><option value="Dessert">Dessert</option></select></div>
                <div class="form-group"><label>Price (RM)</label><input type="number" step="0.01" id="edit_price" name="price" required></div>
                <div class="form-group"><label>Description</label><textarea id="edit_description" name="description" rows="3"></textarea></div>
                <div class="form-group"><label>Image URL</label><input type="text" id="edit_image" name="image_url"></div>
                <button type="submit" class="submit-btn">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = "block"; }
        function closeModal(id) { document.getElementById(id).style.display = "none"; }
        function openEditModal(item) {
            document.getElementById('edit_id').value = item.product_id;
            document.getElementById('edit_name').value = item.product_name;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_image').value = item.image_url;
            document.getElementById('edit_category').value = item.category;
            openModal('editModal');
        }
        window.onclick = function(event) { if (event.target.classList.contains('modal')) event.target.style.display = "none"; }
    </script>
</body>
</html>