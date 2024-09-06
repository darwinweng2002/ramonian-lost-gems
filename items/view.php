<?php
// Use include_once to avoid redeclaring functions
include_once 'config.php';

// Fetch item details
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $item_id = $_GET['id'];
    
    $qry = $conn->query("
        SELECT *, 
            COALESCE((SELECT `name` FROM `category_list` WHERE `category_list`.`id` = `item_list`.`category_id`), 'N/A') AS `category` 
        FROM `item_list` 
        WHERE id = '{$item_id}'
    ");
    
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    } else {
        echo '<script>alert("Item ID is not valid."); location.replace("./?page=items")</script>';
        exit;
    }
} else {
    echo '<script>alert("Item ID is Required."); location.replace("./?page=items")</script>';
    exit;
}
?>

<style>
    .lf-image {
        width: 400px;
        height: 300px;
        margin: 1em auto;
        background: #000;
        box-shadow: 1px 1px 10px #00000069;
    }
    .lf-image > img {
        width: 100%;
        height: 100%;
        object-fit: scale-down;
        object-position: center center;
    }
    .btn a {
        color: #fff;
        padding: 6px 12px;
        text-decoration: none; /* Ensure text decoration is removed */
    }
    .btn-light-green {
        background-color: #28a745; /* Light green color */
        border-color: #28a745;
        border-radius: 0; /* Remove border radius */
    }
    .btn-light-green:hover {
        background-color: #218838; /* Darker green color for hover */
        border-color: #1e7e34;
    }
    .btn-primary {
        background-color: #0d6efd; /* Existing color */
        border-color: #0d6efd;
        border-radius: 0; /* Remove border radius */
    }
    .btn-primary a {
        color: #fff;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .center-buttons {
        text-align: center;
        margin-top: 20px;
    }
</style>

<div class="row mt-lg-n4 mt-md-n4 justify-content-center">
    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
        <div class="card rounded-0">
            <div class="card-body">
                <div class="container-fluid mt-4">
                    <div class="lf-image">
                        <img src="<?= validate_image($image_path ?? "") ?>" alt="<?= htmlspecialchars($title ?? "") ?>">
                    </div>
                    <h2 class="titleTxt"><?= htmlspecialchars($title ?? "") ?> <span>| <?= htmlspecialchars($category ?? "") ?></span></h2>
                    <dl>
                        <dt class="text-muted">Founder Name</dt>
                        <dd class="ps-4"><?= htmlspecialchars($fullname ?? "") ?></dd>
                        <dt class="text-muted">Contact No.</dt>
                        <dd class="ps-4"><?= htmlspecialchars($contact ?? "") ?></dd>
                        <dt class="text-muted">Description</dt>
                        <dd class="ps-4"><?= isset($description) ? str_replace("\n", "<br>", htmlspecialchars($description)) : "" ?></dd>
                        <dt class="text-muted">Landmark</dt>
                        <dd class="ps-4"><?= htmlspecialchars($landmark ?? "N/A") ?></dd>
                        <dt class="text-muted">Time Found</dt>
                        <dd class="ps-4"><?= isset($time_found) ? date('F j, Y, g:i a', strtotime($time_found)) : "N/A" ?></dd>
                        <dt class="text-muted">Status</dt>
                        <dd class="ps-4">
                            <?php if ($status == 1): ?>
                                <span class="badge bg-primary px-3 rounded-pill">Published</span>
                            <?php elseif ($status == 2): ?>
                                <span class="badge bg-success px-3 rounded-pill">Claimed</span>
                            <?php elseif ($status == 3): ?>
                                <span class="badge bg-secondary px-3 rounded-pill">Surrendered</span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-3 rounded-pill">Pending</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                    <div class="center-buttons">
                        <div class="btn btn-light-green">
                            <a href="http://localhost/lostgemramonian/user_members/claim_request.php?item_id=<?= htmlspecialchars($item_id) ?>">Request to Claim</a>
                        </div>
                        <div class="btn btn-primary">
                            <a href="./?page=items">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
