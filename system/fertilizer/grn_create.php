<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
// Get only PENDING PO
$po = $conn->query("SELECT * FROM fertilizer_purchase_orders WHERE status='PENDING'")
    ->fetchAll(PDO::FETCH_ASSOC);

?>

<h3>Create GRN</h3>


<form method="GET">
    <label>Select Purchase Order</label>
    <select name="po_id" class="form-control" onchange="this.form.submit()">
        <option value="">Select PO</option>
        <?php foreach ($po as $p): ?>
            <option value="<?= $p['id'] ?>"
                <?= (isset($_GET['po_id']) && $_GET['po_id'] == $p['id']) ? 'selected' : '' ?>>
                PO-<?= $p['id'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>


<br>


<?php
if (isset($_GET['po_id'])):


    $po_id = $_GET['po_id'];


    // Get PO Items
    $sql = "SELECT pi.*, pr.name
        FROM fertilizer_purchase_order_items pi
        JOIN fertilizer_items pr ON pi.fertilizer_item_id = pr.id
        WHERE pi.purchase_order_id = :id";


    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $po_id);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


    <form method="POST" action="grn_save.php">


        <input type="hidden" name="purchase_id" value="<?= $po_id ?>">


        <label>Date</label>
        <input type="date" name="received_date" class="form-control mb-2" required>


       <table class="table table-bordered">
    <thead>
        <tr>
            <th>Product</th>
            
            <th>Ordered Qty</th>
            <th>Receive Qty</th>
            <th>Unit Price (Rs)</th>
            <th>Total (Rs)</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($items as $it): ?>
            <tr>

                <td>
                    <?= $it['name'] ?>
                    <input type="hidden" name="product_id[]" value="<?= $it['fertilizer_item_id'] ?>">
                </td>

                

                <td>
                    <?= $it['qty'] ?>
                    <input type="hidden" name="ordered_qty[]" value="<?= $it['qty'] ?>">
                </td>

                <td>
                    <input type="number"
                           name="quantity[]"
                           class="form-control qty"
                           value="<?= $it['qty'] ?>"
                           onkeyup="calculateTotal(this)">
                </td>

                <td>
                    <input type="number"
                           step="0.01"
                           name="cost[]"
                           class="form-control price"
                           value="<?= $it['unit_price'] ?>"
                           onkeyup="calculateTotal(this)">
                </td>

                <td>
                    <input type="text"
                           class="form-control total"
                           value="<?= number_format($it['qty'] * $it['unit_price'],2,'.','') ?>"
                           readonly>
                </td>

            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="4" style="text-align:right;">Grand Total (Rs)</th>
        <th>
            <input type="text"
                   id="grand_total"
                   name="grand_total"
                   class="form-control"
                   value="0.00"
                   readonly>
        </th>
    </tr>
</tfoot>

</table>


        <button class="btn btn-success">Save GRN</button>


    </form>


<?php endif; ?>
<script>
function calculateTotal(ele){

    let row = ele.closest("tr");

    let qty = parseFloat(row.querySelector(".qty").value) || 0;
    let price = parseFloat(row.querySelector(".price").value) || 0;

    let total = qty * price;

    row.querySelector(".total").value = total.toFixed(2);

    calculateGrandTotal();
}

function calculateGrandTotal(){

    let grand = 0;

    document.querySelectorAll(".total").forEach(function(item){

        grand += parseFloat(item.value) || 0;

    });

    document.getElementById("grand_total").value = grand.toFixed(2);

}

// Page load වෙද්දීත් Grand Total හදනවා
window.onload = function () {
    calculateGrandTotal();
};
</script>






<?php
$content = ob_get_clean();
include '../layout.php';
?>