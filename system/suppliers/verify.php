<?php
include '../../init.php';
$conn = dbConnect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    extract($_POST);

    try {
        $sql = "UPDATE suppliers SET verify = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: index.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<?php
$content = ob_get_clean();
include '../layout.php';
?>