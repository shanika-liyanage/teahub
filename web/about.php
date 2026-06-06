<?php
ob_start();
include '../init.php'; // Include the initialization file (which includes config.php)
?>
<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center py-5">
        <h1 class="display-2 text-dark mb-4 animated slideInDown">About Us</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Pages</a></li>
                <li class="breadcrumb-item text-dark" aria-current="page">About</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header End -->


<!-- About Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6 text-end">
                        <img class="img-fluid bg-white w-100 mb-3 wow fadeIn" data-wow-delay="0.1s" src="assets/img/about-1.jpg" alt="">
                        <img class="img-fluid bg-white w-50 wow fadeIn" data-wow-delay="0.2s" src="assets/img/about-3.jpg" alt="">
                    </div>
                    <div class="col-6">
                        <img class="img-fluid bg-white w-50 mb-3 wow fadeIn" data-wow-delay="0.3s" src="assets/img/about-4.jpg" alt="">
                        <img class="img-fluid bg-white w-100 wow fadeIn" data-wow-delay="0.4s" src="assets/img/about-2.jpg" alt="">
                    </div>
                </div>
            </div>
            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                <div class="section-title">
                    <p class="fs-5 fw-medium fst-italic text-primary">About Us</p>
                    <p class="display-20">Welcome to Imbulgahagoda Tea Factory, a trusted name in the Sri Lankan tea industry. We are committed to producing and delivering high-quality Ceylon tea by working closely with local tea suppliers.



</p>
       <p>Our process begins with carefully sourcing fresh green tea leaves from trusted growers. These leaves are then processed in our factory using standard tea manufacturing techniques, followed by grading and packaging to ensure freshness and quality in every product.</p>         
<p>We focus on maintaining strong relationships with our suppliers while delivering premium tea products to our customers. Our goal is to provide authentic Sri Lankan tea to markets with consistency and care.</p>
    </div>

                
                
            </div>
        </div>
    </div>
    <!-- About End -->

    <?php
    $content = ob_get_clean();
    include 'layout.php';
    ?>