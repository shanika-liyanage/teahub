<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


// Load customers and products
$customers = $conn->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
// Load products with available stock from `stock` table (aggregate remaining_qty)
$products = $conn->query("
    SELECT p.id, p.name, SUM(s.remaining_qty) as total_stock
    FROM products p
    JOIN stock s ON p.id = s.product_id
    WHERE s.remaining_qty > 0
    GROUP BY p.id
    ORDER BY p.name ASC
")->fetchAll(PDO::FETCH_ASSOC);




if (isset($_POST['create'])) {


    $customer_id = $_POST['customer_id'];
    $order_date = $_POST['order_date'];
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];


    try {
        $conn->beginTransaction();


        // 1. Create order (NO stock deduction)
        $stmt = $conn->prepare("
            INSERT INTO sales_orders (customer_id, order_date, status)
            VALUES (:customer_id, :order_date, 'PENDING')
        ");
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':order_date' => $order_date
        ]);


        $order_id = $conn->lastInsertId();


        // 2. Insert items ONLY
        $stmtItem = $conn->prepare("
            INSERT INTO sales_items (sales_order_id, product_id, quantity)
            VALUES (:order_id, :product_id, :quantity)
        ");


        for ($i = 0; $i < count($product_ids); $i++) {


            $pid = $product_ids[$i];
            $qty = $quantities[$i];


            // OPTIONAL: Validate stock availability (NO deduction)
            $stmtStock = $conn->prepare("
                SELECT SUM(remaining_qty) as total_stock
                FROM stock
                WHERE product_id = :pid
            ");
            $stmtStock->execute([':pid' => $pid]);
            $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);


            if (!$stock || $stock['total_stock'] < $qty) {
                throw new Exception("Not enough stock for product ID $pid");
            }


            // Save item
            $stmtItem->execute([
                ':order_id' => $order_id,
                ':product_id' => $pid,
                ':quantity' => $qty
            ]);
        }


        $conn->commit();


        echo "<script>alert('Order Created Successfully (Pending)'); window.location='sales_list.php';</script>";
        exit;


    } catch (Exception $e) {
        $conn->rollBack();
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>




<h3>Create Sales Order</h3>




<form method="POST">
<div class="mb-3">
    <label>Customer</label>
    <select name="customer_id" class="form-control" required>
        <option value="">Select Customer</option>
        <?php foreach($customers as $c): ?>
            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
        <?php endforeach; ?>
    </select>
</div>




<div class="mb-3">
    <label>Order Date</label>
    <input type="date" name="order_date" class="form-control" required>
</div>




<h5>Products</h5>
<table class="table" id="items">
<tr>
    <th>Product</th>
    <th>Quantity</th>
    <th></th>
</tr>
<tr>
    <td>
        <select name="product_id[]" class="form-control" required>
            <option value="">Select Product</option>
            <?php foreach($products as $p): ?>
                <option value="<?= $p['id'] ?>">
                    <?= $p['name'] ?> (Available: <?= $p['total_stock'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="number" name="quantity[]" class="form-control" min="1" required></td>
    <td><button type="button" onclick="removeRow(this)" class="btn btn-danger">X</button></td>
</tr>
</table>


<button type="button" onclick="addRow()" class="btn btn-secondary mb-3">Add Product</button><br>






<button type="submit" name="create" class="btn btn-success">Create Order</button>
</form>




<script>
function addRow() {
    let table = document.getElementById("items");
    let row = table.rows[1].cloneNode(true);
    row.querySelectorAll("input").forEach(input => input.value = "");
    table.appendChild(row);
}
function removeRow(btn) {
    let table = document.getElementById("items");
    if(table.rows.length > 2) btn.closest("tr").remove();
}
</script>





<?php
$content = ob_get_clean();
include '../layout.php';
?>