<?php
ob_start();
include '../../init.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title "><strong>Tea Leaves Supplier List</strong></h3>
                <br>
                <br>
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
                $sql = "SELECT id,title,first_name,last_name,mobile,verify FROM suppliers ORDER BY id DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                //$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Mobile Number</th>
                            <th>Action</th>
                            <th>Verify</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n=1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //eliyt ganna kyl kynne fetch wln
                            //foreach ($data as $row) { //eliyt ganna kyl kynne foreach wln
                        ?>
                            <tr>
                                <td><?= $n++ ?></td>
                                <td><?= ucfirst($row['title']) ?>. <?= $row['first_name'] ?> <?= $row['last_name'] ?></td>
                                <td><?= $row['mobile'] ?></td>
                                <td>
                                    <form action="view.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="action" value="view" class="btn btn-warning sm">View</button>
                                    </form>
                                    <div class="mb-1"></div>

                                    <form action="delete.php" method="POST" onsubmit="return confirmDelete();">
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-danger sm">Delete</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="verify.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                        <select name="status" class="form-control">
                                            <option value="pending" <?= ($row['verify'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                            <option value="verified" <?= ($row['verify'] == 'verified') ? 'selected' : '' ?>>Verified</option>
                                            <option value="rejected" <?= ($row['verify'] == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                            </select>
                                            <button type="submit" name="action" value="verify" class="btn btn-success sm mt-2">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
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