<?php
include '../../config.php';

if (isset($_GET['claim_id'])) {
    $claim_id = intval($_GET['claim_id']);

    // Fetch claim details from the database, including user details
    $qry = $conn->query("
        SELECT 
            c.id AS claim_id, 
            c.additional_info, 
            i.title AS item_title, 
            cl.name AS category_name, 
            i.fullname, 
            i.contact, 
            i.description, 
            i.image_path,
            m.email AS user_email,
            m.course AS user_course,
            m.year AS user_year,
            m.section AS user_section
        FROM 
            claims c 
        INNER JOIN 
            item_list i ON c.item_id = i.id 
        INNER JOIN
            category_list cl ON i.category_id = cl.id 
        INNER JOIN
            user_member m ON c.user_id = m.id
        WHERE 
            c.id = $claim_id
    ");

    if ($qry && $qry->num_rows > 0) {
        $claim = $qry->fetch_assoc();
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <?php require_once('../inc/header.php'); ?>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>View Claim Details</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    color: #333;
                }
                .container {
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 8px;
                    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
                }
                h2 {
                    color: #2c3e50;
                    margin-bottom: 20px;
                    font-size: 24px;
                    border-bottom: 2px solid #2c3e50;
                    padding-bottom: 10px;
                }
                .claim-details p {
                    font-size: 16px;
                    margin: 12px 0;
                    line-height: 1.6;
                }
                .claim-details p strong {
                    color: #2c3e50;
                }
                .lf-image img {
                    width: 100%; /* Ensure the image fits within its container */
                    max-width: 500px; /* Set a maximum width for images */
                    height: auto; /* Maintain aspect ratio */
                    border-radius: 8px; /* Optional: Add rounded corners */
                    object-fit: cover; /* Ensure the image covers the container */
                    margin-top: 20px;
                }
                .claimer-details {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 2px solid #e9e9e9;
                }
                .claimer-details h3 {
                    margin-bottom: 15px;
                    font-size: 20px;
                    color: #2c3e50;
                }
                .doc-viewer {
                    margin-top: 20px;
                }
                .doc-viewer iframe {
                    width: 100%;
                    height: 500px;
                    border: none;
                }
            </style>
        </head>
        <body>
        <?php require_once('../inc/topBarNav.php'); ?>
        <?php require_once('../inc/navigation.php'); ?>
            <div class="container">
                <div class="claim-details">
                    <h2>Claim Details</h2>
                    <p><strong>Item Title:</strong> <?= htmlspecialchars($claim['item_title']); ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($claim['category_name']); ?></p>
                    <p><strong>Founder Name:</strong> <?= htmlspecialchars($claim['fullname']); ?></p>
                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($claim['contact']); ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($claim['description']); ?></p>
                    <p><strong>Additional Info:</strong> <?= htmlspecialchars($claim['additional_info']); ?></p>
                    <?php if (!empty($claim['image_path'])): ?>
                        <div class="lf-image">
                            <img src="<?= validate_image($claim['image_path']); ?>" alt="<?= htmlspecialchars($claim['item_title']); ?>">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="claimer-details">
                    <h3>Claimer Details</h3>
                    <p><strong>Email:</strong> <?= htmlspecialchars($claim['user_email']); ?></p>
                    <p><strong>Course:</strong> <?= htmlspecialchars($claim['user_course']); ?></p>
                    <p><strong>Year:</strong> <?= htmlspecialchars($claim['user_year']); ?></p>
                    <p><strong>Section:</strong> <?= htmlspecialchars($claim['user_section']); ?></p>
                </div>
            </div>
            <?php require_once('../inc/footer.php'); ?>
        </body>
        </html>

        <?php
    } else {
        echo '<div style="color: red; font-weight: bold;">Claim details not found.</div>';
    }
} else {
    echo '<div style="color: red; font-weight: bold;">Invalid claim ID.</div>';
}
?>
