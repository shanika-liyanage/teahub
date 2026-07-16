<?php
ob_start();
include '../../init.php';

$conn = dbConnect();
$row = null;

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <div class="card p-4">

        <h3 class="mb-4 text-center">Supplier Details</h3>

        <?php if ($row): ?>
            <div class="row">
                <!-- LEFT SIDE (IMAGES) -->
                <div class="col-md-5 text-center">
                    <div class="mb-3">
                        <p><strong>ID View</strong></p>

                        <a href="<?= WEB_URL ?>assets/img/uploads/<?= $row['picture'] ?>" target="_blank">
                            <img src="<?= WEB_URL ?>assets/img/uploads/<?= $row['picture'] ?>"
                                class="img-fluid mb-3"
                                style="border:1px solid #000; height:300px ; width:300px; ">
                        </a>
                    </div>
                    <div class="mb-3">
                        <p><strong>Bank Photo</strong></p>
                        <img src="<?= WEB_URL ?>assets/img/uploads/<?= $row['book'] ?>" class="img-fluid" style="border:1px solid #000; height:300px; width:300px; ">

                    </div>
                </div>

                <!-- RIGHT SIDE (DETAILS) -->
                <div class="col-md-7">
                    <div class="mb-3">
                        <label>ID</label>
                        <input type="text" class="form-control" value="<?= $row['id'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" class="form-control" value="<?= $row['title'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>First Name</label>
                        <input type="text" class="form-control" value="<?= $row['first_name'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Last Name</label>
                        <input type="text" class="form-control" value="<?= $row['last_name'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Address Line 1</label>
                        <input type="text" class="form-control" value="<?= $row['address1'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Address Line 2</label>
                        <input type="text" class="form-control" value="<?= $row['address2'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Area</label>
                        <input type="text" class="form-control" value="<?= $row['area'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="text" class="form-control" value="<?= $row['email'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Mobile</label>
                        <input type="text" class="form-control" value="<?= $row['mobile'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Bank Account Number</label>
                        <input type="text" class="form-control" value="<?= $row['number'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Bank Name</label>
                        <input type="text" class="form-control" value="<?= $row['bank'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Bank Branch</label>
                        <input type="text" class="form-control" value="<?= $row['branch'] ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <form action="verify.php" method="POST">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <select name="status" class="form-control">
                                <option value="pending" <?= ($row['verify'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="verified" <?= ($row['verify'] == 'verified') ? 'selected' : '' ?>>Verified</option>
                                <option value="rejected" <?= ($row['verify'] == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <div class="mt-3">


                                <a href="index.php" class="btn btn-secondary ms-2">
                                    Back
                                </a>
                                <button type="submit" name="action" value="verify" class="btn btn-success">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        <?php else: ?>
            <p class="text-danger">No supplier found</p>
        <?php endif; ?>

    </div>
</div>













<?php
$content = ob_get_clean();
include '../layout.php';
?>