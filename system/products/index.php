<?php
ob_start();
include '../../init.php';

$conn = dbConnect();
//fetch all products from the database
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); //associate aray ekak wdyt data varible ekt dl denw tble eke tyn tika


?>

<h3>Products</h3>
<a href="create.php" class="btn btn-primary mb-2">Add Product</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>SKU</th>
            <th>Price</th>
            
        </tr>
</thead>
<tbody>
    <?php
    foreach ($products as $product){ ?>
        <tr>
        <td><?= $product['id'] ?></td>
        <td><?= $product['name'] ?></td>
        <td><?= $product['sku'] ?></td>
        <td><?= $product['price'] ?></td>
        
        </tr>
   <?php } ?>
</tbody>



    
</table>









<?php
$content = ob_get_clean();
include '../layout.php';
?>