<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $userId = $_SESSION['user_id'];
    $totalAmount = floatval($_POST['total_amount']); 
    $tableNum = $_POST['table_number'] ?? 'Takeaway'; // Capture the Table Number
    
    if (empty($_SESSION['cart'])) {
        header("Location: menu.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        // Added table_number
        $stmt = $conn->prepare("INSERT INTO Orders (user_id, table_number, total_amount, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$userId, $tableNum, $totalAmount]);
        $orderId = $conn->lastInsertId();

        $itemStmt = $conn->prepare("INSERT INTO OrderItems (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        $ids = implode(',', array_keys($_SESSION['cart']));
        $prodStmt = $conn->query("SELECT product_id, price FROM Products WHERE product_id IN ($ids)");
        $products = $prodStmt->fetchAll(PDO::FETCH_KEY_PAIR); 

        foreach ($_SESSION['cart'] as $pId => $qty) {
            if (isset($products[$pId])) {
                $price = $products[$pId];
                $itemStmt->execute([$orderId, $pId, $qty, $price]);
            }
        }

        $conn->commit();
        unset($_SESSION['cart']);

        header("Location: order_success.php?order_id=" . $orderId);
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Order failed: " . $e->getMessage());
    }

} else {
    header("Location: index.php");
    exit();
}
?>