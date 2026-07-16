<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

/*
|--------------------------------------------------------------------------
| DEMO DATA
|--------------------------------------------------------------------------
| Replace with your actual supplier id from session
*/
$user_id = $_SESSION['user_id'];
$supplier_id = $_SESSION['supplier_id'];
$stmt = $conn->prepare("
    SELECT *
    FROM tea_collection
    WHERE supplier_id =:supplier_id");
$stmt->execute([
    ':supplier_id' => $supplier_id
]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

$collection = $stmt->rowCount();




/*
|--------------------------------------------------------------------------
| FERTILIZER DETAILS
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT *
    FROM fertilizer_items
    WHERE status='ACTIVE'
");
$stmt->execute();
$fertilizers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| SAVE REQUEST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    extract($_POST);


    $errors = [];
    if (empty($fertilizer_id)) {
        $errors['fertilizer_id'] = "Please select a fertilizer";
    }
    if (empty($qty_requested)) {
        $errors['qty_requested'] = "Please enter the quantity requested";
    }
    if (empty($required_date)) {
        $errors['required_date'] = "Please enter the required date";
    }
    if(!empty($required_date) && $required_date < date('Y-m-d')) {
        $errors['required_date'] = "Required date cannot be in the past";
    }

    /*----------------------------------------------------
| FERTILIZER ENTITLEMENT VALIDATION
----------------------------------------------------*/

    // Total tea leaves supplied
    $stmt = $conn->prepare("
    SELECT SUM(gross_weight)
    FROM tea_collection
    WHERE supplier_id = :supplier_id");
    $stmt->execute([
        ':supplier_id' => $supplier_id
    ]);

    $total_leaf_qty = $stmt->fetchColumn();


    // Entitlement rate for selected fertilizer
    $stmt = $conn->prepare("
    SELECT rate_per_kg
    FROM fertilizer_entitlement
    WHERE fertilizer_id = :fertilizer_id
    LIMIT 1
");

    $stmt->execute([
        ':fertilizer_id' => $fertilizer_id
    ]);

    $rate_per_kg = $stmt->fetchColumn();

    if (!$rate_per_kg) {
        $rate_per_kg = 0;
    }


    // Maximum fertilizer entitlement
    $eligible_qty = $total_leaf_qty * $rate_per_kg;


    // Already issued fertilizer quantity
    $stmt = $conn->prepare("
    SELECT SUM(fi.Issued_Quantity)
    FROM fertilizer_issue fi    
    WHERE fi.supplier_id = :supplier_id
      AND fi.fertilizer_id = :fertilizer_id
");

    $stmt->execute([
        ':supplier_id' => $supplier_id,
        ':fertilizer_id' => $fertilizer_id
    ]);

    $issued_qty = $stmt->fetchColumn();
    $issued_qty = $issued_qty ?: 0;
   


    // Remaining entitlement
    $available_qty = $eligible_qty - $issued_qty;
    
  
   



    // Validation
    if ($qty_requested > $available_qty) {

        $errors['qty_requested'] =
            "Maximum available quantity is "
            . number_format(max($available_qty, 0), 2)
            . " Kg only.";
    }
  

    if (empty($errors)) {

        $stmt = $conn->prepare("
        INSERT INTO fertilizer_requests
        (
            supplier_id,
            fertilizer_id,
            qty_requested,
            required_date,
            remarks,
            status,
            request_date
        )
        VALUES
        (
            :supplier_id,
            :fertilizer_id,
            :qty_requested,
            :required_date,            
            :remarks,
            'PENDING',
            NOW()
        )
    ");

        $stmt->execute([
            ':supplier_id' => $supplier_id,
            ':fertilizer_id' => $fertilizer_id,
            ':qty_requested' => $qty_requested,
            ':required_date' => $required_date,
            ':remarks' => $remarks
        ]);
        

        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
exit;
    }
}

/*
|--------------------------------------------------------------------------
| REQUEST HISTORY
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT r.*,
           f.name
    FROM fertilizer_requests r
    LEFT JOIN fertilizer_items f
           ON f.id=r.fertilizer_id
    WHERE r.supplier_id=:supplier_id
    ORDER BY r.id DESC
");
$stmt->execute([
    ':supplier_id' => $supplier_id
]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">

    <h2 class="mb-4 text-center">Fertilizer Request</h2>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success">
            Fertilizer request submitted successfully.
        </div>
    <?php } ?>

    <!-- Fertilizer Details -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            Available Fertilizers
        </div>

        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Fertilizer Name</th>
                        <th>Description</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($fertilizers as $row) { ?>
                        <tr>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['discription'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
    <?php
    if ($collection > 0) {


    ?>

        <!-- Request Form -->
        <div class="card mb-4">

            <div class="card-header bg-success text-white">
                Request Fertilizer
            </div>

            <div class="card-body">

                <form method="POST" novalidate>

                    <div class="mb-3">
                        <label>Fertilizer</label>

                        <select name="fertilizer_id" class="form-control" required>
                            <option value="">Select Fertilizer</option>


                            <?php foreach ($fertilizers as $row) { ?>
                                <option value="<?= $row['id'] ?>" <?=  isset($fertilizer_id) && $fertilizer_id==$row['id']?'selected':'' ?>>
                                    <?= $row['name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                        <span class="text-danger small">
                            <?= @$errors['fertilizer_id']  ?>
                    </div>

                    <div class="mb-3">
                        <label>Required Quantity (Kg)</label>

                        <input type="number"
                            name="qty_requested"
                            class="form-control"
                            required value="<?= @$qty_requested ?>">
                        <span class="text-danger small">
                            <?= @$errors['qty_requested']  ?>
                    </div>

                    <div class="mb-3">
                        <label>When Do You Need It?</label>

                        <input type="date"
                            name="required_date"
                            class="form-control" value="<?= @$required_date ?>" required>
                        <span class="text-danger small">
                            <?= @$errors['required_date']  ?>
                    </div>

                    <div class="mb-3">
                        <label>Remarks</label>

                        <textarea name="remarks"
                            class="form-control"><?= @$remarks ?></textarea>
                    </div>

                    <button class="btn btn-success">
                        Submit Request
                    </button>

                </form>

            </div>
        </div>

        <!-- History -->
        <div class="card">

            <div class="card-header bg-dark text-white">
                My Fertilizer Request History
            </div>

            <div class="card-body">

                <table class="table table-striped">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Fertilizer</th>
                            <th>Quantity</th>
                            <th>Required Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($history as $row) { ?>

                            <tr>
                                <td><?= $row['request_date'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['qty_requested'] ?></td>
                                <td><?= $row['required_date'] ?></td>
                                <td>
                                    <?php if ($row['status'] == 'PENDING') { ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php } elseif ($row['status'] == 'APPROVED') { ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php } elseif ($row['status'] == 'ISSUED') { ?>
                                        <span class="badge bg-primary">Issued</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php } ?>
                                </td>
                                <td><?= $row['remarks'] ?></td>
                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>
    <?php } else { ?>
        <div class="alert alert-warning mt-4">
            You cannot request fertilizer as you have no tea collection records.
        </div>
    <?php } ?>
</div>

<?php
$content = ob_get_clean();
include '../layout-dashboard.php';
?>