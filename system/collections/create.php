<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   Load suppliers
------------------------------*/
$sql = "SELECT * FROM suppliers";
$stmt = $conn->prepare($sql);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   FORM SUBMIT
------------------------------*/
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplier_id = $_POST['supplier_id'];
    $collection_date = $_POST['collection_date'];
    $gross_weight = trim($_POST['gross_weight']);
    $water = trim($_POST['water']);
    $mature = trim($_POST['mature']);
    $other = trim($_POST['other']);
    $price = trim($_POST['price_per_kg']);
    $bag = trim($_POST['bag']);
    $box = trim($_POST['box']);

    // ---------------- validation
    if (empty($supplier_id)) {
        $error['supplier_id'] = "Supplier is required";
    }
    if (empty($collection_date)) {
        $error['collection_date'] = "Date is required";
    }
    if ($gross_weight <= 0) {
        $error['gross_weight'] = "Gross weight required";
    }

    // ---------------- calculation
    if (empty($error)) {
$total_bag_weight = 0;
$total_box_weight = 0;
        if (!empty($bag) && $bag > 0) {
            $sql = "SELECT value FROM utility_data WHERE name = :bag";
            $bagweight = "bagWeight";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':bag', $bagweight);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $bag_weight = $result['value'] ?? 0;

            $total_bag_weight = $bag * $bag_weight;
        }
        if (!empty($box) && $box > 0) {
            $sql = "SELECT value FROM utility_data WHERE name = :box";
            $boxweight = "boxWeight";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':box', $boxweight);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $box_weight = $result['value'] ?? 0;
            $total_box_weight = $box * $box_weight;
        }
        $total_deduction = $water + $mature + $other + $total_bag_weight + $total_box_weight;

        $net_weight = $gross_weight - $total_deduction;
        $total_amount = $net_weight * $price;

        try {
            $sql = "INSERT INTO tea_collection
            (supplier_id, collection_date, gross_weight, water_deduction, mature_leaf_deduction, other_deduction, net_weight, price_per_kg, total_amount, no_bag, no_box, total_bag_weight, total_box_weight)
            VALUES
            (:supplier_id, :collection_date, :gross_weight, :water, :mature, :other, :net_weight, :price, :total, :bag, :box, :total_bag_weight, :total_box_weight)";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':supplier_id', $supplier_id);
            $stmt->bindParam(':collection_date', $collection_date);
            $stmt->bindParam(':gross_weight', $gross_weight);
            $stmt->bindParam(':water', $water);
            $stmt->bindParam(':mature', $mature);
            $stmt->bindParam(':other', $other);
            $stmt->bindParam(':net_weight', $net_weight);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':total', $total_amount);
            $stmt->bindParam(':bag', $bag);
            $stmt->bindParam(':box', $box);
            $stmt->bindParam(':total_bag_weight', $total_bag_weight);
            $stmt->bindParam(':total_box_weight', $total_box_weight);

            $stmt->execute();

            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!-- ================= UI ================= -->

<h3>Create Tea Collection</h3>

<form method="POST">

    <!-- Supplier -->
    <select name="supplier_id" class="form-control mb-2">
        <option value="">Select Supplier</option>
        <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>">
                <?= ucfirst($s['title']) ?>. <?= $s['first_name'] ?> <?= $s['last_name'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <span style="color:red;">
        <?= $error['supplier_id'] ?? '' ?>
    </span>

    <!-- Date -->
    <input type="date" name="collection_date" class="form-control mb-2">
    <span style="color:red;">
        <?= $error['collection_date'] ?? '' ?>
    </span>
    <!-- Price -->
    <?php
    $conn = dbConnect();
    $sql = "SELECT amount FROM tea_leaves_purchase_price_list WHERE status = :status ORDER BY date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $active_status = "Active";
    $stmt->bindParam(':status', $active_status);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $price = $result['amount'] ?? 0;

    ?>
    <input type="number" step="0.01" name="price_per_kg" class="form-control mb-2" placeholder="Price per KG" value="<?= $price ?>">
    <table class="table table-bordered table-striped">
        <tr>
            <td></td>
            <td>Kilogram(Kg)</td>

        </tr>
        <tr>
            <td>Gross Weight</td>
            <td><input type="number" step="0.01" name="gross_weight" class="form-control"></td>

        </tr>
        <tr>
            <td>Water Deduction</td>
            <td><input type="number" step="0.01" name="water" class="form-control"></td>

        </tr>
        <tr>
            <td>Mature Leaf</td>
            <td><input type="number" step="0.01" name="mature" class="form-control"></td>

        </tr>
        <tr>
            <td>Other Deductions</td>
            <td><input type="number" step="0.01" name="other" class="form-control"></td>

        </tr>
        <tr>
            <td>Number of Bag</td>
            <td><input type="number"  name="bag" class="form-control"></td>

        </tr>
        <tr>
            <td>Number of Box</td>
            <td><input type="number"  name="box" class="form-control"></td>

        </tr>


    </table>




    <button class="btn btn-success">Save</button>

</form>

<?php
$content = ob_get_clean();
include '../layout.php';
?>