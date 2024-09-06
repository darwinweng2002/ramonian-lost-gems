<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * FROM `inquiry_list` WHERE id = '{$_GET['id']}'");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
        // Update the status of the inquiry
        $conn->query("UPDATE `inquiry_list` SET `status` = 1 WHERE `id` = '{$id}'");
    } else {
        echo '<script>alert("Inquiry ID is not valid."); location.replace("./?page=inquiries")</script>';
    }
} else {
    echo '<script>alert("Inquiry ID is Required."); location.replace("./?page=inquiries")</script>';
}
?>

<div class="row mt-lg-n4 mt-md-n4 justify-content-center">
    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
        <div class="card rounded-0">
            <div class="card-body">
                <div class="container-fluid mt-4">
				<img src="<?= validate_image($image_path ?? "") ?>" alt="<?= $title ?? "" ?>">
                    <h2>From: <b><?= htmlspecialchars($fullname ?? "") ?></b></h2>
                    <dl class="row mb-0">
                        <dt class="col-auto pe-3"><b>Email:</b></dt>
                        <dd><a href="mailto:<?= htmlspecialchars($email ?? "unknown@mail.com") ?>"><?= htmlspecialchars($email ?? "N/A") ?></a></dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-auto pe-3"><b>Contact No.:</b></dt>
                        <dd><?= htmlspecialchars($contact ?? "") ?></dd>
                    </dl>
                    <div><?= isset($message) ? htmlspecialchars_decode(str_replace("\n", "<br>", $message)) : "" ?></div>
                    <!-- Display image if exists -->
                    <?php if (isset($image) && !empty($image)): ?>
                        <div class="mt-3">
                            <img src="<?= validate_image($image) ?>" alt="Inquiry Image" class="img-fluid">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer py-1 text-center">
                <button class="btn btn-danger btn-sm bg-gradient-danger rounded-0" type="button" id="delete_data"><i class="fa fa-trash"></i> Delete</button>
                <a class="btn btn-light btn-sm bg-gradient-light border rounded-0" href="./?page=inquiries"><i class="fa fa-angle-left"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('#delete_data').click(function(){
            _conf("Are you sure to delete this inquiry permanently?", "delete_inquiry", ["<?= isset($id) ? $id :'' ?>"]);
        });
    });

    function delete_inquiry($id){
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=delete_inquiry",
            method: "POST",
            data: { id: $id },
            dataType: "json",
            error: err => {
                console.log(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.replace("./?page=inquiries");
                } else {
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                }
            }
        });
    }
</script>
