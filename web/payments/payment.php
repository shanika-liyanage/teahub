<?php
ob_start();
include '../../init.php';
$pdo = dbConnect();


extract($_GET);
// Get Booking


/*$sql = "
SELECT bookings.*, rooms.room_name
FROM bookings
INNER JOIN rooms
ON rooms.id = bookings.room_id
WHERE bookings.id=:id
";*/


/*$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);


if (empty($booking)) {
    die("Invalid Booking");
}
*/


/*
|--------------------------------------------------------------------------
| PayHere Sandbox Credentials
|--------------------------------------------------------------------------
*/


$merchant_id = "1235921";


/*
|--------------------------------------------------------------------------
| Generate Hash
|--------------------------------------------------------------------------
*/

$total_amount = 100.00; // Replace with actual amount
$merchant_secret = "NDI1MTg5OTY0MTM1ODE1MTc4MDExNzYzMzkyNTQ1NDY2Nzk3MzA=";
$amount = number_format($total_amount, 2, '.', '');
$currency = "LKR";
$hash = strtoupper(
    md5(
        $merchant_id .
            $orderid .
            $amount .
            $currency .
            strtoupper(md5($merchant_secret))
    )
);


?>


<h2>Redirecting To Payment Gateway...</h2>
<form method="post" action="https://sandbox.payhere.lk/pay/checkout">
    <input type="hidden" name="merchant_id" value="<?= $merchant_id ?>"> <!-- Replace your Merchant ID -->
    <input type="hidden" name="return_url" value="http://localhost/TeaSample/web/payments/payment-return.php">
    <input type="hidden" name="cancel_url" value="http://localhost/TeaSample/web/payments/payment-cancel.php">
    <input type="hidden" name="notify_url" value="http://localhost/TeaSample/web/payments/payment-notify.php">
    </br></br>Item Details</br>
    <input type="text" name="order_id" value="<?= $orderid?>">
    <input type="text" name="items" value="order payment">
    <input type="text" name="currency" value="<?= $currency ?>">
    <input type="text" name="amount" value="<?= $amount ?>">
    </br></br>Customer Details</br>
    <input type="text" name="first_name" value="shanika">
    <input type="text" name="last_name" value="liyanag">
    <input type="text" name="email" value="shg@gmail.com">
    <input type="text" name="phone" value="0897432121">
    <input type="text" name="address" value="karapitya">
    <input type="text" name="city" value="galle">
    <input type="hidden" name="country" value="Sri Lanka">
    <?php $hash = strtoupper(
        md5(
            $merchant_id .
                $orderid .
                number_format($amount, 2, '.', '') .
                $currency .
                strtoupper(md5($merchant_secret))
        )
    ); ?>

    <input type="hidden" name="hash" value="<?= $hash ?>"> <!-- Replace with generated hash -->
    <input type="submit" value="Buy Now">
</form>
<script>
    document.getElementById("payhere-form").submit();
</script>
