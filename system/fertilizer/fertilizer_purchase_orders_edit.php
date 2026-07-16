<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

$id = $_GET['id'] ?? $_POST['id'] ;

/* -----------------------------
   LOAD SUPPLIERS
------------------------------*/
$stmt = $conn->prepare("SELECT * FROM fertilizer_suppliers");
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   LOAD PRODUCTS
------------------------------*/
$stmt = $conn->prepare("SELECT * FROM fertilizer_items");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   LOAD PURCHASE ORDER
------------------------------*/
$stmt = $conn->prepare("
    SELECT *
    FROM fertilizer_purchase_orders
    WHERE id = ?
");
$stmt->execute([$id]);
$po = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$po) {
    die("Purchase Order Not Found");
}

/* -----------------------------
   LOAD PO ITEMS
------------------------------*/
$stmt = $conn->prepare("
    SELECT *
    FROM fertilizer_purchase_order_items
    WHERE purchase_order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   UPDATE
------------------------------*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    extract($_POST);

    try {

        $conn->beginTransaction();

        /* Update Header */
        $sql = "
            UPDATE fertilizer_purchase_orders
            SET supplier_id = :supplier_id,
                order_date = :order_date
            WHERE id = :id
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':supplier_id' => $supplier_id,
            ':order_date' => $order_date,
            ':id' => $id
        ]);

        /* Remove Old Items */
        $stmt = $conn->prepare("
            DELETE FROM fertilizer_purchase_order_items
            WHERE purchase_order_id = ?
        ");
        $stmt->execute([$id]);

        /* Insert New Items */
        $sqlItem = "
            INSERT INTO fertilizer_purchase_order_items
            (
                purchase_order_id,
                fertilizer_item_id,
                unit,
                qty,
                unit_price
            )
            VALUES
            (
                :purchase_order_id,
                :fertilizer_item_id,
                :unit,
                :qty,
                :unit_price
            )
        ";

        $stmtItem = $conn->prepare($sqlItem);

        for ($i = 0; $i < count($product_id); $i++) {

            if (empty($product_id[$i])) continue;

            $stmtItem->execute([
                ':purchase_order_id' => $id,
                ':fertilizer_item_id' => $product_id[$i],
                ':unit' => $unit[$i],
                ':qty' => $quantity[$i],
                ':unit_price' => $price[$i]
            ]);
        }

        $conn->commit();

        header("Location:fertilizer_purchase_orders.php");
        exit;

    } catch (Exception $e) {

        $conn->rollBack();
        echo $e->getMessage();
    }
}
?>

<h3>Edit Purchase Order</h3>

<form method="post" >
    <input type="text" name="id" value="<?= $id ?>">

    <div class="mb-3">
        <label>Supplier</label>
        <select name="supplier_id" class="form-control">

            <?php foreach($suppliers as $s){ ?>

                <option
                    value="<?= $s['id'] ?>"
                    <?= ($po['supplier_id']==$s['id'])?'selected':'' ?>
                >
                    <?= $s['company_name'] ?>
                </option>

            <?php } ?>

        </select>
    </div>

    <div class="mb-3">
        <label>Date</label>
        <input
            type="date"
            name="order_date"
            class="form-control"
            value="<?= $po['order_date'] ?>"
        >
    </div>

    <table class="table table-bordered" id="items">
        <thead>
        <tr>
            <th>Product</th>
            <th>Unit</th>
            <th>Qty</th>
            <th>Price</th>
            <th></th>
        </tr>
        </thead>

        <tbody>

        <?php foreach($items as $item){ ?>

            <tr>

                <td>
                    <select name="product_id[]" class="form-control">

                        <?php foreach($products as $p){ ?>

                            <option
                                value="<?= $p['id'] ?>"
                                <?= ($item['fertilizer_item_id']==$p['id'])?'selected':'' ?>
                            >
                                <?= $p['name'] ?>
                            </option>

                        <?php } ?>

                    </select>
                </td>

                <td>
                    <select name="unit[]" class="form-control">
                        <option value="50kg"
                            <?= ($item['unit']=='50kg')?'selected':'' ?>>
                            50Kg
                        </option>

                        <option value="25kg"
                            <?= ($item['unit']=='25kg')?'selected':'' ?>>
                            25Kg
                        </option>
                    </select>
                </td>

                <td>
                    <input
                        type="number"
                        name="quantity[]"
                        class="form-control"
                        value="<?= $item['qty'] ?>"
                    >
                </td>

                <td>
                    <input
                        type="text"
                        name="price[]"
                        class="form-control"
                        value="<?= $item['unit_price'] ?>"
                    >
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="removeRow(this)">
                        X
                    </button>
                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

    <button type="button" onclick="addRow()" class="btn btn-secondary">
        Add Row
    </button>

    <button type="submit" class="btn btn-success">
        Update PO
    </button>

</form>

<script>
function addRow() {

    let row = document.querySelector("#items tbody tr").cloneNode(true);

    row.querySelectorAll("input").forEach(function(input){
        input.value = "";
    });

    document.querySelector("#items tbody").appendChild(row);
}

function removeRow(btn) {

    let tbody = document.querySelector("#items tbody");

    if (tbody.rows.length > 1) {
        btn.closest("tr").remove();
    }
}
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>