<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

if (!isset($_GET['id'])) {
    header("Location: request.php");
    exit;
}

$request_id = $_GET['id'];

/*
|--------------------------------------------------------------------------
| Get Request Details
|--------------------------------------------------------------------------
*/

$sql = "SELECT fr.*,
        CONCAT(s.first_name,' ',s.last_name) AS supplier_name,
        fi.name AS fertilizer_name,
        fi.unit_price
        FROM fertilizer_requests fr
        LEFT JOIN suppliers s ON s.id = fr.supplier_id
        LEFT JOIN fertilizer_items fi ON fi.id = fr.fertilizer_id
        WHERE fr.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $request_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Request Not Found");
}

/*
|--------------------------------------------------------------------------
| Save Issue
|--------------------------------------------------------------------------
*/

if (isset($_POST['issue'])) {
    print_r($_POST);
    extract($_POST); 
    $errors=[];
    if(empty($Issued_Quantity)){
$errors['Issued_Quantity']="Issued Quantity should not be blank";}

    if(!empty($Issued_Quantity)){
        

    // Total tea leaves supplied
    $stmt = $conn->prepare("SELECT SUM(gross_weight)
    FROM tea_collection
    WHERE supplier_id = :supplier_id");
    $stmt->execute([
        ':supplier_id' => $supplier_id
    ]);

    $total_leaf_qty = $stmt->fetchColumn();


    // Entitlement rate for selected fertilizer
    $stmt = $conn->prepare("SELECT rate_per_kg
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
    $stmt = $conn->prepare("SELECT SUM(fi.Issued_Quantity)
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
    if ($Issued_Quantity > $available_qty) {

        $errors['Issued_Quantity'] =
            "Maximum available quantity is "
            . number_format(max($available_qty, 0), 2)
            . " Kg only.";
    }
 
    }
if(empty($errors)){
    try {

        $conn->beginTransaction();

        $remarks = $remarks;

 

        /*
        |--------------------------------------------------------------------------
        | Insert Issue
        |--------------------------------------------------------------------------
        */

        $sql = "INSERT INTO fertilizer_issue
                (supplier_id,issue_date,fertilizer_id,qty_requested,Issued_Quantity,unit_price,total_amount,remarks,created_at)
                VALUES
                (:supplier_id,NOW(),:fertilizer_id,:qty_requested,:Issued_Quantity,:unit_price,:total_amount,:remarks,NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':supplier_id' => $supplier_id,
            ':fertilizer_id'=> $fertilizer_id,
            ':qty_requested' => $qty_requested,
            ':Issued_Quantity'=>$Issued_Quantity,
            ':unit_price'=>$unit_price,
            ':total_amount' => $total_amount,
            ':remarks' => $remarks
        ]);

        $issue_id = $conn->lastInsertId();

               $stmtStockMovement = $conn->prepare("INSERT INTO fertilizer_stock_movements
               (product_id, type, quantity, reference)
                VALUES (:product_id, 'OUT', :qty, :ref)");

 $ref=$issue_id;
 $pid = $fertilizer_id;
 $qty_needed = $Issued_Quantity;
 
 // Get FIFO batches
 $stmtBatches = $conn->prepare("
 SELECT * FROM fertilizer_stock
 WHERE product_id = :pid AND remaining_qty > 0
 ORDER BY created_at ASC
 ");
 $stmtBatches->execute([':pid' => $pid]);
 $batches = $stmtBatches->fetchAll(PDO::FETCH_ASSOC);

 if (!$batches) {
 throw new Exception("No stock for product ID $pid");
 }

 foreach ($batches as $batch) {
 if ($qty_needed <= 0) break;
 $deduct = min($qty_needed, $batch['remaining_qty']);

 // Deduct from stock
 $stmtUpdate = $conn->prepare("
 UPDATE fertilizer_stock
 SET remaining_qty = remaining_qty - :deduct
 WHERE product_id = :pid AND batch_ref = :batch_ref
 ");
 $stmtUpdate->execute([
 ':deduct' => $deduct,
 ':pid' => $pid,
 ':batch_ref' => $batch['batch_ref']
 ]);
 echo $batch['batch_ref'];
 echo $pid;
 // Record movement
 $stmtStockMovement->execute([
 ':product_id' => $pid,
 ':qty' => $deduct,
 ':ref' => $ref]);
 $qty_needed -= $deduct;
 }
       

        /*
        |--------------------------------------------------------------------------
        | Update Request Status
        |--------------------------------------------------------------------------
        */

        $sql = "UPDATE fertilizer_requests
              SET status='ISSUED'
              WHERE id=:id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'id' => $request_id
        ]);

        $conn->commit();

        //header("Location: request.php?success=issued");
        //exit;
    } catch (Exception $e) {

        $conn->rollBack();
        echo $e->getMessage();
    }
    }
}

?>

<div class="container">

    <div class="card">

        <div class="card-header bg-success text-white">
            <h4>Issue Fertilizer</h4>
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th>Supplier</th>
                    <td><?= $row['supplier_name']; ?></td>
                </tr>

                <tr>
                    <th>Fertilizer</th>
                    <td><?= $row['fertilizer_name']; ?></td>
                </tr>

                <tr>
                    <th>Quantity</th>
                    <td><?= $row['qty_requested']; ?> Kg</td>
                </tr>
                 <tr>
                    <th>Unit Price</th>
                    <td> Rs. <?= $row['unit_price']; ?> </td>
                </tr>

                <tr>
                    <th>Required Date</th>
                    <td><?= $row['required_date']; ?></td>
                </tr>

            </table>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label>Issued Quantity (Kg) </label>
                    <input type="number"
                        step="0.01"
                        min="0"
                        name="Issued_Quantity"
                        id="Issued_Quantity"
                        class="form-control" value= "<?= $row['qty_requested']; ?>"
                        required>
                        <span class="text-danger"><?= @$errors['Issued_Quantity'] ?></span>
                </div>

                <div class="mb-3">
                    <label>Total Amount (Rs.)</label>
                    <input type="text"
                        id="total_amount"
                        name="total_amount"
                        class="form-control"
                        readonly>
                </div>


                <div class="mb-3">
                    <label>Remarks</label>
                    <textarea
                        name="remarks"
                        class="form-control"></textarea>
                </div>
                <input type="hidden" name ="supplier_id" value="<?=$row['supplier_id']?>">
                <input type="hidden" name ="fertilizer_id" value="<?=$row['fertilizer_id']?>">
                <input type="hidden" name ="unit_price" value="<?=$row['unit_price']?>">
               <input type="hidden" name ="qty_requested" value="<?=$row['qty_requested']?>">

    

                <button
                    type="submit"
                    name="issue"
                    class="btn btn-success">
                    Issue Fertilizer
                </button>

                <a href="request.php"
                    class="btn btn-secondary">
                    Back
                </a>

            </form>
            <script>
                const price = <?= $row['unit_price']; ?>;
                const qty = document.getElementById('Issued_Quantity');
                const total = document.getElementById('total_amount');

                function calculateTotal(){
                    let totalAmount = price * parseFloat(qty.value || 0);

                    total.value = totalAmount.toFixed(2);

                }
                calculateTotal();

                qty.addEventListener('input', calculateTotal );
            </script>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>