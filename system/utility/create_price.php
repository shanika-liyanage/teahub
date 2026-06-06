<?php
//use common layout  to display this file on layout  and this file is for create price for tea leaves purchase
ob_start();
//include init for this file have function and configuration
include '../../init.php';
//to call db connection function
$conn = dbConnect();
//request method check form submit or not
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    extract($_POST);
//to cut extra spaces
    $date = trim($date);
    $amount = trim($amount);
    $status = trim($status);
//make this variable to validate message store in array and display error message if validation fail
    $error = [];

    // Validation
    if (empty($date)) {
        $error['date'] = "Date is required";
    }

    if (empty($amount)) {
        $error['amount'] = "Amount is required";
    } elseif (!is_numeric($amount)) {
        $error['amount'] = "Amount must be numeric";
    }

    if (empty($status)) {
        $error['status'] = "Status is required";
    }

    // if abouve all are field check error variable is empty then insert data into database
    if (empty($error)) {
//extention handline method use on php to errors on db connection to manage error 
        try {

            $sql = "INSERT INTO tea_leaves_purchase_price_list(date, amount, status)
                    VALUES(:date, :amount, :status)";
//to store data as higt secure to prevent sql injection
            $stmt = $conn->prepare($sql);
//assign value place holders one by one
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':status', $status);

            $stmt->execute();
//after insert data successfully redirect to price list page
            header("Location: price_list.php");

        } catch (PDOException $e) {

            echo "Error : " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card card-success mt-4">

            <div class="card-header">
                <h3 class="card-title">Add Tea Leaves Purchase Price</h3>
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

                        <select name="status" class="form-control">

                            <option value="">-- Select Status --</option>

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

                    <button type="submit" class="btn btn-success">
                        Save Price
                    </button>

                    <a href="index.php" class="btn btn-secondary">
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