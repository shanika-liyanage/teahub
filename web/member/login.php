<?php
ob_start();
include '../../init.php'; // Include the initialization file (which includes config.php)
?>


<div class="container-fluid contact py-5 page-header">
    <div class="container ">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
                <h3 class="mb-4 text-center">Login</h3>
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // Collect form data

                    header('Location:dashboard.php'); //header kynne jump krnn kyl
                }
                ?>


                <form method="post">



                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="row">
                    <div class="col-lg-6 form-check">

                        
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember" class="form-label">Remember me</label>

                    </div>

                    <div class=" col-lg-6 mb-3 ">
                        <a href="forgot_password.php" class="text-light text-decoration-none">Forgot Password ?</a>
                    </div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-dark  py-2 px-2 animated zoomIn">Login</button>
</div>
                </form>
            </div>
        </div>


    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>