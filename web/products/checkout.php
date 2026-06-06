<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


checkLogin(); //must login


$user_id = $_SESSION['user_id'];
//MERGE SESSION CART → DB CART
if (!empty($_SESSION['cart'])) {


    foreach ($_SESSION['cart'] as $product_id => $item) {


        $qty = $item['qty'];


        // CHECK IF ALREADY EXISTS
        $stmt = $conn->prepare("
            SELECT * FROM cart
            WHERE user_id=? AND product_id=?
        ");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $row = $stmt->fetch();


        if ($row) {
            // UPDATE QTY
            $conn->prepare("
                UPDATE cart
                SET qty = qty + ?
                WHERE id = ?
            ")->execute([$qty, $row['id']]);
        } else {
            // INSERT
            $conn->prepare("
                INSERT INTO cart(user_id, product_id, qty)
                VALUES (?,?,?)
            ")->execute([$_SESSION['user_id'], $product_id, $qty]);
        }
    }


    //CLEAR SESSION CART AFTER MERGE
    unset($_SESSION['cart']);
}
//LOAD CART (DB ONLY at checkout)
$stmt = $conn->prepare("
    SELECT c.product_id, c.qty, p.price, p.name
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();


if (!$items) {
    echo "<div class='alert alert-warning'>Cart is empty</div>";
    exit;
}


//CALCULATE TOTAL
$total = 0;
foreach ($items as $i) {
    $total += $i['price'] * $i['qty'];
}


//DISCOUNT
$discount = 0;
if (isset($_SESSION['discount'])) {
    $discount = ($total * $_SESSION['discount']) / 100;
}


$grand_total = $total - $discount;


//PLACE ORDER
if (isset($_POST['place_order'])) {


    $billing = $_POST['billing_address'];
    $delivery = $_POST['delivery_address'];
    $payment = $_POST['payment_method'];


    if (!$billing || !$delivery || !$payment) {
        $_SESSION['error'] = "All fields required!";
        header("Location: checkout.php");
        exit;
    }


    try {


        $conn->beginTransaction();


        //CREATE ORDER
        $stmt = $conn->prepare("
            INSERT INTO sales_orders
            (user_id,total,discount,grand_total,payment_method,billing_address,delivery_address)
            VALUES (?,?,?,?,?,?,?)
        ");


        $stmt->execute([
            $user_id,
            $total,
            $discount,
            $grand_total,
            $payment,
            $billing,
            $delivery
        ]);


        $order_id = $conn->lastInsertId();


        //INSERT ORDER ITEMS + FIFO STOCK DEDUCTION
        foreach ($items as $i) {


            $product_id = $i['product_id'];
            $order_qty = $i['qty'];


            //INSERT ORDER ITEM
            $conn->prepare("
                INSERT INTO sales_items(order_id,product_id,qty,price)
                VALUES (?,?,?,?)
            ")->execute([
                $order_id,
                $product_id,
                $order_qty,
                $i['price']
            ]);


            //CHECK TOTAL STOCK AGAIN (CRITICAL)
            $stmt = $conn->prepare("
                SELECT SUM(remaining_qty) as stock
                FROM stock WHERE product_id=?
            ");
            $stmt->execute([$product_id]);
            $available_stock = $stmt->fetch()['stock'];


            if ($order_qty > $available_stock) {
                throw new Exception("Stock not enough for product ID: $product_id");
            }


            //FIFO DEDUCTION
            $qty_needed = $order_qty;


            $stocks = $conn->prepare("
                SELECT * FROM stock
                WHERE product_id=? AND remaining_qty > 0
                ORDER BY id ASC
            ");
            $stocks->execute([$product_id]);


            foreach ($stocks as $s) {


                if ($qty_needed <= 0) break;


                $deduct = min($qty_needed, $s['remaining_qty']);


                // UPDATE STOCK
                $conn->prepare("
                    UPDATE stock
                    SET remaining_qty = remaining_qty - ?
                    WHERE id = ?
                ")->execute([$deduct, $s['id']]);


                // (OPTIONAL BUT IMPORTANT FOR CANCEL SUPPORT)
                $conn->prepare("
                    INSERT INTO stock_movements(order_id, product_id, stock_id, qty, type)
                    VALUES (?,?,?,?, 'OUT')
                ")->execute([$order_id, $product_id, $s['id'], $deduct]);


                $qty_needed -= $deduct;
            }
        }


        //CLEAR CART
        $conn->prepare("DELETE FROM cart WHERE user_id=?")->execute([$user_id]);


        unset($_SESSION['discount']);


        $conn->commit();


        header("Location: invoice.php?id=" . $order_id);
        exit;
    } catch (Exception $e) {


        $conn->rollBack();


        $_SESSION['error'] = $e->getMessage();
        header("Location: checkout.php");
        exit;
    }
}
?>


<div class="container">
    <h2>Checkout</h2>


    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>


    <div class="row">


        <!-- LEFT SIDE -->
        <div class="col-md-6">


            <form method="post">


                <h5>Billing Address</h5>
                <textarea name="billing_address" class="form-control mb-3" required></textarea>


                <h5>Delivery Address</h5>
                <textarea name="delivery_address" class="form-control mb-3" required></textarea>


                <h5>Payment Method</h5>


                <select name="payment_method" class="form-control mb-3" required>
                    <option value="">Select Payment</option>
                    <option value="COD">Cash on Delivery</option>
                    <option value="BANK">Bank Transfer</option>
                    <option value="ONLINE">Online Payment</option>
                </select>


                <button name="place_order" class="btn btn-success w-100">
                    Place Order
                </button>


            </form>


        </div>


        <!-- RIGHT SIDE (ORDER SUMMARY) -->
        <div class="col-md-6">


            <h5>Order Summary</h5>


            <table class="table table-bordered">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>


                <?php foreach ($items as $i): ?>
                    <tr>
                        <td><?= $i['name'] ?></td>
                        <td><?= $i['qty'] ?></td>
                        <td><?= number_format($i['price'] * $i['qty'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>


            <hr>


            <p>Subtotal: Rs. <?= number_format($total, 2) ?></p>
            <p>Discount: Rs. <?= number_format($discount, 2) ?></p>
            <h4>Grand Total: Rs. <?= number_format($grand_total, 2) ?></h4>


        </div>


    </div>
</div>


<?php
$content = ob_get_clean();
include '../layout.php';
?>
