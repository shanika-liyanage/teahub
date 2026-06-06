
<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   LOAD TEA COLLECTIONS
------------------------------*/
$sql = "SELECT tc.*,
               s.title,
               s.first_name,
               s.last_name
        FROM tea_collection tc
        INNER JOIN suppliers s
        ON tc.supplier_id = s.id
        ORDER BY tc.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* -----------------------------
   FORM SUBMIT
------------------------------*/
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $collection_id           = trim($_POST['collection_id']);
    $water_deduction         = trim($_POST['water_deduction']);
    $mature_leaf_deduction   = trim($_POST['mature_leaf_deduction']);
    $other_deduction         = trim($_POST['other_deduction']);
    $deduction_note          = trim($_POST['deduction_note']);


    /* -----------------------------
       VALIDATION
    ------------------------------*/
    if (empty($collection_id)) {
        $error['collection_id'] = 'Tea collection is required';
    }

    if ($water_deduction == '') {
        $water_deduction = 0;
    }

    if ($mature_leaf_deduction == '') {
        $mature_leaf_deduction = 0;
    }

    if ($other_deduction == '') {
        $other_deduction = 0;
    }


    /* -----------------------------
       GET COLLECTION DATA
    ------------------------------*/
    if (empty($error)) {

        $sql = "SELECT * FROM tea_collection WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $collection_id);
        $stmt->execute();

        $collection = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($collection) {

            $gross_weight = $collection['gross_weight'];
            $bag_weight   = $collection['total_bag_weight'];
            $box_weight   = $collection['total_box_weight'];


            /* -----------------------------
               TOTAL DEDUCTION
            ------------------------------*/
            $total_deduction =
                $water_deduction +
                $mature_leaf_deduction +
                $other_deduction +
                $bag_weight +
                $box_weight;


            /* -----------------------------
               NET WEIGHT
            ------------------------------*/
            $net_weight = $gross_weight - $total_deduction;

            if ($net_weight < 0) {
                $net_weight = 0;
            }


            /* -----------------------------
               UPDATE COLLECTION TABLE
            ------------------------------*/
            $sql = "UPDATE tea_collection
                    SET water_deduction = :water_deduction,
                        mature_leaf_deduction = :mature_leaf_deduction,
                        other_deduction = :other_deduction,
                        total_deduction = :total_deduction,
                        net_weight = :net_weight,
                        deduction_note = :deduction_note
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':water_deduction', $water_deduction);
            $stmt->bindParam(':mature_leaf_deduction', $mature_leaf_deduction);
            $stmt->bindParam(':other_deduction', $other_deduction);
            $stmt->bindParam(':total_deduction', $total_deduction);
            $stmt->bindParam(':net_weight', $net_weight);
            $stmt->bindParam(':deduction_note', $deduction_note);
            $stmt->bindParam(':id', $collection_id);

            $stmt->execute();


            /* -----------------------------
               REDIRECT
            ------------------------------*/
            header('Location: index.php?success=added');
            exit;
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">

        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">Add Tea Deductions</h3>
            </div>


            <form method="POST">

                <div class="card-body">

                    <div class="row">

                        <!-- COLLECTION -->
                        <div class="col-md-6">
                            <div class="form-group">

                                <label>Tea Collection</label>

                                <select name="collection_id" class="form-control">

                                    <option value="">-- Select Collection --</option>

                                    <?php foreach ($collections as $row): ?>

                                        <option value="<?= $row['id'] ?>">

                                            <?= ucfirst($row['title']) ?>.
                                            <?= $row['first_name'] ?>
                                            <?= $row['last_name'] ?>
                                            |
                                            <?= $row['collection_date'] ?>
                                            |
                                            <?= number_format($row['gross_weight'], 2) ?> Kg

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <small class="text-danger">
                                    <?= $error['collection_id'] ?? '' ?>
                                </small>

                            </div>
                        </div>


                        <!-- WATER DEDUCTION -->
                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Water Deduction (Kg)</label>

                                <input type="number"
                                       step="0.01"
                                       name="water_deduction"
                                       class="form-control"
                                       value="<?= $_POST['water_deduction'] ?? '' ?>">

                            </div>
                        </div>


                        <!-- MATURE LEAF -->
                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Mature Leaf Deduction (Kg)</label>

                                <input type="number"
                                       step="0.01"
                                       name="mature_leaf_deduction"
                                       class="form-control"
                                       value="<?= $_POST['mature_leaf_deduction'] ?? '' ?>">

                            </div>
                        </div>


                        <!-- OTHER -->
                        <div class="col-md-4">
                            <div class="form-group">

                                <label>Other Deduction (Kg)</label>

                                <input type="number"
                                       step="0.01"
                                       name="other_deduction"
                                       class="form-control"
                                       value="<?= $_POST['other_deduction'] ?? '' ?>">

                            </div>
                        </div>


                        <!-- NOTE -->
                        <div class="col-md-12">
                            <div class="form-group">

                                <label>Deduction Note</label>

                                <textarea name="deduction_note"
                                          class="form-control"
                                          rows="3"><?= $_POST['deduction_note'] ?? '' ?></textarea>

                            </div>
                        </div>

                    </div>

                </div>


                <div class="card-footer">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Deductions
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>


