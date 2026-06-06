<?php
ob_start();
include '../../init.php';

if (!hasPermission('users/edit.php')) {
    die("Access Denied");
}

$conn = dbConnect();

// Get user ID
extract($_POST);

// Load user data
$sql = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $id); //form ekn ena id eka meka bind krnwa
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

//  Load roles
$sql = "SELECT * FROM roles";
$stmt = $conn->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    extract($_POST);

    try {
        //  If password entered → hash it
        if (!empty($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET first_name = :fname,last_name = :lname,email = :email,password = :password,role_id = :role_id WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fname', $first_name);
            $stmt->bindParam(':lname', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();
        } else {
            //  No password change
            $sql = "UPDATE users SET first_name = :fname,last_name = :lname,email = :email,role_id = :role_id WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fname', $first_name);
            $stmt->bindParam(':lname', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();
        }

        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}
?>

<h3>Edit User</h3>

<form method="POST">

    <div class="mb-2">
        <input name="first_name" class="form-control" value="<?= $user['first_name'] ?>" required>
    </div>

    <div class="mb-2">
        <input name="last_name" class="form-control" value="<?= $user['last_name'] ?>" required>
    </div>

    <div class="mb-2">
        <input name="email" type="email" class="form-control" value="<?= $user['email'] ?>" required>
    </div>

    <div class="mb-2">
        <input name="password" type="password" class="form-control" placeholder="Leave blank to keep current password">
    </div>

    <div class="mb-2">
        <select name="role_id" class="form-control">
            <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>"<?= $user['role_id'] == $r['id'] ? 'selected' : '' ?>><?= $r['role_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <button type="submit" name="action" value="update" class="btn btn-primary">Update User</button>

</form>

<?php
$content = ob_get_clean();
include '../layout.php';
?>