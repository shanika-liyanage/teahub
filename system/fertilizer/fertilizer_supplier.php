<?php
ob_start();
include '../../init.php';
$conn = dbConnect();

/* -----------------------------
   LOAD FERTILIZER ITEMS
------------------------------*/
$sql = "SELECT * FROM fertilizer_items
        WHERE status='Active'
        ORDER BY name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$fertilizers = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* -----------------------------
   INSERT SUPPLIER
------------------------------*/
$error = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    extract($_POST);

    // TRIM VALUES
    $company_name = trim($company_name);
    $address = trim($address);
    $email = trim($email);
    $telephone = trim($telephone);
    $contact_person_name = trim($contact_person_name);
    $contact_person_mobile = trim($contact_person_mobile);

    /* -----------------------------
       VALIDATIONS
    ------------------------------*/

    if (empty($company_name)) {
        $error['company_name'] = "Company name is required";
    }

    if (empty($address)) {
        $error['address'] = "Address is required";
    }

    if (empty($email)) {
        $error['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = "Invalid email";
    }

    if (empty($telephone)) {
        $error['telephone'] = "Telephone number is required";
    }

    if (empty($contact_person_name)) {
        $error['contact_person_name'] = "Contact person name is required";
    }

    if (empty($contact_person_mobile)) {
        $error['contact_person_mobile'] = "Mobile number is required";
    }

    if (empty($_POST['products'])) {
        $error['products'] = "Select at least one product";
    }
    if(!empty($company_name)){
        $sql = "SELECT COUNT(*) FROM fertilizer_suppliers
                WHERE company_name = :company_name";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error['company_name'] = "Company name already exists";
        }
    }

    /* -----------------------------
       INSERT DATA
    ------------------------------*/
    if (empty($error)) {

        // INSERT SUPPLIER
        $sql = "INSERT INTO fertilizer_suppliers
                (
                    company_name,
                    address,
                    email,
                    telephone,
                    contact_person_name,
                    contact_person_mobile
                )
                VALUES
                (
                    :company_name,
                    :address,
                    :email,
                    :telephone,
                    :contact_person_name,
                    :contact_person_mobile
                )";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':contact_person_name', $contact_person_name);
        $stmt->bindParam(':contact_person_mobile', $contact_person_mobile);

        $stmt->execute();

        // LAST INSERT ID
        $supplier_id = $conn->lastInsertId();

        // INSERT SUPPLIER PRODUCTS
        foreach ($_POST['products'] as $product_id) {

            $sql = "INSERT INTO fertilizer_supplier_products
                    (
                        supplier_id,
                        fertilizer_item_id
                    )
                    VALUES
                    (
                        :supplier_id,
                        :fertilizer_item_id
                    )";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':supplier_id', $supplier_id);
            $stmt->bindParam(':fertilizer_item_id', $product_id);

            $stmt->execute();
            unset($_POST);
        }

        $success = "Supplier registered successfully";

        // CLEAR VALUES
        $company_name = '';
        $address = '';
        $email = '';
        $telephone = '';
        $contact_person_name = '';
        $contact_person_mobile = '';
    }
}


/* -----------------------------
   LOAD SUPPLIERS
------------------------------*/
$sql = "SELECT * FROM fertilizer_suppliers
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">

    <div class="row">

        <!-- FORM SECTION -->
        <div class="col-md-4">
            <a href="fertilizer_supplier.php" class="btn btn-info mb-2">New Supplier</a>

            <div class="card">

                <div class="card-header">
                    <h4>Register Supplier</h4>
                </div>

                <div class="card-body">

                    <?php if (!empty($success)) { ?>

                        <div class="alert alert-success">
                            <?= $success ?>
                        </div>

                    <?php } ?>

                    <form method="POST">

                        <!-- COMPANY NAME -->
                        <div class="mb-3">

                            <label class="form-label">
                                Company Name
                            </label>

                            <input type="text"
                                   name="company_name"
                                   class="form-control"
                                   value="<?= @$company_name ?>">

                            <small class="text-danger">
                                <?= @$error['company_name'] ?>
                            </small>

                        </div>

                        <!-- ADDRESS -->
                        <div class="mb-3">

                            <label class="form-label">
                                Address
                            </label>

                            <textarea name="address"
                                      class="form-control"
                                      rows="3"><?= @$address ?></textarea>

                            <small class="text-danger">
                                <?= @$error['address'] ?>
                            </small>

                        </div>

                        <!-- EMAIL -->
                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="text"
                                   name="email"
                                   class="form-control"
                                   value="<?= @$email ?>">

                            <small class="text-danger">
                                <?= @$error['email'] ?>
                            </small>

                        </div>

                        <!-- TELEPHONE -->
                        <div class="mb-3">

                            <label class="form-label">
                                Telephone Number
                            </label>

                            <input type="text"
                                   name="telephone"
                                   class="form-control"
                                   value="<?= @$telephone ?>">

                            <small class="text-danger">
                                <?= @$error['telephone'] ?>
                            </small>

                        </div>

                        <!-- CONTACT PERSON -->
                        <div class="mb-3">

                            <label class="form-label">
                                Contact Person Name
                            </label>

                            <input type="text"
                                   name="contact_person_name"
                                   class="form-control"
                                   value="<?= @$contact_person_name ?>">

                            <small class="text-danger">
                                <?= @$error['contact_person_name'] ?>
                            </small>

                        </div>

                        <!-- MOBILE -->
                        <div class="mb-3">

                            <label class="form-label">
                                Contact Person Mobile
                            </label>

                            <input type="text"
                                   name="contact_person_mobile"
                                   class="form-control"
                                   value="<?= @$contact_person_mobile ?>">

                            <small class="text-danger">
                                <?= @$error['contact_person_mobile'] ?>
                            </small>

                        </div>

                        <!-- PRODUCTS -->
                        <div class="mb-3">

                            <label class="form-label">
                                Supplier Products
                            </label>

                            <?php foreach ($fertilizers as $fertilizer) { ?>

                                <div class="form-check">

                                    <input type="checkbox"
                                           name="products[]"
                                           value="<?= $fertilizer['id'] ?>"
                                           class="form-check-input">

                                    <label class="form-check-label">

                                        <?= $fertilizer['name'] ?>

                                    </label>

                                </div>

                            <?php } ?>

                            <small class="text-danger">
                                <?= @$error['products'] ?>
                            </small>

                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">

                            Save Supplier

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- TABLE SECTION -->
        <div class="col-md-8">

            <div class="card">

                <div class="card-header">
                    <h4>Supplier List</h4>
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-striped">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Company</th>
                                <th>Telephone</th>
                                <th>Contact Person</th>
                                <th>Mobile</th>
                                <th>Products</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($suppliers as $supplier) { ?>

                                <tr>

                                    <td><?= $supplier['id'] ?></td>

                                    <td><?= $supplier['company_name'] ?></td>

                                    <td><?= $supplier['telephone'] ?></td>

                                    <td>
                                        <?= $supplier['contact_person_name'] ?>
                                    </td>

                                    <td>
                                        <?= $supplier['contact_person_mobile'] ?>
                                    </td>

                                    <td>

                                        <?php

                                        $sql = "SELECT fi.name
                                                FROM fertilizer_supplier_products fsp
                                                INNER JOIN fertilizer_items fi
                                                ON fi.id = fsp.fertilizer_item_id
                                                WHERE fsp.supplier_id = :supplier_id";

                                        $stmt = $conn->prepare($sql);

                                        $stmt->bindParam(':supplier_id', $supplier['id']);

                                        $stmt->execute();

                                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($products as $product) {

                                            echo '<span class="badge bg-success me-1">'
                                                . $product['name'] .
                                                '</span>';
                                        }

                                        ?>

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