<?php
ob_start();
include '../../init.php'; // Include the initialization file (which includes config.php)
?>
<div class="container-fluid contact py-5 page-header">
    <div class=" wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
        <div class="container">
            

        
            <div class="row g-5 mb-5">



                <div class="col-lg-6">
                    <div class="card" style="width: 18rem;">
                        <img src="<?= WEB_URL ?>assets/img/payment.jpg" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Payments</h5>
                           
                            <a href="<?= WEB_URL ?>member/payment.php" class="btn btn-primary">view</a>
                        </div>
                    </div>
                </div>


                <div class="col-lg-6">
                    <div class="card" style="width: 18rem;">
                        <img src="<?= WEB_URL ?>assets/img/teasupply.jpg" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title">Order Details</h5>
                           
                            <a href="#" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </div>


               

               













            </div>
            </div>
        </div>
    </div>


    <?php
    $content = ob_get_clean();
    include '../layout-dashboard.php';
    ?>