<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
$sql = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>User List</h3>

<a href="create.php" class="btn btn-primary mb-2">Add User</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= $u['first_name'] . ' ' . $u['last_name'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><?= $u['role_name'] ?></td>
                <td>
                    <?php if (hasPermission('users/edit.php')): ?>
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