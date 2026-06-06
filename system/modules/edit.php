<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


//Get ID
$id = $_GET['id'] ?? null;


if (!$id) {
    die("Invalid Module ID");
}


//Load module data
$stmt = $conn->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$module) {
    die("Module not found");
}


// 🔹 Load parent modules (exclude current to prevent self-parenting)
$parents = $conn->prepare("
    SELECT * FROM modules
    WHERE parent_id IS NULL AND id != ?
");
$parents->execute([$id]);
$parents = $parents->fetchAll(PDO::FETCH_ASSOC);


//UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $module_name  = trim($_POST['module_name']);
    $url          = !empty($_POST['url']) ? trim($_POST['url']) : NULL;
    $action       = !empty($_POST['action']) ? $_POST['action'] : NULL;
    $parent_id    = !empty($_POST['parent_id']) ? $_POST['parent_id'] : NULL;
    $module_index = $_POST['module_index'] ?? 1;
    $ismenu       = $_POST['ismenu'];


    // Validation
    if (empty($module_name)) {
        echo "<div class='alert alert-danger'>Module name is required</div>";
    } else {


        // Main module logic
        if ($parent_id == NULL) {
            $action = NULL;
            $url = NULL;
        }


        try {


            $conn->beginTransaction();


            // 🔹 Duplicate check (exclude current record)
            $check = $conn->prepare("
                SELECT id FROM modules
                WHERE module_name = :module_name
                AND parent_id <=> :parent_id
                AND id != :id
            ");


            $check->execute([
                ':module_name' => $module_name,
                ':parent_id'   => $parent_id,
                ':id'          => $id
            ]);


            if ($check->rowCount() > 0) {
                throw new Exception("Module already exists under this parent");
            }


            // 🔹 Update
            $sql = "UPDATE modules SET
                        module_name = :module_name,
                        url = :url,
                        action = :action,
                        parent_id = :parent_id,
                        module_index = :module_index,
                        ismenu = :ismenu
                    WHERE id = :id";


            $stmt = $conn->prepare($sql);


            $stmt->execute([
                ':module_name'  => $module_name,
                ':url'          => $url,
                ':action'       => $action,
                ':parent_id'    => $parent_id,
                ':module_index' => $module_index,
                ':ismenu'       => $ismenu,
                ':id'           => $id
            ]);


            $conn->commit();


            header("Location: index.php?updated=1");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
        }
    }
}
?>


<h3>Edit Module</h3>


<form method="POST">


    <!-- Module Name -->
    <div class="mb-3">
        <label class="form-label">Module Name</label>
        <input name="module_name" class="form-control"
            value="<?= $module['module_name'] ?>" required>
    </div>


    <!-- URL -->
    <div class="mb-3">
        <label class="form-label">URL</label>
        <input name="url" class="form-control"
            value="<?= $module['url'] ?>">
    </div>


    <!-- Action -->
    <div class="mb-3">
        <label class="form-label">Action</label>
        <select name="action" class="form-control" id="actionField">
            <option value="">-- Select Action --</option>
            <option value="view" <?= $module['action'] == 'view' ? 'selected' : '' ?>>View</option>
            <option value="add" <?= $module['action'] == 'add' ? 'selected' : '' ?>>Add</option>
            <option value="edit" <?= $module['action'] == 'edit' ? 'selected' : '' ?>>Edit</option>
            <option value="delete" <?= $module['action'] == 'delete' ? 'selected' : '' ?>>Delete</option>
        </select>
    </div>


    <!-- Parent -->
    <div class="mb-3">
        <label class="form-label">Parent Module</label>
        <select name="parent_id" class="form-control" id="parentField">
            <option value="">-- Main Module --</option>
            <?php foreach ($parents as $p): ?>
                <option value="<?= $p['id'] ?>"
                    <?= $module['parent_id'] == $p['id'] ? 'selected' : '' ?>>
                    <?= $p['module_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>


    <!-- Order -->
    <div class="mb-3">
        <label class="form-label">Menu Order</label>
        <input name="module_index" type="number" class="form-control"
            value="<?= $module['module_index'] ?>">
    </div>


    <!-- Menu -->
    <div class="mb-3">
        <label class="form-label">Show in Sidebar</label>
        <select name="ismenu" class="form-control">
            <option value="1" <?= $module['ismenu'] == 1 ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= $module['ismenu'] == 0 ? 'selected' : '' ?>>No</option>
        </select>
    </div>


    <button class="btn btn-primary">Update Module</button>


</form>


<!-- 🔥 Smart UX Script -->
<script>
    function toggleAction() {
        let parent = document.getElementById('parentField').value;
        let action = document.getElementById('actionField');


        if (parent === "") {
            action.value = "";
            action.disabled = true;
        } else {
            action.disabled = false;
        }
    }


    // Run on load
    toggleAction();


    // Run on change
    document.getElementById('parentField').addEventListener('change', toggleAction);
</script>


<?php
$content = ob_get_clean();
include '../layout.php';
?>
