<?php
// ornaments.php - Ornaments Page
session_start();
include 'database.php';
include 'catalog.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ornaments</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="container">
        <div class="page-main">
            <h1>Ornaments</h1>

            <form method="post" action="checkout.php" id="orderForm">
                <input type="hidden" name="category" value="orna">

                <table class="data-table">
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                    </tr>
                    <?php foreach ($orna as $item):
                        $inputName = str_replace(' ', '_', $item['name']);
                    ?>
                    <tr>
                        <td><img src="../images/<?php echo $item['image']; ?>?v=2" alt="<?php echo $item['name']; ?>" style="width:100px;height:auto;"></td>
                        <td><?php echo $item['name']; ?></td>
                        <td>$<?php echo $item['price']; ?></td>
                        <td>
                            Quantity (0 to 9):
                            <input type="number" name="<?php echo $inputName; ?>" min="0" max="9" value="0" class="qty-input">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Submit Order</button>
                    <button type="reset" class="btn-reset">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            var inputs = document.querySelectorAll('.qty-input');
            var atLeastOne = false;
            var exceedsMax = false;
            for (var i = 0; i < inputs.length; i++) {
                var val = parseInt(inputs[i].value);
                if (val > 0) atLeastOne = true;
                if (val > 9) exceedsMax = true;
            }
            if (!atLeastOne) {
                alert('Please select at least one item (quantity > 0) before submitting your order.');
                e.preventDefault();
            } else if (exceedsMax) {
                alert('Maximum quantity per item is 9. Please reduce your order quantity.');
                e.preventDefault();
            }
        });
    </script>

</body>
</html>
