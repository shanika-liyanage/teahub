<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM suppliers WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
    <div class="card shadow">


    <div class="card-header bg-success text-white">
        <h3 class="text-center mb-0">My Profile</h3>
    </div>

    <div class="card-body">

        <?php if ($row): ?>

            <div class="row">

                <!-- LEFT SIDE -->
                <div class="col-md-4 text-center">

                    <h5 class="mb-3">Profile Picture</h5>

                    <img src="<?= WEB_URL ?>assets/img/uploads/<?= $row['picture'] ?>"
                         class="img-fluid rounded border mb-4"
                         style="height:250px;width:250px;object-fit:cover;">

                    <h5 class="mb-3">Bank Book</h5>

                    <img src="<?= WEB_URL ?>assets/img/uploads/<?= $row['book'] ?>"
                         class="img-fluid rounded border">

                </div>

                <!-- RIGHT SIDE -->
                <div class="col-md-8">

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <label>Title</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $row['title'] ?>"
                                   readonly>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label>First Name</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $row['first_name'] ?>"
                                   readonly>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label>Last Name</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $row['last_name'] ?>"
                                   readonly>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $row['email'] ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>NIC</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $row['nic'] ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>Mobile Number</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $row['mobile'] ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>Address Line 1</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $row['address1'] ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>Address Line 2</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $row['address2'] ?>"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>Area</label>
                        <input type="text"
                               class="form-control"
                               value="<?= $row['area'] ?>"
                               readonly>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Bank Name</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $row['bank'] ?>"
                                   readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Account Number</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $row['number'] ?>"
                                   readonly>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Verification Status</label>

                            <?php if ($row['verify'] == 'verified') { ?>
                                <div class="badge bg-success p-2">
                                    VERIFIED
                                </div>
                            <?php } elseif ($row['verify'] == 'rejected') { ?>
                                <div class="badge bg-danger p-2">
                                    REJECTED
                                </div>
                            <?php } else { ?>
                                <div class="badge bg-warning text-dark p-2">
                                    PENDING
                                </div>
                            <?php } ?>

                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>

                            <?php if ($row['status'] == 'active') { ?>
                                <div class="badge bg-success p-2">
                                    ACTIVE
                                </div>
                            <?php } else { ?>
                                <div class="badge bg-danger p-2">
                                    INACTIVE
                                </div>
                            <?php } ?>

                        </div>

                    </div>

                    <a href="edit-profile.php"
                       class="btn btn-success">
                        Edit Profile
                    </a>

                </div>

            </div>

        <?php else: ?>

            <div class="alert alert-danger">
                Supplier profile not found.
            </div>

        <?php endif; ?>

    </div>

</div>


</div>
<?php
$content = ob_get_clean();
include '../layout-dashboard.php';
?>