<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
date_default_timezone_set('Asia/Colombo');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);
    $errors = [];
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));

        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $errors['email'] = $expires;
        $sql = "UPDATE users SET 
                 reset_token = :token,
                 reset_expires = :expires
                 WHERE email = :email ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->execute();
        $link = "http://localhost/test-fathimaEstate/web/reset_password.php?token=$token";
        $errors['email'] = $link;
        $body = "<h1>Reset Your Password</h1>";
        $body .= "<p>Click <a href='$link'>here</a> to reset your password.</p>";
        sendEmail($email, $row['first_name'], "Reset Password", $body);
    } else {
        $errors['email'] = "The Email you entered does not exist.";
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
                        <a href="../index.php"><img src="<?= WEB_URL ?>assets/img/logo1.svg" alt="Logo" width="80"
                                height="80" class="me-2 login-icon"></a>
                        <h2 class="fw-bold mb-2 text-white">Fathima Estate</h2>
                        <p class="text-opacity-80 mb-0">Reset password</p>
                    </div>
                    <div class="text-danger"><?= @$errors['email'] ?> </div>

                    <form method="post" class="mt-3">
                        <input type="hidden" name="redirect" value="<?= $redirect ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label text-white fw-medium">Email Address</label>
                            <input type="email" class="form-control bg-white bg-opacity-70 border-0  py-3" name="email"
                                id="email" placeholder="Enter your email">
                        </div>
                        <div class="d-grid gap-2 ">
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold">Submit</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include '../layout.php';
?>