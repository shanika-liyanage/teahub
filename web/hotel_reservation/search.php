<?php
ob_start();
include '../../init.php';
$pdo=dbConnect();
$rooms = [];
if(isset($_POST['search'])){
    extract($_POST);
    // Validations
    if(empty($checkin_date) || empty($checkout_date)){
        $error = "All dates are required.";
    }elseif($checkin_date >= $checkout_date){
        $error = "Checkout date must be greater.";


    }else{


        $sql = "
        SELECT * FROM rooms
        WHERE id NOT IN(
            SELECT room_id FROM bookings
            WHERE (
                checkin_date < :checkout_date
                AND
                checkout_date > :checkin_date
            )
            AND payment_status='Paid'
        )
        ";


        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':checkin_date', $checkin_date);
        $stmt->bindParam(':checkout_date', $checkout_date);
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>


<form method="POST">
    <input type="date" name="checkin_date" required>
    <input type="date" name="checkout_date" required>
    <button type="submit" name="search">
        Search Rooms
    </button>
</form>


<?php if(!empty($rooms)){ ?>


    <div class="room-container">


        <?php foreach($rooms as $room){ ?>


            <form method="POST" action="reserve.php" class="room-card">


                <h3><?php echo $room['room_name']; ?></h3>
                <p>
                    <strong>Type :</strong>
                    <?php echo $room['room_type']; ?>
                </p>
                <p>
                    <strong>Price :</strong>
                    RS.<?php echo number_format($room['price'],2); ?>
                </p>
                <p>
                    <?php echo $room['description']; ?>
                </p>
                <!-- Hidden Values -->
                <input type="hidden" name="id"
                value="<?php echo $room['id']; ?>">
                <input type="hidden" name="checkin"
                value="<?php echo $checkin_date; ?>">
                <input type="hidden" name="checkout"
                value="<?php echo $checkout_date; ?>">
                <button type="submit" class="btn" name="reserve">
                    Reserve Now
                </button>
            </form>
        <?php } ?>


    </div>


<?php }else{ ?>


    <div class="alert alert-danger">
        No Rooms Available For Selected Dates
    </div>


<?php } ?>
