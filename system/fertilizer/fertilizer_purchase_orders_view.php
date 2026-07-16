<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

$id = $_GET['id'] ?? 0;

/* -----------------------------
   LOAD PO HEADER
------------------------------*/
$sql = "
    SELECT po.*,
           s.company_name
    FROM fertilizer_purchase_orders po
    JOIN fertilizer_suppliers s
        ON po.supplier_id = s.id
    WHERE po.id = :id
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

$po = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$po) {
    die("Purchase Order Not Found");
}

/* -----------------------------
   LOAD PO ITEMS
------------------------------*/
$sql = "
    SELECT poi.*,
           fi.name AS fertilizer_name
    FROM fertilizer_purchase_order_items poi
    JOIN fertilizer_items fi
        ON poi.fertilizer_item_id = fi.id
    WHERE poi.purchase_order_id = :id
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">

    <h2>Purchase Order Details</h2>

    <table class="table table-bordered">

        <tr>
            <th width="250">PO ID</th>
            <td><?= $po['id'] ?></td>
        </tr>

        <tr>
            <th>Supplier</th>
            <td><?= $po['company_name'] ?></td>
        </tr>

        <tr>
            <th>Order Date</th>
            <td><?= $po['order_date'] ?></td>
        </tr>

        <tr>
            <th>Status</th>
            <td><?= $po['status'] ?></td>
        </tr>

    </table>

    <h3>Order Items</h3>

    <table class="table table-striped table-bordered">

        <thead>
            <tr>
                <th>#</th>
                <th>Fertilizer</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Unit Price (Rs)</th>
                <th>Total (Rs)</th>
            </tr>
        </thead>

        <tbody>

            <?php
            $grandTotal = 0;
            $no = 1;

            foreach ($items as $item):

                $lineTotal = $item['qty'] * $item['unit_price'];
                $grandTotal += $lineTotal;
            ?>

                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $item['fertilizer_name'] ?></td>
                    <td><?= $item['unit'] ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td><?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= number_format($lineTotal, 2) ?></td>
                </tr>

            <?php endforeach; ?>

        </tbody>

        <tfoot>
            <tr>
                <th colspan="5" class="text-end">
                    Grand Total
                </th>
                <th>
                    Rs <?= number_format($grandTotal, 2) ?>
                </th>
            </tr>
        </tfoot>

    </table>

    <a href="fertilizer_purchase_orders.php" class="btn btn-secondary">
        Back
    </a>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>