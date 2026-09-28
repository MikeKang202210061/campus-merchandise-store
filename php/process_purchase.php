<?php
// process_purchase.php
// mid, category, itemN, price, quantity

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'connectDB.php';
include 'priceNameStructure.php';
?>
<!DOCTYPE html>
<html>
<head>
 <title>Purchase Confirmation - BNBU Memorabilia</title>
 <link rel="stylesheet" href="../style.css">
 <style>
   .receipt { margin:0 auto; border-collapse:collapse; width:80%; }
   .receipt th { background:#add8e6; }
   .total-row td { font-weight:bold; }
   .order-id { font-size:1.1em; color:#006600; }
   .content { text-align:center; }
   .warning { color:#cc0000; font-weight:bold; }
 </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="content">

<?php

// ========== 1. Check login ==========
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
 echo "<div class='warning'>";
 echo "<h2>Please Log In First</h2>";
 echo "<p>You must log in before submitting an order.</p>";
 echo "<p><a href='login.php'>Click here to log in</a></p>";
 echo "</div>";
 echo "</div></body></html>";
 exit;
}
$username = $_SESSION['username'];

// ========== 2. Auto-create tables if they don't exist ==========

// 2a. orders table
$createOrders = "CREATE TABLE IF NOT EXISTS orders (
    order_id    INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(20)  NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status      VARCHAR(20)  NOT NULL DEFAULT 'completed',
    order_time  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_order_time (order_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($createOrders)) {
    die("Error creating orders table: " . $conn->error);
}

// 2b. order_items table
$createItems = "CREATE TABLE IF NOT EXISTS order_items (
    item_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT          NOT NULL,
    itemN      VARCHAR(50)  NOT NULL,
    price      DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantity   INT          NOT NULL DEFAULT 0,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($createItems)) {
    die("Error creating order_items table: " . $conn->error);
}

// 2c. purchase table (original – create if missing)
$createPurchase = "CREATE TABLE IF NOT EXISTS purchase (
    itemN     VARCHAR(50) PRIMARY KEY,
    quantity  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($createPurchase)) {
    die("Error creating purchase table: " . $conn->error);
}

// ========== 3. Validate category ==========
if (!isset($_POST['category'])) {
 echo "<p class='warning'>Error: No category submitted.</p>";
 echo "</div></body></html>";
 exit;
}
$category = $_POST['category'];

switch ($category) {
 case 'clothes': $itemArray = $clothes; break;
 case 'neces':   $itemArray = $neces;   break;
 case 'orna':    $itemArray = $orna;    break;
 default:
 echo "<p class='warning'>Error: Unknown category '$category'.</p>";
 echo "</div></body></html>";
 exit;
}

// ========== 4. Build purchase list & calculate total ==========
$purchasedItems = [];
$totalAmount = 0;

foreach ($itemArray as $item) {
 $itemName  = $item['name'];
 $unitPrice = $item['price'];

 $inputKey = str_replace(' ', '_', $itemName);

 if (!isset($_POST[$inputKey])) continue;
 $quantity = (int)$_POST[$inputKey];
 if ($quantity <= 0) continue;

 // ---- Update purchase table (original behavior) ----
 $updateSql = "UPDATE purchase SET quantity = ? WHERE itemN = ?";
 $updateStmt = $conn->prepare($updateSql);
 if (!$updateStmt) {
     die("Prepare failed (purchase update): " . $conn->error);
 }
 $updateStmt->bind_param("is", $quantity, $itemName);
 if (!$updateStmt->execute()) {
     die("Execute failed (purchase update): " . $updateStmt->error);
 }
 $updateStmt->close();

 // If no row was updated, INSERT a new one
 if ($conn->affected_rows === 0) {
     $insertSql = "INSERT INTO purchase (itemN, quantity) VALUES (?, ?)";
     $insertStmt = $conn->prepare($insertSql);
     if (!$insertStmt) {
         die("Prepare failed (purchase insert): " . $conn->error);
     }
     $insertStmt->bind_param("si", $itemName, $quantity);
     if (!$insertStmt->execute()) {
         die("Execute failed (purchase insert): " . $insertStmt->error);
     }
     $insertStmt->close();
 }

 // ---- Build receipt data ----
 $subtotal = $unitPrice * $quantity;
 $totalAmount += $subtotal;
 $purchasedItems[] = [
     'name'     => $itemName,
     'price'    => $unitPrice,
     'quantity' => $quantity,
     'subtotal' => $subtotal
 ];
}

// ========== 5. No items? early exit ==========
if (count($purchasedItems) === 0) {
 echo "<div class='warning'>";
 echo "<h2>No Items Selected</h2>";
 echo "<p>You did not select any item to buy. Please go back and choose quantities.</p>";
 echo "<p><a href='{$category}.php'>Back to " . ucfirst($category) . "</a></p>";
 echo "</div>";
 $conn->close();
 echo "</div></body></html>";
 exit;
}

// ========== 6. INSERT into orders table ==========
$orderSql = "INSERT INTO orders (username, total_amount, status, order_time)
             VALUES (?, ?, 'completed', NOW())";
$orderStmt = $conn->prepare($orderSql);
if (!$orderStmt) {
    die("Prepare failed (orders insert): " . $conn->error);
}
$orderStmt->bind_param("sd", $username, $totalAmount);
if (!$orderStmt->execute()) {
    die("Execute failed (orders insert): " . $orderStmt->error);
}
$orderId = $orderStmt->insert_id;
$orderStmt->close();

// ========== 7. INSERT into order_items table ==========
$itemSql = "INSERT INTO order_items (order_id, itemN, price, quantity) VALUES (?, ?, ?, ?)";
$itemStmt = $conn->prepare($itemSql);
if (!$itemStmt) {
    die("Prepare failed (order_items insert): " . $conn->error);
}

foreach ($purchasedItems as $p) {
    $itemStmt->bind_param("isdi", $orderId, $p['name'], $p['price'], $p['quantity']);
    if (!$itemStmt->execute()) {
        die("Execute failed (order_items insert): " . $itemStmt->error);
    }
}
$itemStmt->close();

// ========== 8. Display receipt ==========
echo "<div class='confirmation'>";
echo "<h2>Purchase Successful!</h2>";
echo "<p class='order-id'>Order #{$orderId}</p>";
echo "<p>Thank you, <strong>" . htmlspecialchars($username) . "</strong>. Your order has been recorded.</p>";

echo "<h3>Receipt</h3>";
echo "<table class='receipt' border='1' cellpadding='8'>";
echo "<tr><th>Item</th><th>Unit Price</th><th>Quantity</th><th>Subtotal</th></tr>";
foreach ($purchasedItems as $p) {
 echo "<tr>";
 echo "<td>" . htmlspecialchars($p['name']) . "</td>";
 echo "<td>$" . $p['price'] . "</td>";
 echo "<td>" . $p['quantity'] . "</td>";
 echo "<td>$" . $p['subtotal'] . "</td>";
 echo "</tr>";
}
echo "<tr class='total-row'>";
echo "<td colspan='3'><strong>Total</strong></td>";
echo "<td><strong>$" . $totalAmount . "</strong></td>";
echo "</tr>";
echo "</table>";

echo "<p style='margin-top:15px;'>";
echo "<a href='{$category}.php'>Back to " . ucfirst($category) . "</a> | ";
echo "<a href='index.php'>Home</a>";
echo "</p>";
echo "</div>";

$conn->close();
?>

</div>
</body>
</html>
