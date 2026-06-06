<?php
include '../../init.php';
$conn = dbConnect();


$name = trim($_POST['name']);
$price = trim($_POST['price']);
$sku = trim($_POST['sku']);


$errors = [];


// VALIDATION
if (empty($name)) {
    $errors['name'] = "Name required";
}


if (empty($price)) {
    $errors['price'] = "Price required";
}


if (empty($sku)) {
    $errors['sku'] = "SKU required";
}


// IMAGE VALIDATION
if (!isset($_FILES['image']) || $_FILES['image']['error'] == 4) {
    $errors['image'] = "Image required";
} else {


    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];


    if (!in_array($_FILES['image']['type'], $allowed)) {
        $errors['image'] = "Only JPG/PNG allowed";
    }


    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        $errors['image'] = "Max size 2MB";
    }
}


// RETURN ERRORS
if (!empty($errors)) {
    echo json_encode(["status" => "error", "errors" => $errors]);
    exit;
}




// GET FILE INFO
$file = $_FILES['image'];

//extention ek eliyt gnnw
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);


// GENERATE UNIQUE NAME
$filename = uniqid('prod_', true) . '_' . time() . '.' . $ext;


// UPLOAD PATH
$uploadDir = "products/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}


$path = $uploadDir . $filename;


// MOVE FILE
move_uploaded_file($file['tmp_name'], $path);


// SAVE TO DB
$sql = "INSERT INTO products(name,price,sku,image) VALUES(:name,:price,:sku,:image)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':price', $price);
$stmt->bindParam(':sku', $sku);
$stmt->bindParam(':image', $filename);
$stmt->execute();


echo json_encode(["status" => "success"]);
