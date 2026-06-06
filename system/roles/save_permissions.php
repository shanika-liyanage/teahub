<?php
include '../../init.php';
$conn = dbConnect();


$role_id = $_POST['role_id'];
$modules = $_POST['modules'] ?? [];


try {


    $conn->beginTransaction();


    //Delete old
    $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?")
        ->execute([$role_id]);


    $stmt = $conn->prepare("
        INSERT INTO role_permissions (role_id, module_id)
        VALUES (?, ?)
    ");


    // collect all modules (child + parent)
    $allModules = [];


    foreach ($modules as $module_id) {


        // add child
        $allModules[] = $module_id;


        // 🔹 get parent
        $p = $conn->prepare("SELECT parent_id FROM modules WHERE id = ?");
        $p->execute([$module_id]);
        $parent_id = $p->fetchColumn();


        if ($parent_id) {
            $allModules[] = $parent_id;
        }
    }


    //remove duplicates
    $allModules = array_unique($allModules);


    //insert all
    foreach ($allModules as $module_id) {
        $stmt->execute([$role_id, $module_id]);
    }


    $conn->commit();


    header("Location: permissions.php?role_id=$role_id&saved=1");
    exit;
} catch (Exception $e) {


    $conn->rollBack();
    echo $e->getMessage();
}
