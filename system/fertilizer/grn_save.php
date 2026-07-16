<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

$purchase_id = $_POST['purchase_id'];
$date = $_POST['received_date'];



$product_ids = $_POST['product_id'];

$ordered_qtys = $_POST['ordered_qty'];
$quantities = $_POST['quantity'];
$costs = $_POST['cost'];




try {
    $conn->beginTransaction();


    // 1. Insert GRN
    $stmt = $conn->prepare("INSERT INTO fertilizer_grn (purchase_id, received_date) VALUES (:purchase_id, :date)");
    $stmt->execute([':purchase_id' => $purchase_id, ':date' => $date]);
    $grn_id = $conn->lastInsertId();


    // Prepare statements
    $stmtItem = $conn->prepare("INSERT INTO fertilizer_grn_items 
(grn_id, fertilizer_item_id, ordered_qty, received_qty, cost)
VALUES (:grn_id, :fertilizer_item_id,  :ordered_qty, :received_qty, :cost)");
    $stmtStockMovement = $conn->prepare("INSERT INTO fertilizer_stock_movements (product_id, type, quantity, reference) VALUES (:product_id, 'IN', :quantity, :ref)");
    $stmtStock = $conn->prepare("INSERT INTO fertilizer_stock (product_id, batch_ref, quantity,  cost,remaining_qty) VALUES (:product_id, :batch_ref, :quantity,  :cost, :remaining_qty)");


    for ($i = 0; $i < count($product_ids); $i++) {
        $pid = $product_ids[$i];

        $ordered_qty = $ordered_qtys[$i];


        $qty = $quantities[$i];
        $cost = $costs[$i];






        $batch_ref = "GRN-" . $grn_id;


        // 2. Insert GRN item
        $stmtItem->execute([
            ':grn_id' => $grn_id,
            ':fertilizer_item_id' => $pid,

            ':ordered_qty' => $ordered_qty,
            ':received_qty' => $qty,
            ':cost' => $cost
        ]);


        // 3. Insert stock movement
        $stmtStockMovement->execute([
            ':product_id' => $pid,
            ':quantity' => $qty,
            ':ref' => $batch_ref
        ]);


        $result = $stmtStock->execute([
            ':product_id' => $pid,
            ':batch_ref' => $batch_ref,
            ':quantity' => $qty,
            ':remaining_qty' => $qty,
            ':cost' => $cost
        ]);

        //update unit price in fertilizer_items table--------------------------

        $sql="SELECT product_id,((sum(remaining_qty*cost)/sum(remaining_qty))+((sum(remaining_qty*cost)/sum(remaining_qty))*0.1)) as selprice FROM `fertilizer_stock` WHERE product_id=:pid GROUP by product_id";
        $stmt=$conn->prepare($sql);
        $stmt->execute([':pid'=>$pid]);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        $selprice=$row['selprice'];

        $sql="UPDATE fertilizer_items SET unit_price=:selprice WHERE id=:pid";
        $stmt=$conn->prepare($sql);
        $stmt->execute([':selprice'=>$selprice,':pid'=>$pid]);
        //----------------------------------------------------------------------

        if (!$result) {
            echo "<pre>";
            print_r($stmtStock->errorInfo());
            exit;
        }

       
    }


    // 5. Update PO status
    $conn->prepare("UPDATE fertilizer_purchase_orders SET status='RECEIVED' WHERE id=:id")
        ->execute([':id' => $purchase_id]);


    $conn->commit();


    header("Location: grn_index.php");
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

?>




<?php
$content = ob_get_clean();
include '../layout.php';
?>