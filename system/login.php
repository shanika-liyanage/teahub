<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Teahub | Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="assets/dist/img/Teahublogo1.png" alt="" width="150">
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <?php

        include '../init.php'; // or your DB file path
        $conn = dbConnect();
        ?>
        <?php
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
            $sql = "SELECT users.*,roles.role_name FROM users JOIN roles ON roles.id=users.role_id WHERE email=:email";
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
              $_SESSION['role_name'] = $user['role_name'];

              header("Location:index.php");
            } else {
              $errors['general'] = "Invalid email or password";
            }
          }
        }

        ?>
        <div class="text-danger">
          <?= @$errors['general'] ?>
        </div>
        <div class="text-danger">
          <?= @$errors['email'] ?>
        </div>
        <div class="text-danger">
          <?= @$errors['password'] ?>
        </div>
        <form method="POST">

          <input name="email" class="form-control mb-2" placeholder="Email">
          <input name="password" type="password" class="form-control mb-2" placeholder="Password">
          <button class="btn btn-primary">Login</button>

        </form>
        <p class="mb-1">
          <a href="forgot-password.html">I forgot my password</a>
        </p>



      </div>
      <!-- /.login-card-body -->






    </div>

  </div>
  <!-- /.login-box -->



  <!-- jQuery -->
  <script src="assets/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="assets/dist/js/adminlte.min.js"></script>
</body>

</html>