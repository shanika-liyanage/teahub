<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   LOAD SUPPLIERS FOR FILTER
------------------------------*/
$sql = "SELECT id, title, first_name, last_name,register_no
        FROM suppliers 
        ORDER BY first_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* -----------------------------
   GET FILTER VALUES
------------------------------*/
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';


/* -----------------------------
   LOAD TEA COLLECTION DATA
------------------------------*/
$sql = "SELECT tc.*, 
               s.title,
               s.first_name,
               s.last_name,
               s.register_no
        FROM tea_collection tc
        INNER JOIN suppliers s 
        ON tc.supplier_id = s.id
        WHERE 1=1";

$params = [];


/* -----------------------------
   DATE FILTER
------------------------------*/
if (!empty($from_date)) {
    $sql .= " AND tc.collection_date >= :from_date";
    $params[':from_date'] = $from_date;
}

if (!empty($to_date)) {
    $sql .= " AND tc.collection_date <= :to_date";
    $params[':to_date'] = $to_date;
}


/* -----------------------------
   SUPPLIER FILTER
------------------------------*/
if (!empty($supplier_id)) {
    $sql .= " AND tc.supplier_id = :supplier_id";
    $params[':supplier_id'] = $supplier_id;
}


/* -----------------------------
   ORDER BY
------------------------------*/
$sql .= " ORDER BY tc.id DESC";


$stmt = $conn->prepare($sql);
$stmt->execute($params);
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="row">
    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h3 class="card-title">Tea Collection List</h3>

                <div class="card-tools">
                    <a href="create.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Collection
                    </a>
                </div>
            </div>

            <div class="card-body">

                <!-- FILTER FORM -->
                <form method="GET" class="mb-3">

                    <div class="row">

                        <div class="col-md-3">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
                        </div>

                        <div class="col-md-3">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Supplier</label>

                            <select name="supplier_id" class="form-control">
                                <option value="">-- Select Supplier --</option>

                                <?php foreach ($suppliers as $supplier): ?>

                                    <option value="<?= $supplier['id'] ?>" <?= ($supplier_id == $supplier['id']) ? 'selected' : '' ?>>

                                        <?= ucfirst($supplier['title']) ?>.
                                        <?= $supplier['first_name'] ?>
                                        <?= $supplier['last_name'] ?>
                                        <?= $supplier['register_no'] ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">

                            <button type="submit" class="btn btn-success mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>

                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>

                        </div>

                    </div>

                </form>


                <!-- TABLE -->
                <table id="example1" class="table table-bordered table-striped">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Gross Weight (Kg)</th>
                            <th>Total Deductions (Kg)</th>
                            <th>Net Weight (Kg)</th>
                            <th>Price(Rs.) / Kg</th>
                            <th>Total Amount (Rs.)</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $i = 1;
                        $gtotal_gross = 0;
                        $gtotal_deduction = 0;
                        $gtotal_net = 0;
                        $gtotal_amount = 0;

                        foreach ($collections as $row):
                            $gtotal_gross += $row['gross_weight'];
                            $gtotal_deduction += $row['water_deduction'] + $row['mature_leaf_deduction'] + $row['other_deduction'] + $row['total_bag_weight'] + $row['total_box_weight'];
                            $gtotal_net += $row['net_weight'];
                            $gtotal_amount += $row['total_amount'];

                            $total_deduction =
                                $row['water_deduction'] +
                                $row['mature_leaf_deduction'] +
                                $row['other_deduction'] +
                                $row['total_bag_weight'] +
                                $row['total_box_weight'];

                            ?>

                            <tr>

                                <td><?= $i++ ?></td>

                                <td>
                                    <?= ucfirst($row['title']) ?>.
                                    <?= $row['first_name'] ?>
                                    <?= $row['last_name'] ?>
                                    (<?= $row['register_no'] ?>)
                                </td>

                                <td><?= $row['collection_date'] ?></td>

                                <td>
                                    <?= number_format($row['gross_weight'], 2) ?> Kg
                                </td>

                                <td>
                                    <?= number_format($total_deduction, 2) ?> Kg
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        <?= number_format($row['net_weight'], 2) ?> Kg
                                    </span>
                                </td>

                                <td>
                                    Rs. <?= number_format($row['price_per_kg'], 2) ?>
                                </td>

                                <td>
                                    <strong>
                                        Rs. <?= number_format($row['total_amount'], 2) ?>
                                    </strong>
                                </td>

                            </tr>

                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3">Total</td>
                            <td><?= number_format($gtotal_gross) ?>Kg</td>
                            <td><?= number_format($gtotal_deduction) ?>Kg</td>
                            <td><?= number_format($gtotal_net) ?>Kg</td>
                            <td></td>
                            <td>Rs. <?= number_format($gtotal_amount) ?></td>

                        </tr>
                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>


<?php
$content = ob_get_clean();
include '../layout.php';
?>