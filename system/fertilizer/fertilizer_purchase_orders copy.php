<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   LOAD SUPPLIERS
------------------------------*/
$sql = "SELECT *
        FROM fertilizer_suppliers
        ORDER BY company_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   LOAD FERTILIZER ITEMS
------------------------------*/
$sql = "SELECT *
        FROM fertilizer_items
        WHERE status='Active'
        ORDER BY name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = [];
$success = '';

/* -----------------------------
   SAVE PURCHASE ORDER
------------------------------*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    extract($_POST);

    if (empty($supplier_id)) {
        $error['supplier_id'] = "Supplier is required";
    }

    if (empty($order_date)) {
        $error['order_date'] = "Order date is required";
    }

    if (empty($products)) {
        $error['products'] = "Select at least one item";
    }

    if (empty($error)) {

        $sql = "INSERT INTO fertilizer_purchase_orders
                (
                    supplier_id,
                    order_date,
                    status
                )
                VALUES
                (
                    :supplier_id,
                    :order_date,
                    'Pending'
                )";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':supplier_id', $supplier_id);
        $stmt->bindParam(':order_date', $order_date);

        $stmt->execute();

        $purchase_order_id = $conn->lastInsertId();

        foreach ($products as $key => $product_id) {

            $qty = $qtys[$key];
            $unit_price = $prices[$key];

            $sql = "INSERT INTO fertilizer_purchase_order_items
                    (
                        purchase_order_id,
                        fertilizer_item_id,
                        qty,
                        unit_price
                    )
                    VALUES
                    (
                        :purchase_order_id,
                        :fertilizer_item_id,
                        :qty,
                        :unit_price
                    )";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':purchase_order_id', $purchase_order_id);
            $stmt->bindParam(':fertilizer_item_id', $product_id);
            $stmt->bindParam(':qty', $qty);
            $stmt->bindParam(':unit_price', $unit_price);

            $stmt->execute();
        }

        $success = "Purchase Order Created Successfully";
    }
}

/* -----------------------------
   LOAD PURCHASE ORDERS
------------------------------*/
$sql = "SELECT po.*,
               fs.company_name
        FROM fertilizer_purchase_orders po
        INNER JOIN fertilizer_suppliers fs
        ON fs.id = po.supplier_id
        ORDER BY po.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

<div class="row">

<div class="col-md-4">

<div class="card">

<div class="card-header">
<h4>Create Purchase Order</h4>
</div>

<div class="card-body">

<?php if(!empty($success)){ ?>

<div class="alert alert-success">
    <?= $success ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">
<label>Supplier</label>

<select name="supplier_id" class="form-control">

<option value="">Select Supplier</option>

<?php foreach($suppliers as $supplier){ ?>

<option value="<?= $supplier['id'] ?>">
    <?= $supplier['company_name'] ?>
</option>

<?php } ?>

</select>

<small class="text-danger">
<?= @$error['supplier_id'] ?>
</small>

</div>

<div class="mb-3">

<label>Order Date</label>

<input type="date"
       name="order_date"
       class="form-control"
       value="<?= date('Y-m-d') ?>">

<small class="text-danger">
<?= @$error['order_date'] ?>
</small>

</div>

<hr>

<h6>Items</h6>

<?php foreach($items as $item){ ?>

<div class="border p-2 mb-2">

<div class="form-check">

<input type="checkbox"
       class="form-check-input"
       name="products[]"
       value="<?= $item['id'] ?>">

<label class="form-check-label">

<?= $item['name'] ?>

</label>

</div>

<input type="number"
       step="0.01"
       name="qtys[]"
       class="form-control mt-2"
       placeholder="Quantity">

<input type="number"
       step="0.01"
       name="prices[]"
       class="form-control mt-2"
       placeholder="Unit Price">

</div>

<?php } ?>

<small class="text-danger">
<?= @$error['products'] ?>
</small>

<button type="submit"
        class="btn btn-success w-100 mt-3">

Create Order

</button>

</form>

</div>

</div>

</div>

<div class="col-md-8">

<div class="card">

<div class="card-header">
<h4>Purchase Order List</h4>
</div>

<div class="card-body">

<table class="table table-bordered">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Supplier</th>
<th>Date</th>
<th>Status</th>
</tr>

</thead>

<tbody>

<?php foreach($orders as $order){ ?>

<tr>

<td><?= $order['id'] ?></td>

<td><?= $order['company_name'] ?></td>

<td><?= $order['order_date'] ?></td>

<td>

<span class="badge bg-warning">
<?= $order['status'] ?>
</span>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>