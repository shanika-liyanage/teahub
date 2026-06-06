<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//data base connection
function dbConnect()
{
    $servername = "localhost";
    $dbname = "teahub";
    $username = "root";
    $password = "";
    $charset = "utf8mb4";
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset"; //data source name
    try {
        $pdo = new PDO($dsn, $username, $password);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}


function hasPermission($module_url){
    
    $conn = dbConnect();
    $user_id = $_SESSION['user_id'];

    // Get role
    $sql = "SELECT role_id FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $role_id = $stmt->fetchColumn();

    // Check permission using URL
    $sql = "SELECT COUNT(*) FROM role_permissions rp JOIN modules m ON m.id = rp.module_id WHERE rp.role_id = :role_id AND m.url = :url";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':role_id', $role_id);
    $stmt->bindParam(':url', $module_url);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}


function sendEmail($to = null, $receiver = null, $subject = null, $body = null)
{
    require 'email/autoload.php'; // Load PHPMailer

    $mail = new PHPMailer(true);

    try {
        //  Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'xxxxxxxxxxx@gmail.com'; // Your Gmail
        $mail->Password   = 'xxxxxxxx'; // App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & Receiver
        $mail->setFrom('mpsarathw@gmail.com', 'Your Name');
        $mail->addAddress($to, $receiver);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}




function getCartSummary($conn)
{
    $total = 0;
    $count = 0;


    if (isset($_SESSION['user_id'])) {


        $stmt = $conn->prepare("
            SELECT c.qty, p.price
            FROM cart c
            JOIN products p ON p.id=c.product_id
            WHERE user_id=?
        ");
        $stmt->execute([$_SESSION['user_id']]);


        foreach ($stmt as $row) {
            $count += $row['qty'];
            $total += $row['qty'] * $row['price'];
        }
    } else {


        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $item) {


                $p = $conn->query("SELECT price FROM products WHERE id=$id")->fetch();


                $count += $item['qty'];
                $total += $item['qty'] * $p['price'];
            }
        }
    }


    return [
        'count' => $count,
        'total' => $total
    ];
}

