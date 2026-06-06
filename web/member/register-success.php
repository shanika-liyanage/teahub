<?php
ob_start();
include '../../init.php'; // Include the initialization file
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="row w-100 justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card shadow-lg border-0 rounded-4 text-center p-4">
                
                <div class="card-body">

                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 70px;"></i>
                    </div>

                    <h2 class="card-title mb-3">
                        Application Successfully Submitted
                    </h2>

                    <p class="text-muted mb-2">
                        Thank you for your submission.
                    </p>

                    <p class="text-muted mb-4">
                        Your application has been received and is currently under review.
                        We will contact you soon.
                    </p>

                    <a href="login.php" class="btn btn-dark px-4 py-2 rounded-pill">
                        Go to Login
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>