<?php
include '../../init.php';
$conn = dbConnect();
$sql = "SELECT * FROM products";
$res = $conn->prepare($sql);
$res->execute();
$row = $res->fetchAll(PDO::FETCH_ASSOC);


foreach ($row as $row) {
    echo "<tr>
    <td>{$row['id']}</td>
    <td>{$row['name']}</td>
    <td>{$row['price']}</td>
    <td>{$row['sku']}</td>
    <td>
        <button class='btn btn-warning editBtn' data-id='{$row['id']}'>Edit</button>
        <button class='btn btn-danger deleteBtn' data-id='{$row['id']}'>Delete</button>
    </td>
</tr>";
}
