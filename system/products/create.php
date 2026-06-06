<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

$sku = rand(100000, 999999); // Generate a random SKU number
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);

    $name = trim($name);
    $sku = trim($sku);
    $price = trim($price);

    

    $error = [];

    if (empty($name)) {
        $error['name'] = "Name is required";
    }

    if (empty($error)) {
        $sql = "INSERT INTO products(name, sku, price) VALUES(:name, :sku, :price)";
        $stmt = $conn->prepare($sql);


        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':price', $price);


        $stmt->execute();


        header("Location: index.php");
    }
}

?>

<h3>Add Products</h3>


<form method="POST" class="container mt-4">
    <input name="name" class="form-control mb-2" placeholder="Name">
    <span class="text-danger"><?= @$error['name'] ?></span>
    <input name="sku" class="form-control mb-2" placeholder="SKU" value="<?= $sku ?>" readonly>
    <input name="price" class="form-control mb-2" placeholder="Price">
    <button class="btn btn-success">Save</button>
</form>




<?php
$content = ob_get_clean();
include '../layout.php';
?>