<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
?>
<div>
    <h1>Fertilizer Purchase Orders</h1>
    <div class="row">
        <div class="col">
            <a href="create.php" class="btn btn-primary mb-3">Create PO</a>
            <?php
            // Suppliers
            $sup = $conn->prepare("SELECT * FROM fertilizer_suppliers");
            $sup->execute();
            $suppliers = $sup->fetchAll(PDO::FETCH_ASSOC);

            // Products
            $prd = $conn->prepare("SELECT * FROM fertilizer_items");
            $prd->execute();
            $products = $prd->fetchAll(PDO::FETCH_ASSOC);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
               
    
                
                extract($_POST);
                $error = [];
                if (empty($supplier_id)) {
                    $error['supplier_id'] = "Supplier is required";
                }
                if (empty($order_date)) {
                    $error['order_date'] = "Date is required";
                }
                




                if (empty($error)) {
                    try {
                        $conn->beginTransaction();

                        // Insert Purchase Order
                        $sql = "INSERT INTO fertilizer_purchase_orders (supplier_id, order_date, status)
            VALUES (:supplier_id, :order_date, 'PENDING')";

                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':supplier_id', $supplier_id);
                        $stmt->bindParam(':order_date', $order_date);
                        $stmt->execute();

                        $purchase_id = $conn->lastInsertId();

                        // Insert Items
                        $sqlItem = "INSERT INTO fertilizer_purchase_order_items
                (purchase_order_id, fertilizer_item_id,  qty,unit_price)
                VALUES (:purchase_order_id, :fertilizer_item_id,  :qty, :unit_price)";

                        $stmtItem = $conn->prepare($sqlItem);
                        

                        for ($i = 0; $i < count($product_id); $i++) {

                            if (empty($product_id[$i])) continue;
                            $stmtItem->bindParam(':purchase_order_id', $purchase_id);
                            $stmtItem->bindParam(':fertilizer_item_id', $product_id[$i]);
                            
                            $stmtItem->bindParam(':qty', $quantity[$i]);
                            $stmtItem->bindParam(':unit_price', $price[$i]);
                            $stmtItem->execute();
                          
                        }

                        $conn->commit();

                        
                    } catch (Exception $e) {
                        $conn->rollBack();
                        echo "Error: " . $e->getMessage();
                    }
                }
            }


            ?>
            <h3>Create Purchase Order</h3>



            <form method="POST" novalidate>


                <div class="mb-3">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['company_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="text-danger"><?= @$error['supplier_id'] ?> </span>
                </div>


                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" name="order_date" class="form-control" required>
                    <span class="text-danger"><?= @$error['order_date'] ?> </span>
                </div>


                <h5>Items</h5>


                <table class="table" id="items">


                    <thead>
                        <tr>
                            <th>Product</th>
                           
                            <th>Qty(Kg)</th>
                            <th>Price Per (Kg)</th>
                            <th></th>
                        </tr>
                    </thead>


                    <tbody>
                        <tr>
                            <td>
                                <select name="product_id[]" class="form-control">
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            
                            <td><input type="number" name="quantity[]" class="form-control"></td>
                            <td><input type="text" name="price[]" class="form-control"></td>
                            <td><button type="button" onclick="removeRow(this)" class="btn btn-danger">X</button></td>
                        
                        </tr>
                    </tbody>


                </table>


                <button type="button" onclick="addRow()" class="btn btn-secondary">Add Row</button>
                <br><br>


                <button class="btn btn-success">Save PO</button>



            </form>




        </div>


    </div>
    <div class="row">
        <div class="col">
            <?php
            $sql = "SELECT po.*, s.company_name as supplier
        FROM fertilizer_purchase_orders po
        JOIN fertilizer_suppliers s ON po.supplier_id = s.id
        ORDER BY po.id DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <h3>Purchase Orders</h3>





            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>


                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['supplier'] ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><a href="fertilizer_purchase_orders_view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a></td>
                        <td><a href="fertilizer_purchase_orders_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a></td>
                        <td><a href="fertilizer_purchase_orders_delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>


        </div>

    </div>
</div>
<?php
$content = ob_get_clean();
include '../layout.php';

?>
<script>
    function addRow() {
        let tbody = document.querySelector("#items tbody");
        let row = tbody.querySelector("tr").cloneNode(true);

        // Clear input values
        row.querySelectorAll("input").forEach(input => input.value = "");
        tbody.appendChild(row);
    }

    function removeRow(btn) {
        let row = btn.closest("tr");
        let tbody = document.querySelector("#items tbody");

        // Prevent deleting last row
        if (tbody.rows.length > 1) {
            row.remove();
        }
    }
</script>