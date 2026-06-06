<?php
ob_start();
include '../../init.php'; // Include the initialization file (which includes config.php)
?>

<div class="container-fluid contact py-5 page-header">
    <h1>payment</h1>
    <table class="table table-striped bg-light">
        <thead>
            <tr>
                <th>Id</th>
                <th>Total Leaves (kg)</th>
                <th>Gross Amount</th>
                <th>Advance Deduction</th>
                <th>Fertilizer Deduction</th>
                <th>Loan Deduction</th>
                <th>Other Deduction</th>
                <th>Net Payable</th>
                <th>Payment Date</th>
                <th>Payment Time</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><a href="#" class="btn btn-primary">view</a></td>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>2024-06-05</td>
                <td>$50.00</td>
                <td>PayPal</td>
                <td>Pending</td>
                <td><a href="#" class="btn btn-primary">make payment</a></td>
            </tr>

        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include '../layout-dashboard.php';
?>
