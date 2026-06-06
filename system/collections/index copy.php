<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   LOAD TEA COLLECTION DATA
------------------------------*/
$sql = "SELECT tc.*, 
               s.title,
               s.first_name,
               s.last_name
        FROM tea_collection tc
        INNER JOIN suppliers s 
        ON tc.supplier_id = s.id
        ORDER BY tc.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
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

                <table id="example1" class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <!-- SELECT ALL CHECKBOX -->
                            <th width="40">
                                <input type="checkbox" id="select_all">
                            </th>

                            <th>#</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Gross Weight</th>
                            <th>Total Deductions</th>
                            <th>Net Weight</th>
                            <th>Price / KG</th>
                            <th>Total Amount</th>

                            <!-- NEW COLUMNS -->
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Payment Date</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $i = 1;
                        $total_deduction = 0;
                        foreach ($collections as $row):

                            // DEFAULT VALUES
                            $status = $row['status'] ?? 'Pending';
                            $payment_status = $row['payment_status'] ?? 'Unpaid';
                            $payment_date = $row['payment_date'] ?? '-';
                            $total_deduction = $row['water_deduction'] + $row['mature_leaf_deduction'] + $row['other_deduction'] + $row['total_bag_weight'] + $row['total_box_weight'];

                        ?>

                            <tr>

                                <!-- ROW CHECKBOX -->
                                <td>
                                    <input type="checkbox"
                                        class="row_checkbox"
                                        value="<?= $row['id'] ?>">
                                </td>

                                <td><?= $i++ ?></td>

                                <td>
                                    <?= ucfirst($row['title']) ?>.
                                    <?= $row['first_name'] ?>
                                    <?= $row['last_name'] ?>
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

                                <!-- STATUS -->
                                <!-- STATUS -->
                                <td>

                                    <div class="form-check form-switch">

                                        <input class="form-check-input status-switch"
                                            type="checkbox"
                                            <?= ($status == 'Processing') ? 'checked' : '' ?>>

                                        <label class="form-check-label">

                                            <?php if ($status == 'Processing') { ?>

                                                <span class="badge bg-success status-label">
                                                    Processing
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-warning status-label">
                                                    Pending
                                                </span>

                                            <?php } ?>

                                        </label>

                                    </div>

                                </td>

                                <!-- PAYMENT STATUS -->
                                <!-- PAYMENT STATUS -->
                                <td>

                                    <div class="form-check form-switch">

                                        <input class="form-check-input payment-switch"
                                            type="checkbox"
                                            <?= ($payment_status == 'Paid') ? 'checked' : '' ?>>

                                        <label class="form-check-label">

                                            <?php if ($payment_status == 'Paid') { ?>

                                                <span class="badge bg-success payment-label">
                                                    Paid
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-danger payment-label">
                                                    Unpaid
                                                </span>

                                            <?php } ?>

                                        </label>

                                    </div>

                                </td>
                                <!-- PAYMENT DATE -->
                                <td>
                                    <?= $payment_date ?>
                                </td>

                                <td>

                                    <!-- EDIT -->
                                    <a href="edit.php?id=<?= $row['id'] ?>"
                                        class="btn btn-warning btn-sm">

                                        <i class="fas fa-edit"></i>

                                    </a>

                                    <!-- DELETE -->
                                    <a href="delete.php?id=<?= $row['id'] ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure to delete this record ?')">

                                        <i class="fas fa-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

<!-- CHECKBOX SCRIPT -->
<script>
    // SELECT ALL
    document.getElementById('select_all').addEventListener('click', function() {

        let checkboxes = document.querySelectorAll('.row_checkbox');

        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('select_all').checked;
        });

    });


    // STATUS SWITCH
    document.querySelectorAll('.status-switch').forEach(function(sw) {

        sw.addEventListener('change', function() {

            let label = this.parentElement.querySelector('.status-label');

            if (this.checked) {

                label.innerHTML = 'Processing';
                label.classList.remove('bg-warning');
                label.classList.add('bg-success');

            } else {

                label.innerHTML = 'Pending';
                label.classList.remove('bg-success');
                label.classList.add('bg-warning');

            }

        });

    });


    // PAYMENT SWITCH
    document.querySelectorAll('.payment-switch').forEach(function(sw) {

        sw.addEventListener('change', function() {

            let label = this.parentElement.querySelector('.payment-label');

            if (this.checked) {

                label.innerHTML = 'Paid';
                label.classList.remove('bg-danger');
                label.classList.add('bg-success');

            } else {

                label.innerHTML = 'Unpaid';
                label.classList.remove('bg-success');
                label.classList.add('bg-danger');

            }

        });

    });
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>