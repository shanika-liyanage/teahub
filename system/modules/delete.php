<?php
include '../../init.php';
$conn = dbConnect();


$id = $_GET['id'] ?? null;


if (!$id) {
    die("Invalid Module ID");
}


try {


    $conn->beginTransaction();


    // 🔹 1. CHECK CHILD MODULES
    $checkChild = $conn->prepare("
        SELECT COUNT(*) FROM modules WHERE parent_id = ?
    ");
    $checkChild->execute([$id]);
    $childCount = $checkChild->fetchColumn();


    if ($childCount > 0) {
        throw new Exception("Cannot delete: This module has sub modules");
    }


    // 🔹 2. CHECK ROLE PERMISSIONS
    $checkPermission = $conn->prepare("
        SELECT COUNT(*) FROM role_permissions WHERE module_id = ?
    ");
    $checkPermission->execute([$id]);
    $permCount = $checkPermission->fetchColumn();


    if ($permCount > 0) {
        throw new Exception("Cannot delete: Module is assigned to roles");
    }


    // 🔹 3. DELETE MODULE
    $stmt = $conn->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->execute([$id]);


    $conn->commit();


    header("Location: index.php?deleted=1");
    exit;
} catch (Exception $e) {


    $conn->rollBack();


    header("Location: index.php?error=" . urlencode($e->getMessage()));
    exit;
}


