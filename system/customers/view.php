<?php
ob_start();
include '../../init.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customer List</h3>
                <br>
                <br>

                <a href="create.php" class="btn btn-primary mb-2">Add Customer</a>

                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-0">
                <?php
                $conn = dbConnect();
                //fetch all products from the database
                $stmt = $conn->prepare("SELECT * FROM customers");
                $stmt->execute();
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC); //associate aray ekak wdyt data varible ekt dl denw tble eke tyn tika
                ?>


                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Address</th>
                            <th>Action</th>
                            <th>Verify</th>


                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($customers as $customer) { ?>
                            <tr>
                                <td><?= $customer['id'] ?></td>
                                <td><?= $customer['name'] ?></td>
                                <td><?= $customer['mobile'] ?></td>
                                <td><?= $customer['address'] ?></td>
                                <!--<td><a href="edit.php?id=<?= $customer['id']; ?>" class = "btn btn-warning sm">Edit</a></td> UNSECUR NISA MHM YWNNE NA-->
                                <td>
                                    <form action="edit.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $customer['id']; ?>">
                                        <button type="submit" name="action" value="edit" class="btn btn-warning sm">Edit</button>

                                    </form>
                                    <form action="delete.php" method="POST" onsubmit="return confirmDelete();">
                                        <input type="hidden" name="id" value="<?= $customer['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-danger sm">Delete</button>

                                    </form>
                                </td>
                                 <td>
                                    <form action="edit.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $customer['id']; ?>">
                                        <button type="submit" name="action" value="edit" class="btn btn-warning sm">pending</button>

                                    </form>
                                    <form action="delete.php" method="POST" onsubmit="return confirmDelete();">
                                        <input type="hidden" name="id" value="<?= $customer['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-danger sm">reject</button>

                                    </form>
                                </td>


                            </tr>
                        <?php } ?>
                    </tbody>




                </table>

            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>




<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this customer?");
    }
</script>




<?php
$content = ob_get_clean();
include '../layout.php';
?>