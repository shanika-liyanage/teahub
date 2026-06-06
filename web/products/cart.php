<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


$total = 0;
$discount = 0;


//REMOVE ITEM
if (isset($_GET['remove'])) {


    if (isset($_SESSION['user_id'])) {
        $conn->prepare("DELETE FROM cart WHERE id=?")
             ->execute([$_GET['remove']]);
    } else {
        unset($_SESSION['cart'][$_GET['remove']]);
    }


    header("Location: cart.php");
    exit;
}


// UPDATE QTY
if (isset($_POST['update_cart'])) {


    if (isset($_SESSION['user_id'])) {


        foreach ($_POST['qty'] as $id => $qty) {
            $conn->prepare("UPDATE cart SET qty=? WHERE id=?")
                 ->execute([$qty, $id]);
        }


    } else {


        foreach ($_POST['qty'] as $id => $qty) {
            $_SESSION['cart'][$id]['qty'] = $qty;
        }
    }


    header("Location: cart.php");
    exit;
}


// APPLY DISCOUNT (simple logic)
if (isset($_POST['apply_discount'])) {


    if ($_POST['discount_code'] == "SAVE10") {
        $_SESSION['discount'] = 10; // 10%
    } else {
        $_SESSION['discount'] = 0;
        $_SESSION['error'] = "Invalid coupon!";
    }


    header("Location: cart.php");
    exit;
}
?>


<div class="container">
<h2>My Cart</h2>


<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
<?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>


<form method="post">


<table class="table table-bordered">
<tr>
    <th>Product</th>
    <th>Price</th>
    <th width="120">Qty</th>
    <th>Total</th>
    <th>Action</th>
</tr>


<?php if (isset($_SESSION['user_id'])): ?>


    <!-- DB CART -->
    <?php
    $user_id = $_SESSION['user_id'];


    $stmt = $conn->prepare("
        SELECT c.id, p.name, p.price, c.qty
        FROM cart c
        JOIN products p ON p.id=c.product_id
        WHERE user_id=?
    ");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();


    foreach ($items as $i):
        $sub = $i['price'] * $i['qty'];
        $total += $sub;
    ?>
    <tr>
        <td><?= $i['name'] ?></td>
        <td><?= number_format($i['price'],2) ?></td>
        <td>
            <input type="number" name="qty[<?= $i['id'] ?>]" value="<?= $i['qty'] ?>" min="1" class="form-control">
        </td>
        <td><?= number_format($sub,2) ?></td>
        <td>
            <a href="?remove=<?= $i['id'] ?>" class="btn btn-danger btn-sm">Remove</a>
        </td>
    </tr>
    <?php endforeach; ?>


<?php else: ?>


    <!-- SESSION CART -->
    <?php
    if (!empty($_SESSION['cart'])):
        foreach ($_SESSION['cart'] as $id => $item):


            $p = $conn->query("SELECT * FROM products WHERE id=$id")->fetch();
            $sub = $p['price'] * $item['qty'];
            $total += $sub;
    ?>
    <tr>
        <td><?= $p['name'] ?></td>
        <td><?= number_format($p['price'],2) ?></td>
        <td>
            <input type="number" name="qty[<?= $id ?>]" value="<?= $item['qty'] ?>" min="1" class="form-control">
        </td>
        <td><?= number_format($sub,2) ?></td>
        <td>
            <a href="?remove=<?= $id ?>" class="btn btn-danger btn-sm">Remove</a>
        </td>
    </tr>
    <?php endforeach; endif; ?>


<?php endif; ?>


</table>


<button name="update_cart" class="btn btn-warning">Update Cart</button>


</form>


<hr>


<!-- DISCOUNT -->
<form method="post" class="mt-3">
    <div class="row">
        <div class="col-md-4">
            <input name="discount_code" placeholder="Enter Coupon Code" class="form-control">
        </div>
        <div class="col-md-2">
            <button name="apply_discount" class="btn btn-info">Apply</button>
        </div>
    </div>
</form>


<?php
// APPLY DISCOUNT
if (isset($_SESSION['discount'])) {
    $discount = ($total * $_SESSION['discount']) / 100;
}


$grandTotal = $total - $discount;
?>


<hr>


<h5>Subtotal: Rs. <?= number_format($total,2) ?></h5>
<h5>Discount: Rs. <?= number_format($discount,2) ?></h5>
<h4>Grand Total: Rs. <?= number_format($grandTotal,2) ?></h4>


<br>


<?php if (!isset($_SESSION['user_id'])): ?>
    <a href="login.php" class="btn btn-warning">Login to Checkout</a>
<?php else: ?>
    <a href="checkout.php" class="btn btn-success">Checkout</a>
<?php endif; ?>


</div>


<?php
$content = ob_get_clean();
include '../layout.php';
?>
