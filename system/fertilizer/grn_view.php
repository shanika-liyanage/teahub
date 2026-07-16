<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

if (!isset($_GET['id'])) {
    header("Location: grn.php");
    exit;
}

$grn_id = $_GET['id'];

/* GRN Details */
$sql = "SELECT g.*,
               po.id AS po_number
        FROM fertilizer_grn g
        JOIN fertilizer_purchase_orders po
            ON g.purchase_id = po.id
        WHERE g.id=?";

$stmt = $conn->prepare($sql);
$stmt->execute([$grn_id]);
$grn = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grn) {
    die("GRN not found");
}

/* GRN Items */
$sql = "SELECT gi.*,
               fi.name,
               gi.unit
        FROM fertilizer_grn_items gi
        JOIN fertilizer_items fi
            ON gi.fertilizer_item_id = fi.id
        WHERE gi.grn_id=?";

$stmt = $conn->prepare($sql);
$stmt->execute([$grn_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>GRN Details</h3>

<table class="table table-bordered">

    <tr>
        <th>GRN ID</th>
        <td><?= $grn['id'] ?></td>
    </tr>

    <tr>
        <th>PO Number</th>
        <td>PO-<?= $grn['po_number'] ?></td>
    </tr>

    <tr>
        <th>Received Date</th>
        <td><?= $grn['received_date'] ?></td>
    </tr>

</table>

<h4>Received Items</h4>

<table class="table table-bordered">

    <tr>
        <th>Product</th>
       
        <th>Received Qty</th>
        <th>Cost</th>
        <th>Total</th>
    </tr>

    <?php
    $grand = 0;

    foreach ($items as $item):

        $total = $item['received_qty'] * $item['cost'];
        $grand += $total;
    ?>

    <tr>
        <td><?= $item['name'] ?></td>
       
        <td><?= $item['received_qty'] ?></td>
        <td><?= number_format($item['cost'],2) ?></td>
        <td><?= number_format($total,2) ?></td>
    </tr>

    <?php endforeach; ?>

    <tr>
        <th colspan="3" class="text-end">Grand Total</th>
        <th><?= number_format($grand,2) ?></th>
    </tr>

</table>

<a href="grn_index.php" class="btn btn-secondary">Back</a>

<?php
$content = ob_get_clean();
include '../layout.php';
?>