<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    extract($_POST);

    $role_name = trim($role_name);

    $error = [];

    // Validation
    if (empty($role_name)) {
        $error['role_name'] = "Role Name is required";
    }

    // Check duplicate role
    if (!empty($role_name)) {

        $sql = "SELECT COUNT(*) FROM roles WHERE role_name = :role_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':role_name', $role_name);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error['role_name'] = "Role already exists";
        }
    }

    // Insert
    if (empty($error)) {

        try {

            $sql = "INSERT INTO roles(role_name)
                    VALUES(:role_name)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':role_name', $role_name);

            $stmt->execute();

            header("Location: index.php");
            exit();

        } catch (PDOException $e) {

            echo "Error : " . $e->getMessage();
        }
    }
}
?>

<h3>Create Role</h3>

<form method="POST">

    <div class="mb-3">
        <label class="form-label">Role Name</label>

        <input type="text"
               name="role_name"
               class="form-control"
               value="<?= isset($role_name) ? htmlspecialchars($role_name) : '' ?>">

        <?php if (!empty($error['role_name'])) { ?>
            <div class="text-danger">
                <?= $error['role_name'] ?>
            </div>
        <?php } ?>
    </div>

    <button type="submit" class="btn btn-success">
        Save
    </button>

    <a href="index.php" class="btn btn-secondary">
        Cancel
    </a>

</form>

<?php
$content = ob_get_clean();
include '../layout.php';
?>