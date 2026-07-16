<?php
ob_start();
include '../init.php';
$conn = dbConnect();
date_default_timezone_set('Asia/Colombo');
extract($_GET);
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    extract($_POST);
    $errors = [];
    $password = password_hash($password, PASSWORD_DEFAULT);
    $sql="SELECT * FROM users WHERE reset_token = :token AND reset_expires >= NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($stmt->rowCount() > 0){
        $sql = "UPDATE users SET password = :password,
                 reset_token = NULL,
                 reset_expires = NULL
                 WHERE reset_token = :token";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $errors['reset_token'] = "Your password has been reset successfully.";
    }else{
        $errors['reset_token'] = "Invalid or expired token";
    }

}
?>
<section class="mt-5 login-bg">
    <div class="container mt-5 ">
        <!-- Login Form -->
        <div class="row justify-content-center align-items-center min-vh-50">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="glass-form border-white border-opacity-25 rounded-4 shadow-lg p-4 p-md-5 ">
                    <div class="login-logo text-center text-white">
                        <a href="index.php"><img src="<?=WEB_URL?>assets/img/logo1.svg" alt="Logo" width="80" height="80" class="me-2 login-icon"></a>
                        <h2 class="fw-bold mb-2 text-white">Fathima Estate</h2>
                        <p class="text-opacity-80 mb-0">Reset password</p>
                    </div>
                    <div class="text-danger"><?= @$errors['reset_token'] ?> </div>

                    <form method="post" class="mt-3">
                        <input type="hidden" name="token" value="<?= $token ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label text-white fw-medium">Password</label>
                            <input type="password" class="form-control bg-white bg-opacity-70 border-0  py-3"
                                   name="password" id="password" placeholder="Enter your new password">
                        </div>
                        <div class="d-grid gap-2 ">
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include 'layout.php';
?>


