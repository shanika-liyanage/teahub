<?php
ob_start();
include '../../init.php';
$conn = dbConnect();


// Load parent modules
$parents = $conn->query("SELECT * FROM modules WHERE parent_id IS NULL")
    ->fetchAll(PDO::FETCH_ASSOC);


if ($_POST) {


    $module_name = $_POST['module_name'];
    $url = $_POST['url'] ?: NULL;
    $action = $_POST['action'] ?: NULL;
    $parent_id = $_POST['parent_id'] ?: NULL;
    $module_index = $_POST['module_index'];
    $ismenu = $_POST['ismenu'];


    // Main module should not have action
    if ($parent_id == NULL) {
        $action = NULL;
    }


    $sql = "INSERT INTO modules
            (module_name, url, action, parent_id, module_index, ismenu)
            VALUES
            (:module_name, :url, :action, :parent_id, :module_index, :ismenu)";


    $stmt = $conn->prepare($sql);


    $stmt->bindParam(':module_name', $module_name);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':parent_id', $parent_id);
    $stmt->bindParam(':module_index', $module_index);
    $stmt->bindParam(':ismenu', $ismenu);


    $stmt->execute();


    header("Location: index.php");
}
?>


<h3>Create Module</h3>


<form method="POST">


    <!-- Module Name -->
    <div class="mb-3">
        <label class="form-label">Module Name</label>
        <input name="module_name" class="form-control" placeholder="Enter module name" required>
    </div>


    <!-- URL -->
    <div class="mb-3">
        <label class="form-label">URL</label>
        <input name="url" class="form-control" placeholder="e.g. modules/users/create.php">
    </div>


    <!-- Parent Module -->
    <div class="mb-3">
        <label class="form-label">Parent Module</label>
        <select name="parent_id" class="form-control">
            <option value="">-- Main Module --</option>
            <?php foreach ($parents as $p): ?>
                <option value="<?= $p['id'] ?>"><?= $p['module_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>


    <!-- Action -->
    <div class="mb-3">
        <label class="form-label">Action</label>
        <select name="action" class="form-control">
            <option value="">-- Select Action (Sub Modules Only) --</option>
            <option value="view">View</option>
            <option value="add">Add</option>
            <option value="edit">Edit</option>
            <option value="delete">Delete</option>
        </select>
    </div>






    <!-- Module Order -->
    <div class="mb-3">
        <label class="form-label">Menu Order</label>
        <input name="module_index" type="number" class="form-control" value="1">
    </div>


    <!-- Menu Visibility -->
    <div class="mb-3">
        <label class="form-label">Show in Sidebar Menu</label>
        <select name="ismenu" class="form-control">
            <option value="1">Yes (Visible)</option>
            <option value="0">No (Hidden)</option>
        </select>
    </div>


    <button class="btn btn-success">Save Module</button>


</form>


<!-- Optional UX Script -->
<script>
    document.querySelector('select[name="parent_id"]').addEventListener('change', function() {
        let actionField = document.querySelector('select[name="action"]');


        if (this.value === "") {
            actionField.value = "";
            actionField.disabled = true;
        } else {
            actionField.disabled = false;
        }
    });


    // Initial state
    document.querySelector('select[name="action"]').disabled = true;
</script>


<?php
$content = ob_get_clean();
include '../layout.php';
?>

