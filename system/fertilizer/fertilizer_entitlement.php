<?php
ob_start();
include '../../init.php';
$conn = dbConnect();
$error = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // EXTRACT POST DATA
    extract($_POST);

    // TRIM INPUTS
    $fertilizer_id = trim($fertilizer_id);
    $leaf_qty = trim($leaf_qty);
    $rate_per_kg = trim($rate_per_kg);


    /* -----------------------------
       VALIDATIONS
    ------------------------------*/

    // Name Validation
    if (empty($fertilizer_id)) {
        $error['fertilizer_id'] = "Fertilizer name is required";
    }

    // Weight Validation
    if (empty($leaf_qty)) {
        $error['leaf_qty'] = "quantity is required";
    } elseif (!is_numeric($leaf_qty)) {
        $error['leaf_qty'] = "quantity must be numeric";
    }





    /* -----------------------------
       CHECK IF NAME ALREADY EXISTS

    /* -----------------------------
       INSERT DATA
    ------------------------------*/
    if (empty($error)) {

        $sql = "INSERT INTO fertilizer_entitlement (fertilizer_id, leaf_qty, rate_per_kg)
                VALUES
                (:fertilizer_id, :leaf_qty, :rate_per_kg)";


        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':fertilizer_id', $fertilizer_id);
        $stmt->bindParam(':leaf_qty', $leaf_qty);
        $stmt->bindParam(':rate_per_kg', $rate_per_kg);


        $stmt->execute();

        $success = "Fertilizer entitlement added successfully";

        // CLEAR FORM
        $fertilizer_id = '';
        $leaf_qty = '';
        $rate_per_kg = '';
    }
}

/* -----------------------------
   LOAD FERTILIZER ITEMS
------------------------------*/
$sql = "SELECT E.*,I.name FROM fertilizer_entitlement E JOIN fertilizer_items I ON I.id=E.fertilizer_id ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <div class="row">

        <!-- FORM SECTION -->
        <div class="col-md-4">

            <div class="card">

                <div class="card-header">
                    <h4>Add Fertilizer Entitlement</h4>
                </div>

                <div class="card-body">

                    <?php if (!empty($success)) { ?>
                        <div class="alert alert-success">
                            <?= $success ?>
                        </div>
                    <?php } ?>

                    <form method="POST" action="">

                        <!-- NAME -->
                        <div class="mb-3">

                            <label class="form-label">
                                Fertilizer Name
                            </label>

                            <select
                                name="fertilizer_id"
                                class="form-control"
                                value="<?= @$name ?>">
                                <option value="">Select Fertilizer</option>
                                <?php
                                $sql = "SELECT * FROM fertilizer_items";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $fertilizer_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($fertilizer_items as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>    
                                <?php endforeach; ?>

                            </select>

                            <small class="text-danger">
                                <?= @$error['fertilizer_id'] ?>
                            </small>

                        </div>

                        <!-- WEIGHT -->

                        <div class="mb-3">

                            <label class="form-label">
                                Leaf Quantity

                            </label>

                            <input type="text"
                                name="leaf_qty"
                                class="form-control"
                                value="<?= @$quantity ?>">

                            <small class="text-danger">
                                <?= @$error['leaf_qty'] ?>
                            </small>

                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mb-3">

                            <label class="form-label">
                                Rate Per Kg
                            </label>

                            <input type="text" name="rate_per_kg"
                                class="form-control"
                               >

                            <small class="text-danger">
                                <?= @$error['rate_per_kg'] ?>
                            </small>

                        </div>


                        <button type="submit"
                            class="btn btn-success w-100">

                            Save Fertilizer Entitlement

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <!-- TABLE SECTION -->
        <div class="col-md-8">

            <div class="card">

                <div class="card-header">
                    <h4>Fertilizer Entitlement List</h4>
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-striped">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Fertilizer Name</th>
                                <th>Leaf Quantity</th>
                                <th>Rate Per 1Kg</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (!empty($items)) { ?>

                                <?php foreach ($items as $item) { ?>

                                    <tr>

                                        <td><?= $item['id'] ?></td>

                                        <td><?= $item['name'] ?></td>

                                        <td>
                                            <?= $item['leaf_qty'] ?> KG
                                        </td>

                                        <td>
                                            <?= $item['rate_per_kg'] ?>
                                        </td>




                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>

                                    <td colspan="5"
                                        class="text-center">

                                        No fertilizer entitlement found

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>
<?php
$content = ob_get_clean();
include '../layout.php';

?>