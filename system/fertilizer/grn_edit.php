<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

if (!isset($_GET['id'])) {
    header("Location: grn.php");
    exit;
}

$grn_id = $_GET['id'];

/* Get GRN */

$sql = "SELECT * FROM fertilizer_grn WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$grn_id]);
$grn = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grn) {
    die("GRN not found");
}

/* Get Items + Ordered Qty */

$sql = "SELECT gi.id,
               gi.received_qty,
               gi.cost,
               fi.name,
               poi.qty AS ordered_qty
        FROM fertilizer_grn_items gi
        JOIN fertilizer_items fi
            ON gi.fertilizer_item_id = fi.id
        JOIN fertilizer_purchase_order_items poi
            ON poi.purchase_order_id = ?
           AND poi.fertilizer_item_id = gi.fertilizer_item_id
        WHERE gi.grn_id=?";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $grn['purchase_id'],
    $grn_id
]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Update */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
       

    try {
        $conn->beginTransaction();

        $received_date = $_POST['received_date'];

        // update GRN header
        $sql = "UPDATE fertilizer_grn
                SET received_date=?
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$received_date, $grn_id]);
        

        foreach ($_POST['item_id'] as $key => $item_id) {
           

            $qty = $_POST['quantity'][$key];
            $cost = $_POST['cost'][$key];
            $ordered = $_POST['ordered_qty'][$key];
            

            if ($qty > $ordered) {
                throw new Exception("Received quantity cannot exceed ordered quantity.");
            }

            $sql = "UPDATE fertilizer_grn_items
                    SET received_qty=?,
                        cost=?
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->execute([$qty, $cost, $item_id]);
        }

        $conn->commit();

        header("Location: grn_index.php?success=1");
        exit;

    } catch (Exception $e) {

        $conn->rollBack();
        die("Update failed: " . $e->getMessage());
    }
}
?>

<h3>Edit GRN</h3>

<form method="post">

<label>Received Date</label>

<input type="date"
       name="received_date"
       class="form-control mb-3"
       value="<?= $grn['received_date'] ?>"
       required>

<table class="table table-bordered">

<tr>
    <th>Product</th>
    <th>Ordered Qty</th>
    <th>Received Qty</th>
    <th>Cost</th>
    <th>Total</th>
</tr>

<?php foreach($items as $item): ?>

<tr>

<td>
<?= $item['name'] ?>

<input type="hidden"
       name="item_id[]"
       value="<?= $item['id'] ?>">

<input type="hidden"
       name="ordered_qty[]"
       value="<?= $item['ordered_qty'] ?>">

</td>

<td>
<?= $item['ordered_qty'] ?>
</td>

<td>

<input type="number"
       class="form-control"
       name="quantity[]"
       value="<?= $item['received_qty'] ?>"
       min="0"
       max="<?= $item['ordered_qty'] ?>"
       required>

</td>

<td>

<input type="number"
       step="0.01"
       class="form-control"
       name="cost[]"
       value="<?= $item['cost'] ?>"
       required>

</td>

<td>

<?= number_format($item['received_qty']*$item['cost'],2) ?>

</td>

</tr>

<?php endforeach; ?>

</table>

<button type="submit" class="btn btn-success">
Update GRN
</button>

<a href="grn_index.php" class="btn btn-secondary">
Cancel
</a>

</form>

<?php
$content = ob_get_clean();
include '../layout.php';
?>