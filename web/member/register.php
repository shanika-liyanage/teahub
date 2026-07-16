<?php


ob_start();
include '../../init.php'; // Include the initialization file (which includes config.php)
?>

<style>
    .myheading {
        font-size: 30px;
        font-weight: bold;
        color: #cbf025;
    }
</style>

<div class="container-fluid contact py-5 page-header">
    <div class="container ">
        <div class="row g-5 justify-content-center">
            <div class="col wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
                <h3 class="mb-3 text-center py-5 myheading">Register Supplier</h3>

                <?php
                //action eka wenuwt form eke attribute eke
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // Collect form data
                    extract($_POST);
                    $first_name = trim($first_name);
                    $last_name = trim($last_name);
                    $address1 = trim($address1);
                    $address2 = trim($address2);
                    $email = trim($email);
                    $nic = trim($nic);
                    $mobile = trim($mobile);
                    $number = trim($number);
                    $bank = trim($bank);



                    $error = [];
                    if (empty($first_name)) {
                        $error['first_name'] = "first name is required";
                    }
                    if (empty($last_name)) {
                        $error['last_name'] = "last name is required";
                    }
                    if (empty($address1)) {
                        $error['address1'] = "address line 1 is required";
                    }
                    if (empty($address2)) {
                        $error['address2'] = "address line 2 is required";
                    }
                    if (empty($area)) {
                        $error['area'] = "area is required";
                    }
                    if (empty($title)) {
                        $error['title'] = "title is required";
                    }

                    if (empty($email)) {
                        $error['email'] = "mail is required";
                    }
                    if (!empty($email)) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $error['email'] = "Invalid email format";
                        } else {
                            try {
                                $conn = dbConnect();
                                $sql = "SELECT COUNT(*) FROM suppliers WHERE email = :email";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':email', $email);
                                $stmt->execute();
                                $count = $stmt->fetchColumn();

                                if ($count > 0) {
                                    $error['email'] = "Email already exists";
                                }
                            } catch (PDOException $e) {
                                echo "Error: " . $e->getMessage();
                            }
                        }
                    }
                    $password = trim($password);
                    $confirm_password = trim($confirm_password);

                    if (empty($password)) {
                        $error['password'] = "Password is required";
                    } elseif (strlen($password) < 8) {
                        $error['password'] = "Password must contain at least 8 characters";
                    }

                    if (empty($confirm_password)) {
                        $error['confirm_password'] = "Confirm Password is required";
                    } elseif ($password != $confirm_password) {
                        $error['confirm_password'] = "Passwords do not match";
                    }

                    if (empty($nic)) {
                        $error['nic'] = "nic is required";
                    }
                    if (!empty($nic)) {
                        $len = strlen($nic);
                        if ($len != 10 && $len != 12) {
                            $error['nic'] = "Invalid NIC format";
                        } else {
                            if ($len == 10) {
                                $last = strtolower(substr($nic, -1));
                                if ($last != 'v' && $last != 'x') {
                                    $error['nic'] = "Invalid NIC format";
                                }
                            }
                        }
                    }
                    if (empty($mobile)) {
                        $error['mobile'] = "mobile number is required";
                    }
                    if (!empty($mobile)) {

                        // check only digits
                        if (!ctype_digit($mobile)) {
                            $error['mobile'] = "Mobile number must contain only digits";
                        } else {
                            // check length
                            if (strlen($mobile) != 10) {
                                $error['mobile'] = "Mobile number must be 10 digits";
                            } else {
                                // check starts with 0
                                if (substr($mobile, 0, 1) != '0') {
                                    $error['mobile'] = "Mobile number must start with 0";
                                }
                            }
                        }
                    }
                    if (empty($bank)) {
                        $error['bank'] = "bank name is required";
                    }
                    if (empty($branch)) {
                        $error['branch'] = "branch name is required";
                    }

                    if (empty($number)) {
                        $error['number'] = "bank account number is required";
                    }
                    if (!empty($number)) {
                        if (!ctype_digit($number)) {
                            $error['number'] = "Bank account number must contain only digits";
                        }
                    }
                    
                }
                if (empty($error)) {
                    if (!empty($_FILES['book']['name'])) {
                        $file_book = $_FILES['book'];
                        $file_name_book = $file_book['name'];
                        $file_tmp_book = $file_book['tmp_name'];
                        $file_size_book = $file_book['size'];
                        $file_error_book = $file_book['error'];

                        $file_ext_book = explode('.', $file_name_book);
                        $file_ext_book = strtolower(end($file_ext_book));
                        $allowed_ext_book = ['jpg', 'jpeg', 'png'];

                        if (in_array($file_ext_book, $allowed_ext_book)) {
                            if ($file_error_book === 0) {
                                if ($file_size_book <= 2 * 1024 * 1024) {
                                    $file_name_new_book = uniqid('', true) . '.' . $file_ext_book;
                                    $file_destination_book = '../assets/img/uploads/' . $file_name_new_book;
                                    move_uploaded_file($file_tmp_book, $file_destination_book);
                                } else {
                                    $error['book'] = "File size must be less than 2MB";
                                }
                            } else {
                                $error['book'] = "Error uploading file";
                            }
                        } else {
                            $error['book'] = "Invalid file type. Only JPG, JPEG, PNG are allowed.";
                        }
                    } else {
                        $error['book'] = "bank book picture is required";
                    }
                }


                if (empty($error)) {  //plain text eke err ekk ndd kyl blnw
                    if (!empty($_FILES['picture']['name'])) { //file upload krn wdy 2nd step ek ,reuried nam wtrai else eka ena tika dnne nthnm me tika dnmm ati(PROFILE PICTURE EK SELECT KRLD NDD KYL BLNN)
                        $file = $_FILES['picture'];
                        $file_name = $file['name'];
                        $file_tmp = $file['tmp_name'];
                        $file_size = $file['size'];
                        $file_error = $file['error'];

                        $file_ext = explode('.', $file_name);
                        $file_ext = strtolower(end($file_ext));
                        $allowed_ext = ['jpg', 'jpeg', 'png'];

                        if (in_array($file_ext, $allowed_ext)) { //file extension eka check krn wdy
                            if ($file_error === 0) { //file upload krn wdy 3rd step ek ,error ndd kyla check krn wdy
                                if ($file_size <= 2 * 1024 * 1024) { //file size eka check krn wdy,2mb wtrai file size eka nam
                                    $file_name_new = uniqid('', true) . '.' . $file_ext; //file name eka unique krn wdy
                                    $file_destination = '../assets/img/uploads/' . $file_name_new; //file destination eka hadnwa
                                    move_uploaded_file($file_tmp, $file_destination); //file upload krn wdy 4th step ek
                                } else {
                                    $error['picture'] = "File size must be less than 2MB";
                                }
                            } else {
                                $error['picture'] = "Error uploading file";
                            }
                        } else {
                            $error['picture'] = "Invalid file type. Only JPG, JPEG, PNG are allowed.";
                        }
                    } else { //select krl nthnm required kyl pennuw
                        $error['picture'] = "picture is required";
                    }
                }




                if (empty($error)) { //tble ekkt data insert krn wdy

                    try { //data yana eka db ekt
                        $conn = dbConnect(); //conn ntuw on ekk dnnnth plwn x,y...
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $role_id = 6; // Supplier role id

                        $sqlUser = "INSERT INTO users(first_name,last_name,email,password,role_id) VALUES(:first_name,:last_name,:email,:password,:role_id)";

                        $stmtUser = $conn->prepare($sqlUser);

                        $stmtUser->bindParam(':first_name', $first_name);
                        $stmtUser->bindParam(':last_name', $last_name);
                        $stmtUser->bindParam(':email', $email);
                        $stmtUser->bindParam(':password', $hashed_password);
                        $stmtUser->bindParam(':role_id', $role_id);
                        $stmtUser->execute();

                        $user_id = $conn->lastInsertId();

                        $sql = "INSERT INTO suppliers (title,first_name,last_name,address1,address2,area,email,nic,mobile,bank,branch,number,book,picture,user_id) VALUES (:title,:first_name,:last_name,:address1,:address2,:area,:email,:nic,:mobile, :bank,:branch,:number,:book,:picture,:user_id)"; //sql query ekk hadnwa
                        $stmt = $conn->prepare($sql); //statement ekk hadnwa
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':first_name', $first_name);
                        $stmt->bindParam(':last_name', $last_name);
                        $stmt->bindParam(':address1', $address1);

                        $stmt->bindParam(':address2', $address2);
                        $stmt->bindParam(':area', $area);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':nic', $nic);
                        $stmt->bindParam(':mobile', $mobile);
                        $stmt->bindParam(':bank', $bank);
                        $stmt->bindParam(':branch', $branch);
                        $stmt->bindParam(':number', $number);
                        
                        $stmt->bindParam(':book', $file_name_new_book);
                        $stmt->bindParam(':picture', $file_name_new);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $supplier_id = $conn->lastInsertId(); //auto increment id eka ganna eka


                        header('Location:register-success.php'); //header kynne jump krnn kyl

                    } catch (PDOException $e) { //err ek penn eka
                        die("Error: " . $e->getMessage());
                    }
                }

                ?>


                <div class="row justify-content-center">

                    <div class="col">

                        <div class="card shadow-lg border-0 rounded-4">

                            <!-- Card Header -->
                            <div class="card-header bg-success text-white text-center py-4 rounded-top-4">

                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4">

                                <form method="post" enctype="multipart/form-data" novalidate>

                                    <div class="row">

                                        <!-- Title -->
                                        <div class="col-md-3">
                                            <div class="mb-3">

                                                <label for="title" class="form-label">
                                                    Select Title
                                                </label>

                                                <select name="title"
                                                    id="title"
                                                    class="form-select">

                                                    <option value="">--select--</option>

                                                    <option value="mr"
                                                        <?= isset($title) && $title == 'mr' ? 'selected' : '' ?>>
                                                        Mr
                                                    </option>

                                                    <option value="ms"
                                                        <?= isset($title) && $title == 'ms' ? 'selected' : '' ?>>
                                                        Ms
                                                    </option>

                                                    <option value="mrs"
                                                        <?= isset($title) && $title == 'mrs' ? 'selected' : '' ?>>
                                                        Mrs
                                                    </option>

                                                </select>

                                                <span class="text-danger">
                                                    <?= @$error['title'] ?>
                                                </span>

                                            </div>
                                        </div>

                                        <!-- First Name -->
                                        <div class="col-md-9">
                                            <div class="mb-3">

                                                <label for="first_name" class="form-label">
                                                    First Name
                                                </label>

                                                <input type="text"
                                                    class="form-control"
                                                    name="first_name"
                                                    id="first_name"
                                                    value="<?= @$first_name ?>">

                                                <span class="text-danger">
                                                    <?= @$error['first_name'] ?>
                                                </span>

                                            </div>
                                        </div>

                                    </div>

                                    <!-- Last Name -->
                                    <div class="mb-3">

                                        <label for="last_name" class="form-label">
                                            Last Name
                                        </label>

                                        <input type="text"
                                            name="last_name"
                                            id="last_name"
                                            class="form-control"
                                            value="<?= @$last_name ?>">

                                        <span class="text-danger">
                                            <?= @$error['last_name'] ?>
                                        </span>

                                    </div>

                                    <!-- Address 1 -->
                                    <div class="mb-3">

                                        <label for="address1" class="form-label">
                                            Address Line 1
                                        </label>

                                        <textarea name="address1"
                                            id="address1"
                                            class="form-control"
                                            rows="2"><?= @$address1 ?></textarea>

                                        <span class="text-danger">
                                            <?= @$error['address1'] ?>
                                        </span>

                                    </div>

                                    <!-- Address 2 -->
                                    <div class="mb-3">

                                        <label for="address2" class="form-label">
                                            Address Line 2
                                        </label>

                                        <textarea name="address2"
                                            id="address2"
                                            class="form-control"
                                            rows="2"><?= @$address2 ?></textarea>

                                        <span class="text-danger">
                                            <?= @$error['address2'] ?>
                                        </span>

                                    </div>

                                    <!-- Area -->
                                    <div class="mb-3">

                                        <label for="area" class="form-label">
                                            Select Area
                                        </label>

                                        <select name="area"
                                            id="area"
                                            class="form-select">

                                            <option value="">--select--</option>

                                            <option value="Indurupathwila"
                                                <?= isset($area) && $area == 'Indurupathwila' ? 'selected' : '' ?>>
                                                Indurupathwila
                                            </option>

                                            <option value="Ihala Lelwala"
                                                <?= isset($area) && $area == 'Ihala Lelwala' ? 'selected' : '' ?>>
                                                Ihala Lelwala
                                            </option>

                                            <option value="Pahala Lelwala"
                                                <?= isset($area) && $area == 'Pahala Lelwala' ? 'selected' : '' ?>>
                                                Pahala Lelwala
                                            </option>

                                            <option value="Panvila"
                                                <?= isset($area) && $area == 'Panvila' ? 'selected' : '' ?>>
                                                Panvila
                                            </option>

                                            <option value="Kumbalamalahena"
                                                <?= isset($area) && $area == 'Kumbalamalahena' ? 'selected' : '' ?>>
                                                Kumbalamalahena
                                            </option>

                                            <option value="Weihena"
                                                <?= isset($area) && $area == 'Weihena' ? 'selected' : '' ?>>
                                                Weihena
                                            </option>

                                        </select>

                                        <span class="text-danger">
                                            <?= @$error['area'] ?>
                                        </span>

                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">

                                        <label for="email" class="form-label">
                                            E-mail
                                        </label>

                                        <input type="email"
                                            name="email"
                                            id="email"
                                            class="form-control"
                                            value="<?= @$email ?>">

                                        <span class="text-danger">
                                            <?= @$error['email'] ?>
                                        </span>

                                    </div>
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="mb-3">

                                                <label for="password" class="form-label">
                                                    Password
                                                </label>

                                                <input type="password"
                                                    name="password"
                                                    id="password"
                                                    class="form-control">

                                                <span class="text-danger">
                                                    <?= @$error['password'] ?>
                                                </span>

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">

                                                <label for="confirm_password" class="form-label">
                                                    Confirm Password
                                                </label>

                                                <input type="password"
                                                    name="confirm_password"
                                                    id="confirm_password"
                                                    class="form-control">

                                                <span class="text-danger">
                                                    <?= @$error['confirm_password'] ?>
                                                </span>

                                            </div>
                                        </div>

                                    </div>




                                    <!-- NIC -->
                                    <div class="mb-3">

                                        <label for="nic" class="form-label">
                                            NIC
                                        </label>

                                        <input type="text"
                                            name="nic"
                                            id="nic"
                                            class="form-control"
                                            value="<?= @$nic ?>">

                                        <span class="text-danger">
                                            <?= @$error['nic'] ?>
                                        </span>

                                    </div>

                                    <!-- Mobile -->
                                    <div class="mb-3">

                                        <label for="mobile" class="form-label">
                                            Mobile Number
                                        </label>

                                        <input type="text"
                                            name="mobile"
                                            id="mobile"
                                            class="form-control"
                                            value="<?= @$mobile ?>">

                                        <span class="text-danger">
                                            <?= @$error['mobile'] ?>
                                        </span>

                                    </div>
                                     <!-- Bank -->
                                    <div class="mb-3">

                                        <label for="bank" class="form-label">
                                            Select Bank
                                        </label>

                                        <select name="bank"
                                            id="bank"
                                            class="form-select">

                                            <option value="">--select--</option>

                                            <option value="boc"
                                                <?= isset($bank) && $bank == 'boc' ? 'selected' : '' ?>>
                                                Bank of Ceylon (BOC)
                                            </option>

                                            <option value="commercial"
                                                <?= isset($bank) && $bank == 'commercial' ? 'selected' : '' ?>>
                                                Commercial Bank of Ceylon
                                            </option>

                                            <option value="sampath"
                                                <?= isset($bank) && $bank == 'sampath' ? 'selected' : '' ?>>
                                                Sampath Bank
                                            </option>

                                            <option value="hnb"
                                                <?= isset($bank) && $bank == 'hnb' ? 'selected' : '' ?>>
                                                Hatton National Bank (HNB)
                                            </option>

                                            <option value="people"
                                                <?= isset($bank) && $bank == 'people' ? 'selected' : '' ?>>
                                                People's Bank
                                            </option>

                                            <option value="nsb"
                                                <?= isset($bank) && $bank == 'nsb' ? 'selected' : '' ?>>
                                                National Savings Bank (NSB)
                                            </option>

                                        </select>

                                        <span class="text-danger">
                                            <?= @$error['bank'] ?>
                                        </span>

                                    </div>

                                     <!-- Bank branch -->
                                    <div class="mb-3">

                                        <label for="branch" class="form-label">
                                            Bank Branch
                                        </label>

                                        <input type="text"
                                            name="branch"
                                            id="branch"
                                            class="form-control"
                                            value="<?= @$number ?>">

                                        <span class="text-danger">
                                            <?= @$error['branch'] ?>
                                        </span>

                                    </div>

                                    <!-- Bank Account -->
                                    <div class="mb-3">

                                        <label for="number" class="form-label">
                                            Bank Account Number
                                        </label>

                                        <input type="number"
                                            name="number"
                                            id="number"
                                            class="form-control"
                                            value="<?= @$number ?>">

                                        <span class="text-danger">
                                            <?= @$error['number'] ?>
                                        </span>

                                    </div>

                                   

                                    <!-- Bank Book -->
                                    <div class="mb-3">

                                        <label for="book" class="form-label">
                                            Bank Book Photo
                                        </label>

                                        <input type="file"
                                            name="book"
                                            id="book"
                                            class="form-control">

                                        <span class="text-danger">
                                            <?= @$error['book'] ?>
                                        </span>

                                    </div>

                                    <!-- ID Front -->
                                    <div class="mb-4">

                                        <label for="picture" class="form-label">
                                            ID Front View
                                        </label>

                                        <input type="file"
                                            name="picture"
                                            id="picture"
                                            class="form-control">

                                        <span class="text-danger">
                                            <?= @$error['picture'] ?>
                                        </span>

                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center">

                                        <button type="submit"
                                            class="btn btn-success px-5 py-2 rounded-pill shadow-sm">

                                            Submit

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>


            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>