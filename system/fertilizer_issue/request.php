<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

$sql  = "SELECT fr.*,
               CONCAT(s.first_name,' ',s.last_name) AS supplier_name,
               fi.name AS fertilizer_name
        FROM fertilizer_requests fr
        LEFT JOIN suppliers s ON s.id = fr.supplier_id
        LEFT JOIN fertilizer_items fi ON fi.id = fr.fertilizer_id
        ORDER BY fr.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <div class="row mb-3">
        <div class="col">
            <h3>Fertilizer Requests</h3>
        </div>
    </div>

    <table class="table table-bordered table-striped">

        <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>Supplier</th>
                <th>Fertilizer</th>
                <th>Quantity (Kg)</th>
                <th>Required Date</th>
                <th>Request Date</th>
                <th>Status</th>
                <th width="180">Action</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach($requests as $row){ ?>

            <tr>

                <td><?= $row['id'] ?></td>

                <td><?= $row['supplier_name'] ?></td>

                <td><?= $row['fertilizer_name'] ?></td>

                <td><?= $row['qty_requested'] ?></td>

                <td><?= $row['required_date'] ?></td>

                <td><?= $row['request_date'] ?></td>

                <td>
                    <?php
                    if($row['status']=='PENDING'){
                        echo '<span class="badge bg-warning">Pending</span>';
                    }elseif($row['status']=='APPROVED'){
                        echo '<span class="badge bg-success">Approved</span>';
                    }elseif($row['status']=='ISSUED'){
                        echo '<span class="badge bg-primary">Issued</span>';
                    }else{
                        echo '<span class="badge bg-danger">Rejected</span>';
                    }
                    ?>
                </td>

                <td>

    <a href="view_request.php?id=<?= $row['id'] ?>"
       class="btn btn-info btn-sm">
        View
    </a>

    <?php if($row['status']=='PENDING'){ ?>

        <a href="approve.php?id=<?= $row['id'] ?>"
           class="btn btn-success btn-sm">
            Approve
        </a>

        <a href="reject.php?id=<?= $row['id'] ?>"
           class="btn btn-danger btn-sm">
            Reject
        </a>

    <?php } elseif($row['status']=='APPROVED'){ ?>

        <a href="issue_fertilizer.php?id=<?= $row['id'] ?>"
           class="btn btn-primary btn-sm">
            Issue
        </a>

    <?php }  ?>

        

</td>

            </tr>

            <?php } ?>

        </tbody>

    </table>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>