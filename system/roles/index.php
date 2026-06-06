<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
$sql = "SELECT * FROM roles";
$stmt = $conn->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Roles</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Role Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($roles as $role): ?>
            <tr>
                <td><?= $role['id'] ?></td>
                <td><?= $role['role_name'] ?></td>
                <td>
                    <form action="permissions.php" method="POST">
                        <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                        <button type="submit" name="action" value="permissions" class="btn btn-sm btn-primary">Assign Permissions</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
include '../layout.php';
?>