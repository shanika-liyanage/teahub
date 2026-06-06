<?php
$orderid = 857;
header("location:payment.php?orderid=$orderid");

?>
<a href="payment.php?orderid=<?= $orderid ?>">pay via card</a>
<a href="payment.php?orderid=<?= $orderid ?>">pay via bank transfer</a>
<a href="payment.php?orderid=<?= $orderid ?>">pay via cash on delivery</a>