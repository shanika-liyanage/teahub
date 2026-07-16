<?php
ob_start();
include '../../init.php'; // Include the initialization file (which includes config.php)



$conn = dbConnect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    $errors = [];
    $email = trim($email);
    $password = trim($password);

    if (empty($email)) {
        $errors['email'] = "Email should not be blank";
    }
    if (empty($password)) {
        $errors['password'] = "Password should not be blank";
    }
    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email=:email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['islogin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $user_id = $_SESSION['user_id'];

            $stmt = $conn->prepare("
    SELECT *
    FROM suppliers
    WHERE user_id =:user_id");
            $stmt->execute([
                ':user_id' => $user_id
            ]);
            $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
            $supplier_id = $supplier['id'];
            $_SESSION['supplier_id'] = $supplier_id;

            header("Location:dashboard.php");
        } else {
            $errors['general'] = "Invalid email or password";
        }
    }
}
?>


<div class="container-fluid contact py-5 page-header">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="card shadow-lg border-0 ">
                    <div class="card-header green-header text-center py-3">
                        <h3 class="mb-0 text-white">Login</h3>
                    </div>

                    <div class="card-body p-4">

                        <?php if (!empty($errors['general'])) { ?>
                            <div class="alert alert-danger">
                                <?= $errors['general'] ?>
                            </div>
                        <?php } ?>

                        <form method="post">

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">
                                    Email Address
                                </label>
                                <input type="email" class="form-control" name="email" id="email"
                                    value="<?= $_POST['email'] ?? '' ?>" placeholder="Enter your email">
                                <span class="text-danger small">
                                    <?= $errors['email'] ?? "" ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">
                                    Password
                                </label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Enter your password">
                                <span class="text-danger small">
                                    <?= $errors['password'] ?? "" ?>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">

                                    <label class="form-check-label" for="remember">
                                        Remember Me
                                    </label>
                                </div>

                                <a href="forgot_password.php" class="text-decoration-none">
                                    Forgot Password?
                                </a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn green-btn btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>

                        </form>

                    </div>

                    <div class="card-footer text-center bg-light">
                        <small class="text-muted">
                            Don't have an account?
                            <a href="register.php" class="text-decoration-none">
                                Register Here
                            </a>
                        </small>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>