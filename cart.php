<?php include 'db.php'; ?>
<h2>🛒 My Cart - ShambaFresh</h2>
<a href="index.html">← Back to Shop</a> | <a href="checkout.php">Checkout →</a><hr>
<p>Your cart is saved in your browser. Click Checkout to place order.</p>

<?php
if(isset($_SESSION['user_id'])) echo "Logged in as: ".$_SESSION['name']." (".$_SESSION['role'].")";
else echo "You are not logged in. <a href='login.php'>Login to checkout</a> - but you can still add to cart.";
?>