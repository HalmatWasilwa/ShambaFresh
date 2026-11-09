<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) die("Login as Buyer first");
// $cart comes from front-end: e.g. [{produce_id:1, qty:2, price:120}]
$cart = json_decode($_POST['cart_json'], true);
$total = 0; foreach ($cart as $item) $total += $item['qty'] * $item['price'];
$total += 200; // delivery

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO Orders (buyer_id, total_amount, delivery_address) VALUES (?,?,?)");
    $stmt->bind_param("ids", $_SESSION['user_id'], $total, $_POST['address']);
    $stmt->execute();
    $order_id = $conn->insert_id;

    foreach ($cart as $item) {
        $sub = $item['qty'] * $item['price'];
        $stmt2 = $conn->prepare("INSERT INTO Order_Items (order_id, produce_id, quantity, unit_price, subtotal) VALUES (?,?,?,?,?)");
        $stmt2->bind_param("iiidd", $order_id, $item['produce_id'], $item['qty'], $item['price'], $sub);
        $stmt2->execute();
        // Reduce stock
        $conn->query("UPDATE Produce SET quantity = quantity - {$item['qty']} WHERE produce_id = {$item['produce_id']}");
    }
    $conn->commit();
    echo "✅ Order #$order_id Placed! Status: Placed";
} catch (Exception $e) {
    $conn->rollback();
    echo "Order failed: " . $e->getMessage();
}
?>