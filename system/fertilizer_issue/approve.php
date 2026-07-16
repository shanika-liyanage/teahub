<?php
ob_start();
include '../../init.php';

$conn = dbConnect();

if (!isset($_GET['id'])) {
    header("Location: request.php");
    exit;
}

$id = $_GET['id'];

try {

    $sql = "UPDATE fertilizer_requests
            SET status='APPROVED'
            WHERE id=:id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['success'] = "Fertilizer request approved successfully.";

} catch (PDOException $e) {

    $_SESSION['error'] = $e->getMessage();
}

header("Location: request.php");
exit;
?>