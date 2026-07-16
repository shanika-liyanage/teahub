<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

/*
|--------------------------------------------------------------------------
| LOGGED IN SUPPLIER
|--------------------------------------------------------------------------
*/
$supplier_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| FILTER VALUES
|--------------------------------------------------------------------------
*/
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

/*
|--------------------------------------------------------------------------
| LOAD TEA COLLECTION HISTORY
|--------------------------------------------------------------------------
*/
$sql = "SELECT *
        FROM tea_collection
        WHERE supplier_id = :supplier_id";

$params = [
    ':supplier_id' => $supplier_id
];

if (!empty($from_date)) {
    $sql .= " AND collection_date >= :from_date";
    $params[':from_date'] = $from_date;
}

if (!empty($to_date)) {
    $sql .= " AND collection_date <= :to_date";
    $params[':to_date'] = $to_date;
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">

    <h2 class="mb-4 text-center">
        My Tea Supply History
    </h2>

    <!-- FILTER -->
    <div class="card mb-4">

        <div class="card-header bg-success text-white">
            Filter Records
        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4">
                        <label>From Date</label>
                        <input type="date"
                               name="from_date"
                               class="form-control"
                               value="<?= $from_date ?>">
                    </div>

                    <div class="col-md-4">
                        <label>To Date</label>
                        <input type="date"
                               name="to_date"
                               class="form-control"
                               value="<?= $to_date ?>">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">

                        <button type="submit"
                                class="btn btn-success me-2">
                            Filter
                        </button>

                        <a href="index.php"
                           class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- TEA COLLECTION HISTORY -->
    <div class="card">

        <div class="card-header bg-dark text-white">
            Tea Collection History
        </div>

        <div class="card-body">

            <table class="table table-bordered table-striped">

                <thead>

                    <tr>
                        <th>Date</th>
                        <th>Gross Weight (Kg)</th>
                        <th>Total Deductions (Kg)</th>
                        <th>Net Weight (Kg)</th>
                        <th>Price Per Kg (Rs.)</th>
                        <th>Total Amount (Rs.)</th>
                    </tr>

                </thead>

                <tbody>

                    <?php
                    $total_gross = 0;
                    $total_deduction = 0;
                    $total_net = 0;
                    $total_amount = 0;

                    foreach ($collections as $row):

                        $deduction =
                            $row['water_deduction'] +
                            $row['mature_leaf_deduction'] +
                            $row['other_deduction'] +
                            $row['total_bag_weight'] +
                            $row['total_box_weight'];

                        $total_gross += $row['gross_weight'];
                        $total_deduction += $deduction;
                        $total_net += $row['net_weight'];
                        $total_amount += $row['total_amount'];
                    ?>

                        <tr>

                            <td>
                                <?= $row['collection_date'] ?>
                            </td>

                            <td>
                                <?= number_format($row['gross_weight'], 2) ?>
                            </td>

                            <td>
                                <?= number_format($deduction, 2) ?>
                            </td>

                            <td>
                                <span class="badge bg-success">
                                    <?= number_format($row['net_weight'], 2) ?>
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

                    <?php if (count($collections) > 0) { ?>

                        <tr class="table-secondary">

                            <th>Total</th>

                            <th>
                                <?= number_format($total_gross, 2) ?> Kg
                            </th>

                            <th>
                                <?= number_format($total_deduction, 2) ?> Kg
                            </th>

                            <th>
                                <?= number_format($total_net, 2) ?> Kg
                            </th>

                            <th></th>

                            <th>
                                Rs. <?= number_format($total_amount, 2) ?>
                            </th>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout-dashboard.php';
?>