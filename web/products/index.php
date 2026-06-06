<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


$products = $conn->query("
SELECT
    p.id,
    p.name,
    p.sku,
    p.price,
    SUM(s.remaining_qty) AS total_stock
FROM products p
LEFT JOIN stock s ON s.product_id = p.id
GROUP BY p.id
")->fetchAll(PDO::FETCH_ASSOC);
?>


<?php
// ADD TO CART
if (isset($_POST['add'])) {


    $product_id = $_POST['product_id'];
    $qty = (int) $_POST['qty'];


    if ($qty <= 0) {
        $_SESSION['error'] = "Invalid quantity";
        header("Location: products.php");
        exit;
    }


    // CHECK STOCK
    $stmt = $conn->prepare("
        SELECT SUM(remaining_qty) as stock
        FROM stock WHERE product_id=?
    ");
    $stmt->execute([$product_id]);
    $stock = $stmt->fetch()['stock'];


    if ($qty > $stock) {
        $_SESSION['error'] = "Not enough stock!";
        header("Location: products.php");
        exit;
    }


    //LOGGED USER → DB
    if (isset($_SESSION['user_id'])) {


        $user_id = $_SESSION['user_id'];


        $stmt = $conn->prepare("
            SELECT * FROM cart
            WHERE user_id=? AND product_id=?
        ");
        $stmt->execute([$user_id, $product_id]);
        $row = $stmt->fetch();


        if ($row) {
            $conn->prepare("UPDATE cart SET qty=qty+? WHERE id=?")
                ->execute([$qty, $row['id']]);
        } else {
            $conn->prepare("INSERT INTO cart(user_id,product_id,qty) VALUES(?,?,?)")
                ->execute([$user_id, $product_id, $qty]);
        }
    } else {
        //SESSION CART
        $_SESSION['cart'][$product_id]['qty'] =
            ($_SESSION['cart'][$product_id]['qty'] ?? 0) + $qty;
    }


    $_SESSION['success'] = "Item added to cart!";
}
?>


<div class="container">
    <?php
    $cart = getCartSummary($conn);
    ?>
    <div class="bg-dark text-white p-3 mb-3">
        Items: <b><?= $cart['count'] ?></b> |
        Total: <b>Rs. <?= number_format($cart['total'], 2) ?></b>
    </div>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>


    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <h2>Products</h2>


    <div class="row">


        <?php foreach ($products as $p): ?>
            <div class="col-md-3">
                <div class="card p-3 mb-3">


                    <h5><?= $p['name'] ?></h5>
                    <p><b>SKU:</b> <?= $p['sku'] ?></p>


                    <p><b>Price:</b> Rs. <?= number_format($p['price'], 2) ?></p>


                    <p>
                        <b>Stock:</b>
                        <?php if ($p['total_stock'] > 0): ?>
                            <span class="text-success"><?= $p['total_stock'] ?> Available</span>
                        <?php else: ?>
                            <span class="text-danger">Out of Stock</span>
                        <?php endif; ?>
                    </p>


                    <?php if ($p['total_stock'] > 0): ?>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="number" name="qty" value="1" min="1" max="<?= $p['total_stock'] ?>" class="form-control mb-2">


                            <button name="add" class="btn btn-primary w-100">
                                Add to Cart
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>
                            Out of Stock
                        </button>
                    <?php endif; ?>


                </div>
            </div>
        <?php endforeach; ?>


    </div>
</div>


<?php
$content = ob_get_clean();
include '../layout.php';
?>
