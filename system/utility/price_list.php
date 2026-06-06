<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

// Load Price List
$sql = "SELECT * FROM tea_leaves_purchase_price_list ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();

$price_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="row">
    <div class="col-12">

        <div class="card mt-4">

            <div class="card-header">

                <h3 class="card-title">
                    Tea Leaves Purchase Price List
                </h3>

                <div class="card-tools">

                    <a href="create_price.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Price
                    </a>

                </div>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($price_list)) : ?>

                            <?php foreach ($price_list as $p) : ?>

                                <tr>

                                    <td><?= $p['id'] ?></td>

                                    <td><?= $p['date'] ?></td>

                                    <td>
                                        Rs. <?= number_format($p['amount'], 2) ?>
                                    </td>

                                    <td>

                                        <?php if ($p['status'] == 'Active') : ?>

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        <?php else : ?>

                                            <span class="badge bg-danger">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if (hasPermission('utility/edit_price.php')) : ?>

                                            <form action="edit_price.php" method="POST">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= $p['id'] ?>">

                                                <button type="submit"
                                                        name="action"
                                                        value="edit"
                                                        class="btn btn-sm btn-outline-primary">

                                                    <i class="fas fa-edit"></i> Edit

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else : ?>

                            <tr>

                                <td colspan="5" class="text-center text-danger">
                                    No Price Records Found
                                </td>

                            </tr>

                        <?php endif; ?>

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