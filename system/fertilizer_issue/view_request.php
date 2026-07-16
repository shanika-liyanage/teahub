<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

if (!isset($_GET['id'])) {
    header("Location: request.php");
    exit;
}

$id = $_GET['id'];

$sql = "SELECT fr.*,
               CONCAT(s.first_name,' ',s.last_name) AS supplier_name,
               s.mobile,
               s.nic,
               s.area,
               fi.name AS fertilizer_name
        FROM fertilizer_requests fr
        LEFT JOIN suppliers s ON s.id = fr.supplier_id
        LEFT JOIN fertilizer_items fi ON fi.id = fr.fertilizer_id
        WHERE fr.id = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header("Location: request.php");
    exit;
}
?>

<div class="container-fluid">

    <div class="row mb-3">
        <div class="col">
            <h3>Fertilizer Request Details</h3>
        </div>
    </div>

    <div class="card">

        <div class="card-header bg-success text-white">
            Request Information
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="250">Request ID</th>
                    <td><?= $request['id'] ?></td>
                </tr>

                <tr>
                    <th>Supplier Name</th>
                    <td><?= $request['supplier_name'] ?></td>
                </tr>

                <tr>
                    <th>NIC</th>
                    <td><?= $request['nic'] ?></td>
                </tr>

                <tr>
                    <th>Mobile</th>
                    <td><?= $request['mobile'] ?></td>
                </tr>

                <tr>
                    <th>Area</th>
                    <td><?= $request['area'] ?></td>
                </tr>

                <tr>
                    <th>Fertilizer</th>
                    <td><?= $request['fertilizer_name'] ?></td>
                </tr>

                <tr>
                    <th>Requested Quantity</th>
                    <td><?= $request['qty_requested'] ?> Kg</td>
                </tr>

                <tr>
                    <th>Required Date</th>
                    <td><?= $request['required_date'] ?></td>
                </tr>

                <tr>
                    <th>Remarks</th>
                    <td><?= !empty($request['remarks']) ? $request['remarks'] : '-' ?></td>
                </tr>

                <tr>
                    <th>Request Date</th>
                    <td><?= $request['request_date'] ?></td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <?php
                        if ($request['status'] == 'PENDING') {
                            echo '<span class="badge bg-warning">Pending</span>';
                        } elseif ($request['status'] == 'APPROVED') {
                            echo '<span class="badge bg-success">Approved</span>';
                        } else {
                            echo '<span class="badge bg-danger">Rejected</span>';
                        }
                        ?>
                    </td>
                </tr>

            </table>

        </div>

        <div class="card-footer">

            <a href="request.php" class="btn btn-secondary">
                Back
            </a>
            


            <?php if ($request['status'] == 'PENDING') { ?>

                <a href="approve.php?id=<?= $request['id'] ?>"
                   class="btn btn-success">
                    Approve
                </a>

                <a href="reject.php?id=<?= $request['id'] ?>"
                   class="btn btn-danger">
                    Reject
                </a>

                

            <?php } ?>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>