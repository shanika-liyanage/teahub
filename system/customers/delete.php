<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

extract($_POST);

if ($_SERVER["REQUEST_METHOD"] === "POST" &&  $action == "delete") {
    
    $sql = "DELETE FROM customers WHERE id = $id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header('Location: view.php');
    } else {
        echo "Error deleting record: ";
    }
}



?>









<?php
$content = ob_get_clean();
include '../layout.php';

?>