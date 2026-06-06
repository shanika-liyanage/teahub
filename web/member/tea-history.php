<?php
ob_start();
include '../../init.php';
?>
<div class="container-fluid contact py-5 page-header">
    <h1>Tea History</h1>
    <table class="table table-striped bg-light">
        <thead>
            <tr>
                <th>Quantity</th>
                <th>Price Per (kg)</th>
                <th>Total</th>
                <th>Collection Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>10</td>
                <td>200</td>
                <td>2000</td>
                <td>2025.05</td>
                
                
            </tr>

        </tbody>
    </table>

<?php
$content = ob_get_clean();
include '../layout-dashboard.php';
?>