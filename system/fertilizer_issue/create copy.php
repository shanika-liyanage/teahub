<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

/* -----------------------------
   LOAD TEA SUPPLIERS
-----------------------------*/
$sql = "SELECT *
        FROM suppliers
        ";

$stmt = $conn->prepare($sql);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   LOAD FERTILIZER ITEMS
-----------------------------*/
$sql = "SELECT *
        FROM fertilizer_items
        WHERE status='Active'
        ORDER BY name";

$stmt = $conn->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   SAVE
-----------------------------*/
if($_SERVER['REQUEST_METHOD']=='POST'){

    extract($_POST);

    $tea_qty = (float)$tea_qty;
    $rate_per_kg = (float)$rate_per_kg;
    $unit_price = (float)$unit_price;

    // fertilizer quantity in KG
    $fertilizer_qty = ($tea_qty * $rate_per_kg) / 1000;

    $amount = $fertilizer_qty * $unit_price;

    try{

        $conn->beginTransaction();

        /* -----------------------------
           INSERT SUPPLY HEADER
        -----------------------------*/
        $sql = "INSERT INTO fertilizer_supplies
                (
                    supplier_id,
                    issue_date,
                    total_amount,
                    remarks,
                    created_at
                )
                VALUES
                (
                    :supplier_id,
                    :issue_date,
                    :total_amount,
                    :remarks,
                    NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':supplier_id'=>$supplier_id,
            ':issue_date'=>$issue_date,
            ':total_amount'=>$amount,
            ':remarks'=>$remarks
        ]);

        $supply_id = $conn->lastInsertId();

        /* -----------------------------
           INSERT ITEM
        -----------------------------*/
        $sql = "INSERT INTO fertilizer_supply_items
                (
                    supply_id,
                    fertilizer_item_id,
                    qty,
                    unit_price,
                    amount
                )
                VALUES
                (
                    :supply_id,
                    :fertilizer_item_id,
                    :qty,
                    :unit_price,
                    :amount
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':supply_id'=>$supply_id,
            ':fertilizer_item_id'=>$fertilizer_item_id,
            ':qty'=>$fertilizer_qty,
            ':unit_price'=>$unit_price,
            ':amount'=>$amount
        ]);

        $conn->commit();

        header("Location:index.php");
        exit;

    }catch(Exception $e){

        $conn->rollBack();
        echo $e->getMessage();
    }
}
?>

<h3>Issue Fertilizer To Tea Supplier</h3>

<form method="POST">

    <div class="mb-2">
        <label>Tea Supplier</label>
        <select name="supplier_id" class="form-control" required>
            <option value="">Select Supplier</option>

            <?php foreach($suppliers as $s){ ?>
                <option value="<?= $s['id'] ?>">
                     <?= ucfirst($s['title']) ?>. <?= $s['first_name'] ?> <?= $s['last_name'] ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-2">
        <label>Issue Date</label>
        <input type="date"
               name="issue_date"
               class="form-control"
               value="<?= date('Y-m-d') ?>"
               required>
    </div>

    <div class="mb-2">
        <label>Fertilizer Item</label>
        <select name="fertilizer_item_id"
                class="form-control"
                required>

            <option value="">Select Item</option>

            <?php foreach($items as $item){ ?>
                <option value="<?= $item['id'] ?>">
                    <?= $item['name'] ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-2">
        <label>Tea Supplied (KG)</label>
        <input type="number"
               step="0.01"
               id="tea_qty"
               name="tea_qty"
               class="form-control"
               required>
    </div>

    <div class="mb-2">
        <label>Fertilizer Rate (g per Tea KG)</label>
        <input type="number"
               step="0.01"
               id="rate_per_kg"
               name="rate_per_kg"
               value="120"
               class="form-control"
               required>
    </div>

    <div class="mb-2">
        <label>Unit Price</label>
        <input type="number"
               step="0.01"
               id="unit_price"
               name="unit_price"
               class="form-control"
               required>
    </div>

    <div class="mb-2">
        <label>Fertilizer Qty (KG)</label>
        <input type="text"
               id="fertilizer_qty"
               class="form-control"
               readonly>
    </div>

    <div class="mb-2">
        <label>Amount</label>
        <input type="text"
               id="amount"
               class="form-control"
               readonly>
    </div>

    <div class="mb-2">
        <label>Remarks</label>
        <textarea name="remarks"
                  class="form-control"></textarea>
    </div>

    <button type="submit" class="btn btn-success">
        Save
    </button>

</form>

<script>
function calculate(){

    let teaQty = parseFloat(document.getElementById('tea_qty').value) || 0;
    let rate = parseFloat(document.getElementById('rate_per_kg').value) || 0;
    let unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;

    let fertilizerQty = (teaQty * rate) / 1000;
    let amount = fertilizerQty * unitPrice;

    document.getElementById('fertilizer_qty').value =
        fertilizerQty.toFixed(2);

    document.getElementById('amount').value =
        amount.toFixed(2);
}

document.getElementById('tea_qty').addEventListener('keyup',calculate);
document.getElementById('rate_per_kg').addEventListener('keyup',calculate);
document.getElementById('unit_price').addEventListener('keyup',calculate);
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>