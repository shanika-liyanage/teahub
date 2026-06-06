<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   INSERT FERTILIZER ITEM
------------------------------*/
$error = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // EXTRACT POST DATA
    extract($_POST);

    // TRIM INPUTS
    $name = trim($name);
    $weight = trim($weight);
    $description = trim($description);
    $status = trim($status);

    /* -----------------------------
       VALIDATIONS
    ------------------------------*/

    // Name Validation
    if (empty($name)) {
        $error['name'] = "Fertilizer name is required";
    }

    // Weight Validation
    if (empty($weight)) {
        $error['weight'] = "Weight is required";
    } elseif (!is_numeric($weight)) {
        $error['weight'] = "Weight must be numeric";
    }

    
    // Status Validation
    if (empty($status)) {
        $error['status'] = "Status is required";
    }

    //Min Stock Validation
    if (empty($min_stock)) {
        $error['min_stock'] = "Minimum stock is required";
    } elseif (!is_numeric($min_stock)) {
        $error['min_stock'] = "Minimum stock must be numeric";
    }

     /* -----------------------------
       CHECK IF NAME ALREADY EXISTS

    /* -----------------------------
       INSERT DATA
    ------------------------------*/
    if (empty($error)) {

        $sql = "INSERT INTO fertilizer_items
                (name, weight, discription, status, min_stock)
                VALUES
                (:name, :weight, :description, :status, :min_stock)";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':min_stock', $min_stock);

        $stmt->execute();

        $success = "Fertilizer item added successfully";

        // CLEAR FORM
        $name = '';
        $weight = '';
        $description = '';
        $min_stock = '';
        $status = '';
    }
}

/* -----------------------------
   LOAD FERTILIZER ITEMS
------------------------------*/
$sql = "SELECT * FROM fertilizer_items ORDER BY id DESC";
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
                    <h4>Add Fertilizer Item</h4>
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

                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="<?= @$name ?>">

                            <small class="text-danger">
                                <?= @$error['name'] ?>
                            </small>

                        </div>

                        <!-- WEIGHT -->
                        
                        <div class="mb-3">

                            <label class="form-label">
                                Fertilizer Dosage for 1Kg for Tea Leaves
                            </label>

                            <input type="text"
                                   name="weight"
                                   class="form-control"
                                   value="<?= @$weight ?>">

                            <small class="text-danger">
                                <?= @$error['weight'] ?>
                            </small>

                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mb-3">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="3"><?= @$description ?></textarea>

                            <small class="text-danger">
                                <?= @$error['description'] ?>
                            </small>

                        </div>
                        <div>
                            <label for="">Min Stock (Kg)</label>
                            <input type="number" name="min_stock" class="form-control" value="<?= @$min_stock ?>">
                            <small class="text-danger">
                                <?= @$error['min_stock'] ?> 
                            </small>
                        </div>


                        <!-- STATUS -->
                        <div class="mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select name="status" class="form-control">

                                <option value="">
                                    Select Status
                                </option>

                                <option value="Active"
                                    <?= (@$status == 'Active') ? 'selected' : '' ?>>

                                    Active

                                </option>

                                <option value="Inactive"
                                    <?= (@$status == 'Inactive') ? 'selected' : '' ?>>

                                    Inactive

                                </option>

                            </select>

                            <small class="text-danger">
                                <?= @$error['status'] ?>
                            </small>

                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">

                            Save Fertilizer

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <!-- TABLE SECTION -->
        <div class="col-md-8">

            <div class="card">

                <div class="card-header">
                    <h4>Fertilizer Item List</h4>
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-striped">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Fertilizer Issue for 1Kg</th>
                                <th>Description</th>
                                <th>Min Stock</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (!empty($items)) { ?>

                                <?php foreach ($items as $item) { ?>

                                    <tr>

                                        <td><?= $item['id'] ?></td>

                                        <td><?= $item['name'] ?></td>

                                        <td>
                                            <?= $item['weight'] ?> KG
                                        </td>

                                        <td>
                                            <?= $item['discription'] ?>
                                        </td>
                                        <td>
                                            <?= $item['min_stock'] ?>
                                        </td>

                                        <td>

                                            <?php if ($item['status'] == 'Active') { ?>

                                                <span class="badge bg-success">
                                                    Active
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-danger">
                                                    Inactive
                                                </span>

                                            <?php } ?>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>

                                    <td colspan="5"
                                        class="text-center">

                                        No fertilizer items found

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