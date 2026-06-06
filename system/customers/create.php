<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);

    $name = trim($name);
    $mobile = trim($mobile);
    

    

    $error = [];

    if (empty($name)) {
        $error['name'] = "Name is required";
    }
    if(empty($mobile)){
        $error['mobile'] = "Mobile number is required";
    }
        if(empty($address)){
            $error['address'] = "Address is required";
        }

    if (empty($error)) {
        $sql = "INSERT INTO customers(name, mobile,address) VALUES(:name, :mobile,:address)";
        $stmt = $conn->prepare($sql);


        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':address', $address);
      


        $stmt->execute();


        header("Location: view.php");
    }
}

?>

<h3>Add Customer</h3>


<form method="POST" class="container mt-4">
    <input name="name" class="form-control mb-2" placeholder="Name">
    <span class="text-danger"><?= @$error['name'] ?></span>

    
    <input name="mobile" class="form-control mb-2" placeholder="mobile">
    <input name="address" class="form-control mb-2" placeholder="address">
    <button class="btn btn-success">Save</button>
</form>




<?php
$content = ob_get_clean();
include '../layout.php';
?>