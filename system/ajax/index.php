<?php
ob_start();
include '../../init.php';
?>
<h3>Product List</h3>


<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
    Add Product
</button>


<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Sku</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="table-data">
        <!-- Data loaded by AJAX -->
    </tbody>
</table>
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">


            <div class="modal-header">
                <h5>Add Product</h5>
            </div>


            <div class="modal-body">
                <input type="text" id="name" class="form-control mb-1" placeholder="Name">
                <small class="text-danger" id="error_name"></small>


                <input type="number" id="price" class="form-control mb-1" placeholder="Price">
                <small class="text-danger" id="error_price"></small>


                <input type="text" id="sku" class="form-control mb-1" placeholder="SKU">
                <small class="text-danger" id="error_sku"></small>


                <input type="file" id="image" class="form-control mb-1">
                <small class="text-danger" id="error_image"></small>
            </div>


            <div class="modal-footer">
                <button class="btn btn-success" id="saveBtn">Save</button>
            </div>


        </div>
    </div>
</div>
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">


            <div class="modal-header">
                <h5>Edit Product</h5>
            </div>


            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <input type="text" id="edit_name" class="form-control mb-1">
                <small class="text-danger" id="edit_error_name"></small>


                <input type="number" id="edit_price" class="form-control mb-1">
                <small class="text-danger" id="edit_error_price"></small>


                <input type="text" id="edit_sku" class="form-control mb-1">
                <small class="text-danger" id="edit_error_sku"></small>


                <!-- Current Image -->
                <div class="mb-2">
                    <label>Current Image</label><br>
                    <img id="current_image" src="" width="80" class="img-thumbnail">
                </div>
                <input type="file" id="edit_image" class="form-control mb-1">
                <!-- Preview New Image -->
                <img id="preview_image" src="" width="80" class="mt-2 d-none">
                <small class="text-danger" id="edit_error_image"></small>
            </div>


            <div class="modal-footer">
                <button class="btn btn-primary" id="updateBtn">Update</button>
            </div>


        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include '../layout.php';
?>
<script>
    $(document).ready(function() {


        loadData();


        function loadData() {
            $.ajax({
                url: "fetch.php",
                method: "GET",
                success: function(data) {
                    $("#table-data").html(data);
                }
            });
        }


        // INSERT
        $("#saveBtn").click(function() {


            $("#error_name").text('');
            $("#error_price").text('');
            $("#error_sku").text('');
            $("#error_image").text('');

//class eken object ekk hdgnnw
            let formData = new FormData();


            formData.append("name", $("#name").val());
            formData.append("price", $("#price").val());
            formData.append("sku", $("#sku").val());


            let file = $("#image")[0].files[0];
            formData.append("image", file);


            $.ajax({
                url: "insert.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,


                success: function(response) {

//result eka json string ekk
                    let res = JSON.parse(response);


                    if (res.status === "error") {


                        if (res.errors.name) $("#error_name").text(res.errors.name);
                        if (res.errors.price) $("#error_price").text(res.errors.price);
                        if (res.errors.sku) $("#error_sku").text(res.errors.sku);
                        if (res.errors.image) $("#error_image").text(res.errors.image);


                    } else {


                        $("#addModal").modal('hide');


                        $("#name").val('');
                        $("#price").val('');
                        $("#sku").val('');
                        $("#image").val('');


                        loadData();
                    }
                }
            });


        });


        // DELETE
        $(document).on("click", ".deleteBtn", function() {
            if (confirm("Delete this record?")) {


                $.post("delete.php", {
                    id: $(this).data("id")
                }, function() {
                    loadData();
                });
            }
        });


        // LOAD EDIT DATA
        $(document).on("click", ".editBtn", function() {


            let id = $(this).data("id");


            $.get("get.php", {
                id: id
            }, function(data) {


                let row = JSON.parse(data);


                $("#edit_id").val(row.id);
                $("#edit_name").val(row.name);
                $("#edit_price").val(row.price);
                $("#edit_sku").val(row.sku);


                // ✅ SET CURRENT IMAGE
                if (row.image) {
                    $("#current_image").attr("src", "products/" + row.image);
                } else {
                    $("#current_image").attr("src", "");
                }


                // reset preview
                $("#preview_image").attr("src", "").addClass("d-none");
                $("#edit_image").val('');


                $("#editModal").modal('show');
            });


        });


        // UPDATE
        $("#updateBtn").click(function() {


            let formData = new FormData();


            formData.append("id", $("#edit_id").val());
            formData.append("name", $("#edit_name").val());
            formData.append("price", $("#edit_price").val());
            formData.append("sku", $("#edit_sku").val());


            let file = $("#edit_image")[0].files[0];
            if (file) {
                formData.append("image", file);
            }


            $.ajax({
                url: "update.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,


                success: function(response) {


                    let res = JSON.parse(response);


                    if (res.status === "error") {


                        $("#edit_error_name").text(res.errors.name || '');
                        $("#edit_error_price").text(res.errors.price || '');
                        $("#edit_error_sku").text(res.errors.sku || '');


                    } else {


                        $("#editModal").modal('hide');
                        loadData();
                    }
                }
            });


        });
        $("#edit_image").change(function() {


            let file = this.files[0];


            if (file) {
                let reader = new FileReader();


                reader.onload = function(e) {
                    $("#preview_image")
                        .attr("src", e.target.result)
                        .removeClass("d-none");
                }


                reader.readAsDataURL(file);
            }
        });


    });
</script>