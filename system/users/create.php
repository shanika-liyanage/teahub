<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
// Load roles
//$roles = $conn->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
$sql = "SELECT * FROM roles";
$stmt = $conn->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $email = trim($email);
    $password = trim($password);

    $error = [];
    if (empty($first_name)) {
        $error['first_name'] = "First Name is required";
    }
    if (empty($last_name)) {
        $error['last_name'] = "Last Name is required";
    }
    if (empty($email)) {
        $error['email'] = "Email is required";
    }
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error['email'] = "Invalid email format";
        } else {
            try {
                // Check if email already exists
                $conn = dbConnect();
                $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $error['email'] = "Email already exists";
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }
    if (empty($password)) {
        $error['password'] = "Password is required";
    }

    if (empty($error)) {
        // No validation errors, proceed with database insertion
        try {
            $conn = dbConnect();
            $password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, email, password, role_id) VALUES(:fname, :lname, :email, :password, :role_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':fname', $first_name);
            $stmt->bindParam(':lname', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();
            
            header("Location: index.php");
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<h3>Create User</h3>

<form method="POST">
    <input name="first_name" class="form-control mb-2" placeholder="First Name">
    <input name="last_name" class="form-control mb-2" placeholder="Last Name">
    <input name="email" type="email" class="form-control mb-2" placeholder="Email">
    <input name="password" type="password" class="form-control mb-2" placeholder="Password">

    <select name="role_id" class="form-control mb-2">
        <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>"><?= $r['role_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-success">Save</button>
</form>

<?php
$content = ob_get_clean();
include '../layout.php';
?>