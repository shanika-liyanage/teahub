<?php
//use common layout to display this file on layout
//this file is for create deductions for tea leaves purchase

ob_start();

//include init file for functions and configurations
include '../../init.php';

//database connection
$conn = dbConnect();

//check form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    extract($_POST);

    //remove extra spaces
    $date = trim($date);
    $deduction_type = trim($deduction_type);
    $amount = trim($amount);
    $status = trim($status);

    //validation error array
    $error = [];

    //validation
    if (empty($date)) {
        $error['date'] = "Date is required";
    }

    if (empty($deduction_type)) {
        $error['deduction_type'] = "Deduction type is required";
    }

    if (empty($amount)) {
        $error['amount'] = "Amount is required";
    } elseif (!is_numeric($amount)) {
        $error['amount'] = "Amount must be numeric";
    }

    if (empty($status)) {
        $error['status'] = "Status is required";
    }

    //if no validation errors
    if (empty($error)) {

        try {

            $sql = "INSERT INTO tea_leaves_purchase_deductions
                    (date, deduction_type, amount, status)
                    VALUES
                    (:date, :deduction_type, :amount, :status)";

            $stmt = $conn->prepare($sql);

            //bind values
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':deduction_type', $deduction_type);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':status', $status);

            $stmt->execute();

            //redirect after success
            header("Location: deductions_list.php");

        } catch (PDOException $e) {

            echo "Error : " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">

    <div class="col-md-6">

        <div class="card card-danger mt-4">

            <div class="card-header">
                <h3 class="card-title">
                    Add Tea Leaves Purchase Deduction
                </h3>
            </div>

            <form method="POST">

                <div class="card-body">

                    <!-- Date -->
                    <div class="mb-3">

                        <label class="form-label">Date</label>

                        <input type="date"
                               name="date"
                               class="form-control"
                               value="<?= @$date ?>">

                        <div class="text-danger">
                            <?= @$error['date'] ?>
                        </div>

                    </div>

                    <!-- Deduction Type -->
                    <div class="mb-3">

                        <label class="form-label">
                            Deduction Type
                        </label>

                        <select name="deduction_type"
                                class="form-control">

                            <option value="">
                                -- Select Deduction Type --
                            </option>

                            <option value="Bag"
                                <?= (@$deduction_type == 'Bag') ? 'selected' : '' ?>>
                                Bag
                            </option>

                            <option value="Box"
                                <?= (@$deduction_type == 'Box') ? 'selected' : '' ?>>
                                Box
                            </option>

                            <option value="Other"
                                <?= (@$deduction_type == 'Other') ? 'selected' : '' ?>>
                                Other
                            </option>

                        </select>

                        <div class="text-danger">
                            <?= @$error['deduction_type'] ?>
                        </div>

                    </div>

                    <!-- Amount -->
                    <div class="mb-3">

                        <label class="form-label">Amount</label>

                        <input type="text"
                               name="amount"
                               class="form-control"
                               placeholder="Enter Amount"
                               value="<?= @$amount ?>">

                        <div class="text-danger">
                            <?= @$error['amount'] ?>
                        </div>

                    </div>

                    <!-- Status -->
                    <div class="mb-3">

                        <label class="form-label">Status</label>

                        <select name="status"
                                class="form-control">

                            <option value="">
                                -- Select Status --
                            </option>

                            <option value="Active"
                                <?= (@$status == 'Active') ? 'selected' : '' ?>>
                                Active
                            </option>

                            <option value="Inactive"
                                <?= (@$status == 'Inactive') ? 'selected' : '' ?>>
                                Inactive
                            </option>

                        </select>

                        <div class="text-danger">
                            <?= @$error['status'] ?>
                        </div>

                    </div>

                </div>

                <div class="card-footer text-center">

                    <button type="submit"
                            class="btn btn-danger">

                        Save Deduction

                    </button>

                    <a href="index.php"
                       class="btn btn-secondary">

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>