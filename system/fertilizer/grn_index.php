<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

$sql = "SELECT g.*, po.id as po_number
        FROM fertilizer_grn g
        JOIN fertilizer_purchase_orders po ON g.purchase_id = po.id
        ORDER BY g.id DESC";


$stmt = $conn->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<h3>GRN List</h3>


<a href="create.php" class="btn btn-primary mb-3">Create GRN</a>


<table class="table table-bordered">
<tr>
    <th>ID</th>
    <th>PO #</th>
    <th>Received Date</th>
    <th>Actions</th>
</tr>


<?php foreach($data as $row): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['po_number'] ?></td>
    <td><?= $row['received_date'] ?></td>
    <td>
        <a href="grn_view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
        <a href="grn_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php 
$content = ob_get_clean();
include '../layout.php';
?>