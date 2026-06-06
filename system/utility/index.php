<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
$sql = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Utility List</h3>

<a href="create.php" class="btn btn-primary mb-2">Add Utility</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>value</th>
           
        </tr>
    </thead>
    <tbody>
        <?php foreach ($utility as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= $u['name'] ?></td>
                <td><?= $u['value'] ?></td>
                
                <td>
                    <?php if (hasPermission('utility/edit.php')): ?>
                        <form action="edit.php" method="POST">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" name="action" value="edit" class="btn btn-sm btn-outline-primary">Edit</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
include '../layout.php';
?>