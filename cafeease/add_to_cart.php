<?php
// Start the session to access the cart
session_start();

// Set the header to return JSON (since our Javascript expects it)
header('Content-Type: application/json');

// Include your database connection
require_once 'db_connect.php';

// 1. Read the raw POST data (because we sent JSON from the Javascript)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Initialize response array
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// 2. Validate Input
if (isset($data['action']) && $data['action'] === 'add' && isset($data['item_id'])) {
    
    $productId = intval($data['item_id']);
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

    // Check if the database connection is working
    if ($conn) {
        try {
            // 3. Verify the product actually exists in the database before adding
            $stmt = $conn->prepare("SELECT product_name, price FROM Products WHERE product_id = :id AND is_available = TRUE");
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                // 4. Product exists! Add or Update the Session Cart
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                // If item already in cart, increment quantity
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    // Otherwise, add it new
                    $_SESSION['cart'][$productId] = $quantity;
                }

                $response['success'] = true;
                $response['message'] = $product['product_name'] . " added to cart!";
                
                // Debugging (Optional): Return cart count
                $response['cart_count'] = array_sum($_SESSION['cart']);

            } else {
                $response['message'] = "Error: Product ID $productId does not exist or is unavailable.";
            }

        } catch (PDOException $e) {
            $response['message'] = "Database Error: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Database connection failed.";
    }

} else {
    $response['message'] = "Invalid data received.";
}

// 5. Return the JSON response
echo json_encode($response);
?>