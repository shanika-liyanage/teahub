<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


// Load all modules with parent name
$sql = "SELECT m.*,p.module_name AS parent_name
FROM modules m
LEFT JOIN modules p ON m.parent_id = p.id
ORDER BY COALESCE(m.parent_id, m.id),m.parent_id IS NULL DESC,m.module_index ASC";


$modules = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>


<h3>Module Management</h3>


<a href="create.php" class="btn btn-primary mb-3">Add Module</a>


<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Module saved successfully</div>
<?php endif; ?>


<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Module Name</th>
            <th>Parent Module</th>
            <th>URL</th>
            <th>Action</th>
            <th>Menu</th>
            <th>Order</th>
            <th width="150">Actions</th>
        </tr>
    </thead>
    <tbody>


        <?php
        $count = 1;


        // Separate main & sub modules
        $mainModules = [];
        $subModules = [];


        foreach ($modules as $m) {
            if ($m['parent_id'] == NULL) {
                $mainModules[] = $m;
            } else {
                $subModules[] = $m;
            }
        }
        ?>


        <?php foreach ($mainModules as $main): ?>


            <!-- MAIN MODULE -->
            <tr class="table-primary fw-bold">
                <td><?= $count++ ?></td>
                <td><?= $main['module_name'] ?></td>
                <td>—</td>
                <td><?= $main['url'] ?></td>
                <td><?= strtoupper($main['action']) ?></td>
                <td><?= $main['ismenu'] ? 'Yes' : 'No' ?></td>
                <td><?= $main['module_index'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $main['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete.php?id=<?= $main['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete this module?')">Delete</a>
                </td>
            </tr>


            <!-- SUB MODULES -->
            <?php foreach ($subModules as $sub): ?>
                <?php if ($sub['parent_id'] == $main['id']): ?>
                    <tr>
                        <td><?= $count++ ?></td>
                        <td class="ps-4">↳ <?= $sub['module_name'] ?></td>
                        <td><?= $main['module_name'] ?></td>
                        <td><?= $sub['url'] ?></td>
                        <td><?= strtoupper($sub['action']) ?></td>
                        <td><?= $sub['ismenu'] ? 'Yes' : 'No' ?></td>
                        <td><?= $sub['module_index'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this module?')">Delete</a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>


        <?php endforeach; ?>


    </tbody>
</table>
<?php
$content = ob_get_clean();
include '../layout.php';
?>