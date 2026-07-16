<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

$sql = "SELECT fi.id,
               fi.issue_date,
               fi.remarks,
               s.first_name,
               s.last_name,
               fi.Issued_Quantity,
               fi.total_amount,
               fi.unit_price,
               f.name AS fertilizer_name
        FROM fertilizer_issue fi

        INNER JOIN suppliers s
            ON s.id = fi.supplier_id    

        INNER JOIN fertilizer_items f
            ON f.id = fi.fertilizer_id

        ORDER BY fi.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <h3 class="mb-3">Fertilizer Issue History</h3>

    <table class="table table-bordered table-striped">

        <thead class="table-success">
            <tr>
                <th>Issue ID</th>
                <th>Supplier</th>
                <th>Fertilizer</th>
                <th>Quantity (Kg)</th>
                <th>Unit Price(Rs.)</th>
                <th>Total Amount(Rs.)</th>
                <th>Issue Date</th>
                
            </tr>
        </thead>

        <tbody>

        <?php foreach($rows as $row){ ?>

            <tr>

                <td><?= $row['id']; ?></td>

                <td><?= $row['first_name']." ".$row['last_name']; ?></td>

                <td><?= $row['fertilizer_name']; ?></td>

                <td><?= $row['Issued_Quantity']; ?></td>
                <td><?= $row['unit_price']; ?></td>
                   <td><?= $row['total_amount']; ?></td>

                <td><?= $row['issue_date']; ?></td>

                

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>