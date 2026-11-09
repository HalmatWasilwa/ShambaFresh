<?php
include 'db.php';

// 1. Must be logged in to checkout
if (!isset($_SESSION['user_id'])) {
    echo "<h2>⚠️ Please Login First</h2>";
    echo "You need to login as Buyer to place order.<br><br>";
    echo "<a href='login.php'>👉 Login Here</a> | <a href='index.html'>← Back to Shop</a>";
    exit;
}

echo "<h2>✅ Checkout - Place Order</h2>";
echo "Logged in as: " . $_SESSION['name'] . " (" . $_SESSION['role'] . ") | <a href='index.html'>← Back to Shop</a><hr>";

// 2. If form submitted - CREATE ORDER IN DB
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $location = $_POST['delivery_location'];
    $payment = $_POST['payment_method'];
    $total = $_POST['total_amount'];
    $buyer_id = $_SESSION['user_id'];
    $items_text = $_POST['items_text']; // e.g. Tomatoes x2, Sukuma x1

    // Insert into Orders table
    $stmt = $conn->prepare("INSERT INTO Orders (buyer_id, total_amount, status, delivery_location, payment_method) VALUES (?, ?, 'Pending', ?, ?)");
    // If your Orders table does NOT have delivery_location/payment_method columns, use this simpler line instead:
    // $stmt = $conn->prepare("INSERT INTO Orders (buyer_id, total_amount, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("idss", $buyer_id, $total, $location, $payment);

    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        echo "<div style='background:#d4edda;padding:15px;border-radius:10px'>";
        echo "<h3>🎉 Order Placed Successfully!</h3>";
        echo "<b>Order ID:</b> #$order_id<br>";
        echo "<b>Items:</b> $items_text<br>";
        echo "<b>Total:</b> KES $total<br>";
        echo "<b>Delivery to:</b> $location<br>";
        echo "<b>Payment:</b> $payment<br>";
        echo "<b>Status:</b> Pending - Admin will confirm soon";
        echo "</div>";
        echo "<br><a href='index.html'>← Continue Shopping</a> | <a href='admin.php'>View in Admin →</a>";
        echo "<script>localStorage.removeItem('shamba_cart'); localStorage.removeItem('shamba_checkout');</script>";
        exit;
    } else {
        echo "❌ Error: " . $conn->error;
        echo "<br><b>FIX:</b> Your Orders table may not have delivery_location column. Run this in phpMyAdmin SQL:";
        echo "<br><code>ALTER TABLE Orders ADD COLUMN delivery_location VARCHAR(100), ADD COLUMN payment_method VARCHAR(50);</code>";
    }
}
?>

<!-- 3. Checkout Form - Filled automatically from Cart -->
<form method="POST" action="checkout.php" onsubmit="return confirmOrder()">
  <h3>🛒 Your Order Summary</h3>
  <div id="summary" style="background:#f9f9f9;padding:15px;border-radius:10px">Loading cart...</div>

  <p><b>Delivery Location:</b><br>
  <input type="text" name="delivery_location" id="locInput" placeholder="e.g. Westlands, Nairobi" required style="width:100%;padding:12px"></p>

  <p><b>Payment Method:</b><br>
  <select name="payment_method" style="width:100%;padding:12px">
    <option value="M-Pesa">M-Pesa</option>
    <option value="Cash on Delivery">Cash on Delivery</option>
    <option value="Card">Card</option>
  </select></p>

  <!-- Hidden fields - JS fills these from localStorage cart -->
  <input type="hidden" name="total_amount" id="totalInput">
  <input type="hidden" name="items_text" id="itemsInput">
  <input type="hidden" name="place_order" value="1">

  <button type="submit" style="background:#1a7a2e;color:white;padding:14px 25px;border:none;border-radius:8px;font-weight:800;cursor:pointer;width:100%">✅ PLACE ORDER</button>
</form>

<script>
// Read cart saved from index.html
let checkoutData = JSON.parse(localStorage.getItem('shamba_checkout') || 'null');
let cart = JSON.parse(localStorage.getItem('shamba_cart') || '[]');

if (checkoutData && checkoutData.cart) cart = checkoutData.cart;

if (cart.length === 0) {
  document.getElementById('summary').innerHTML = "⚠️ Cart is empty! <br><a href='index.html'>Go to Shop and Add Produce →</a>";
} else {
  let html = "";
  let subtotal = 0;
  let items = [];
  cart.forEach(i => {
    let line = i.price * i.qty;
    subtotal += line;
    items.push(i.name + " x" + i.qty);
    html += i.name + " - KES " + i.price + " x " + i.qty + " = KES " + line + "<br>";
  });
  let delivery = 100;
  let total = subtotal + delivery;
  html += "<hr><b>Subtotal:</b> KES " + subtotal + "<br><b>Delivery:</b> KES " + delivery + "<br><b>Total:</b> KES " + total;
  document.getElementById('summary').innerHTML = html;
  document.getElementById('totalInput').value = total;
  document.getElementById('itemsInput').value = items.join(", ");
  if (checkoutData && checkoutData.loc) document.getElementById('locInput').value = checkoutData.loc;
}

function confirmOrder(){
  if(cart.length===0){ alert("Cart empty!"); return false; }
  return confirm("Place this order?");
}
</script>