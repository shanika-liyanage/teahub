<?php


ob_start();
include '../../init.php'; // Include the initialization file (which includes config.php)
?>


<div class="container-fluid contact py-5 page-header">
    <div class="container ">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
                <h3 class="mb-3 text-center py-5">Register Customer</h3>

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
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

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
                            $error['picture'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
                        }
                    } else { //select krl nthnm required kyl pennuw
                        $error['picture'] = "picture is required";
                    }
                }




                if (empty($error)) { //tble ekkt data insert krn wdy

                    try { //data yana eka db ekt
                        $conn = dbConnect(); //conn ntuw on ekk dnnnth plwn x,y...
                        $sql = "INSERT INTO suppliers (title,first_name,last_name,address1,address2,area,email,nic,mobile,area,gender,picture) VALUES (:title,:first_name,:last_name,:address1,:address1,:area,:email,:nic,:mobile,:area,:gender,:picture)"; //sql query ekk hadnwa
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


                        $stmt->bindParam(':picture', $file_name_new);
                        $stmt->execute();
                        $supplier_id = $conn->lastInsertId(); //auto increment id eka ganna eka


                        header('Location:register-success.php'); //header kynne jump krnn kyl

                    } catch (PDOException $e) { //err ek penn eka
                        die("Error: " . $e->getMessage());
                    }
                }

                ?>

                <form method="post" enctype="multipart/form-data" novalidate> <!--enctype="multipart/form-data" file upload krn wdy 1st step ek-->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="title" class="form-label">Select Title</label>
                                <select name="title" id="title" class="form-control bg-light">
                                    <option value="">--select--</option>
                                    <option value="mr" <?= isset($title) && $title == 'mr' ? 'selected' : '' ?>>Mr</option>
                                    <option value="ms" <?= isset($title) && $title == 'ms' ? 'selected' : '' ?>>Ms</option>
                                    <option value="mrs" <?= isset($title) && $title == 'mrs' ? 'selected' : '' ?>>Mrs</option>
                                </select>
                                <span class="text-danger">
                                    <?= @$error['title'] ?>
                                </span>

                            </div>

                        </div>
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" value="<?= @$first_name ?>">
                                <span class="text-danger">
                                    <?= @$error['first_name'] //@ dnmama errr eka load wenn kln penna eka nwtenw
                                    ?>
                                </span>
                            </div>

                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" value="<?= @$last_name ?>">
                        <span class="text-danger">
                            <?= @$error['last_name'] ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <label for="address1" class="form-label">Address Line 1</label>
                        <textarea name="address1" id="address1" class="form-control"><?= @$address1 ?></textarea>
                        <span class="text-danger">
                            <?= @$error['address1'] ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <label for="address2" class="form-label">Address Line 2</label>
                        <textarea name="address2" id="address2" class="form-control"><?= @$address2 ?></textarea>
                        <span class="text-danger">
                            <?= @$error['address2'] ?>
                        </span>

                    </div>
                 

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= @$email ?>">
                        <span class="text-danger">
                            <?= @$error['email'] ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <label for="nic" class="form-label">NIC</label>
                        <input type="nic" name="nic" id="nic" class="form-control" value="<?= @$nic ?>">
                        <span class="text-danger">
                            <?= @$error['nic'] ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="mobile" name="mobile" id="mobile" class="form-control" value="<?= @$mobile ?>">
                        <span class="text-danger">
                            <?= @$error['mobile'] ?>
                        </span>
                    </div>


                    <div class="mb-3">
                        <label for="picture" class="form-label ">ID Front View</label>
                        <input type="file" name="picture" id="picture" class="form-control bg-light">
                        <span class="text-danger"><?= @$error['picture'] ?></span>
                    </div>


                    <button type="submit" class="btn btn-dark  py-2 px-2 animated zoomIn">Submit</button>
                </form>

            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?>