<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
//get the id from the url
//$id = $_GET['id'];
extract($_POST);

if($_SERVER['REQUEST_METHOD'] === "POST" && $action === "edit") {
    //create prepare statement to fetch the product from the database


$stmt = $conn->prepare("SELECT * FROM customers WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);//fetch waladi eka record ekk wtrak array krno signla record ekkt
    
}




if ($_SERVER['REQUEST_METHOD'] === "POST" && $action === "update") {



   // $name = $_POST['name'];
    //$phone = $_POST['mobile'];
   // $address = $_POST['address'];  EXTRACT WENUWT EW DNN PLWN


    $sql = "UPDATE customers
            SET name = :name, mobile = :mobile, address = :address
            WHERE id = :id";


    $stmt = $conn->prepare($sql);


    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':mobile', $mobile);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':id', $id);


    if ($stmt->execute()) {
        header("Location: view.php");
    } else {
        echo "Update failed.";
    }
}

?>

<h3>Edit Customer</h3>


<form method="POST" >
    <div class="mb-2">
        <label>Name</label>
        <input type="text" name="name" value="<?= $customer['name']; ?>" class="form-control">
    </div>


    <div class="mb-2">
        <label>Phone</label>
        <input type="text" name="mobile" value="<?= $customer['mobile']; ?>" class="form-control">
    </div>


    <div class="mb-2">
        <label>Address</label>
        <textarea name="address" class="form-control"><?= $customer['address']; ?></textarea>
    </div>

<input type="hidden" name ="id" value="<?= $customer['id']; ?>" >
    <button class="btn btn-primary" type = "submit" name ="action" value="update">Update</button>
</form>









<?php
$content = ob_get_clean();
include '../layout.php';
?>