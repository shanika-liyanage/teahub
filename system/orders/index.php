<?php
ob_start();
include '../../init.php';
$conn = dbConnect();



// Join customers table
$sql = "SELECT so.*, c.name as customer_name
        FROM sales_orders so
        JOIN customers c ON so.customer_id = c.id
        ORDER BY so.id DESC";


$stmt = $conn->prepare($sql);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<h3>Sales Orders</h3>


<a href="create.php" class="btn btn-primary mb-3">Create Order</a>


<table class="table table-bordered">
<tr>
    <th>ID</th>
    <th>Customer</th>
    <th>Date</th>
    <th>Status</th>
    <th>Actions</th>
</tr>


<?php foreach($sales as $s): ?>
<tr>
    <td><?= $s['id'] ?></td>
    <td><?= $s['customer_name'] ?></td>
    <td><?= $s['order_date'] ?></td>
    <td><?= $s['status'] ?></td>
    <td>
        <a href="sales_view.php?id=<?= $s['id'] ?>" class="btn btn-info btn-sm">View</a>
        <?php if($s['status'] == 'PENDING'): ?>
        <a href="sales_issue.php?id=<?= $s['id'] ?>" class="btn btn-warning btn-sm">Issue</a>
    <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>





<?php
$content = ob_get_clean();
include '../layout.php';
?>