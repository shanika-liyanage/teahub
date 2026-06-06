<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   LOAD SUPPLIERS
------------------------------*/
$sql = "SELECT * FROM suppliers ORDER BY first_name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   FORM SUBMIT
------------------------------*/
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    extract($_POST);

    // Trim values
    $supplier_id = trim($supplier_id);
    $total_leaves_kg = trim($total_leaves_kg);
    $gross_amount = trim($gross_amount);
    $advance_deduction = trim($advance_deduction);
    $fertilizer_deduction = trim($fertilizer_deduction);
    $loan_deduction = trim($loan_deduction);
    $other_deduction = trim($other_deduction);
    $payment_date = trim($payment_date);

    /* -----------------------------
       VALIDATION
    ------------------------------*/

    if (empty($supplier_id)) {
        $error['supplier_id'] = "Supplier is required";
    }

    if (empty($total_leaves_kg)) {
        $error['total_leaves_kg'] = "Total leaves kg is required";
    }

    if (empty($gross_amount)) {
        $error['gross_amount'] = "Gross amount is required";
    }

    // Default deduction values
    $advance_deduction = !empty($advance_deduction) ? $advance_deduction : 0;
    $fertilizer_deduction = !empty($fertilizer_deduction) ? $fertilizer_deduction : 0;
    $loan_deduction = !empty($loan_deduction) ? $loan_deduction : 0;
    $other_deduction = !empty($other_deduction) ? $other_deduction : 0;

    /* -----------------------------
       NET PAYABLE CALCULATION
    ------------------------------*/

    $total_deductions =
        $advance_deduction +
        $fertilizer_deduction +
        $loan_deduction +
        $other_deduction;

    $net_payable = $gross_amount - $total_deductions;

    /* -----------------------------
       INSERT DATA
    ------------------------------*/

    if (empty($error)) {

        try {

            $payment_time = date("H:i:s");

            $sql = "INSERT INTO payments (
                        supplier_id,
                        total_leaves_kg,
                        gross_amount,
                        advance_deduction,
                        fertilizer_deduction,
                        loan_deduction,
                        other_deduction,
                        net_payable,
                        payment_date,
                        payment_time
                    ) VALUES (
                        :supplier_id,
                        :total_leaves_kg,
                        :gross_amount,
                        :advance_deduction,
                        :fertilizer_deduction,
                        :loan_deduction,
                        :other_deduction,
                        :net_payable,
                        :payment_date,
                        :payment_time
                    )";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':supplier_id', $supplier_id);
            $stmt->bindParam(':total_leaves_kg', $total_leaves_kg);
            $stmt->bindParam(':gross_amount', $gross_amount);
            $stmt->bindParam(':advance_deduction', $advance_deduction);
            $stmt->bindParam(':fertilizer_deduction', $fertilizer_deduction);
            $stmt->bindParam(':loan_deduction', $loan_deduction);
            $stmt->bindParam(':other_deduction', $other_deduction);
            $stmt->bindParam(':net_payable', $net_payable);
            $stmt->bindParam(':payment_date', $payment_date);
            $stmt->bindParam(':payment_time', $payment_time);

            $stmt->execute();

            header("Location: index.php");

        } catch (PDOException $e) {

            echo "Error : " . $e->getMessage();

        }

    }

}
?>

<h3>Create Payment</h3>

<form method="POST">

    <!-- Supplier -->
    <div class="mb-2">
        <label>Supplier</label>

        <select name="supplier_id" class="form-control">
            <option value="">Select Supplier</option>

            <?php foreach ($suppliers as $s): ?>

                <option value="<?= $s['id'] ?>">
                    <?= $s['first_name'] . ' ' . $s['last_name'] ?>
                </option>

            <?php endforeach; ?>

        </select>

        <small class="text-danger">
            <?= $error['supplier_id'] ?? '' ?>
        </small>
    </div>

    <!-- Total Leaves KG -->
    <div class="mb-2">
        <label>Total Leaves KG</label>

        <input type="number"
               step="0.01"
               name="total_leaves_kg"
               class="form-control">

        <small class="text-danger">
            <?= $error['total_leaves_kg'] ?? '' ?>
        </small>
    </div>

    <!-- Gross Amount -->
    <div class="mb-2">
        <label>Gross Amount</label>

        <input type="number"
               step="0.01"
               name="gross_amount"
               class="form-control">

        <small class="text-danger">
            <?= $error['gross_amount'] ?? '' ?>
        </small>
    </div>

    <!-- Advance Deduction -->
    <div class="mb-2">
        <label>Advance Deduction</label>

        <input type="number"
               step="0.01"
               name="advance_deduction"
               class="form-control"
               value="0">
    </div>

    <!-- Fertilizer Deduction -->
    <div class="mb-2">
        <label>Fertilizer Deduction</label>

        <input type="number"
               step="0.01"
               name="fertilizer_deduction"
               class="form-control"
               value="0">
    </div>

    <!-- Loan Deduction -->
    <div class="mb-2">
        <label>Loan Deduction</label>

        <input type="number"
               step="0.01"
               name="loan_deduction"
               class="form-control"
               value="0">
    </div>

    <!-- Other Deduction -->
    <div class="mb-2">
        <label>Other Deduction</label>

        <input type="number"
               step="0.01"
               name="other_deduction"
               class="form-control"
               value="0">
    </div>

    <!-- Payment Date -->
    <div class="mb-3">
        <label>Payment Date</label>

        <input type="date"
               name="payment_date"
               class="form-control"
               value="<?= date('Y-m-d') ?>">
    </div>

    <button type="submit" class="btn btn-success">
        Save Payment
    </button>

</form>

<?php
$content = ob_get_clean();
include '../layout.php';
?>