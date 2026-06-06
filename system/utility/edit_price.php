<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

// Check ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = $_POST['id'];

    // Load Existing Data
    $sql = "SELECT * FROM tea_leaves_purchase_price_list WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $price = $stmt->fetch(PDO::FETCH_ASSOC);

    // If Update Button Clicked
    if (isset($_POST['update'])) {

        $date = trim($_POST['date']);
        $amount = trim($_POST['amount']);
        $status = trim($_POST['status']);

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

        // Update Data
        if (empty($error)) {

            try {

                $sql = "UPDATE tea_leaves_purchase_price_list
                        SET date = :date,
                            amount = :amount,
                            status = :status
                        WHERE id = :id";

                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':date', $date);
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);

                $stmt->execute();

                header("Location: price_list.php");

            } catch (PDOException $e) {

                echo "Error : " . $e->getMessage();
            }
        }

    } else {

        // Default Values
        $date = $price['date'];
        $amount = $price['amount'];
        $status = $price['status'];
    }

} else {

    header("Location: price_list.php");
}
?>

<div class="row justify-content-center">

    <div class="col-md-6">

        <div class="card card-primary mt-4">

            <div class="card-header">

                <h3 class="card-title">
                    Edit Tea Leaves Purchase Price
                </h3>

            </div>

            <form method="POST">

                <input type="hidden"
                       name="id"
                       value="<?= $id ?>">

                <div class="card-body">

                    <!-- Date -->
                    <div class="mb-3">

                        <label class="form-label">
                            Date
                        </label>

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

                        <label class="form-label">
                            Amount
                        </label>

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

                        <label class="form-label">
                            Status
                        </label>

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
                            name="update"
                            class="btn btn-primary">

                        <i class="fas fa-save"></i> Update Price

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