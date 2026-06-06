<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
print_r($_POST);
// Get role
extract($_POST);
extract($_GET);

if (!$role_id) {
    die("Role not selected");
}

//  Load roles (for dropdown)
$sql = "SELECT * FROM roles";
$stmt = $conn->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load modules
$sql = "SELECT * FROM modules ORDER BY module_index ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get assigned permissions
$sql = "SELECT module_id FROM role_permissions WHERE role_id = :role_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':role_id', $role_id);
$stmt->execute();
$assigned = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build tree 
//main menu
$tree = [];
foreach ($modules as $m) {
    if ($m['parent_id'] == NULL) {
        $tree[$m['id']] = $m;
        $tree[$m['id']]['children'] = [];
    }
}

// sub menu
foreach ($modules as $m) {
    if ($m['parent_id'] != NULL) {
        $tree[$m['parent_id']]['children'][] = $m;
    }
}
?>

<h3>Assign Permissions</h3>

<!-- Role Select -->
<form method="POST" class="mb-3">
    <label>Select Role</label>
    <select name="role_id" class="form-control" onchange="this.form.submit()">
        <option value="">-- Select Role --</option>
        <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= $role_id == $r['id'] ? 'selected' : '' ?>><?= $r['role_name'] ?></option>
        <?php endforeach; ?>
    </select>
</form>

<form method="POST" action="save_permissions.php">

    <input type="hidden" name="role_id" value="<?= $role_id ?>">

    <?php foreach ($tree as $parent): ?>

        <div class="card mb-3">
            <!--  MAIN MODULE -->
            <div class="card-header">
                <div class="form-check">
                    <input class="form-check-input parent-checkbox" type="checkbox" value="<?= $parent['id'] ?> " <?= in_array($parent['id'], $assigned) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold"><?= $parent['module_name'] ?>
                    </label>
                </div>
            </div>

            <div class="card-body">
                <?php foreach ($parent['children'] as $child): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="modules[]" value="<?= $child['id'] ?>" <?= in_array($child['id'], $assigned) ? 'checked' : '' ?>>
                        <label class="form-check-label"> <?= $child['module_name'] ?>
                            <?php if ($child['action']): ?>
                                <span class="badge bg-info">
                                    <?= strtoupper($child['action']) ?>
                                </span>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <button class="btn btn-success">Save Permissions</button>

</form>

<script>
    document.querySelectorAll('.form-check-input').forEach(function(checkbox) {

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                let card = this.closest('.card');
                let parentCheckbox = card.querySelector('.parent-checkbox');

                if (parentCheckbox) {
                    parentCheckbox.checked = true;
                }
            }
        });
});
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>