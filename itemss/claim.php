<p><strong>Name:</strong> <?= htmlspecialchars($claimantData['first_name'] . ' ' . $claimantData['last_name']); ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($claimantData['email']); ?></p>
        <p><strong>College:</strong> <?= htmlspecialchars($claimantData['college']); ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($claimantData['course']); ?></p>
        <p><strong>Year & Section:</strong> <?= htmlspecialchars($claimantData['year'] . ' - ' . $claimantData['section']); ?></p>
    </div>

    <!-- Disable form if the claimer is the finder -->
    <?php if ($isFinder): ?>
        <div class="disabled-msg">
            <strong>Note:</strong> You cannot claim your own posted item.
        </div>
    <?php else: ?>
        <!-- Claim Form -->
        <form id="claimForm" action="submit_claim.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="item_id" value="<?= $itemId; ?>">

            <div class="form-group">
                <label for="item_description">Describe the item (e.g., color, model, size, etc.):</label>
                <textarea id="item_description" name="item_description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="date_lost">When did you lose the item?</label>
                <input type="date" id="date_lost" name="date_lost" required>
            </div>

            <div class="form-group">
                <label for="location_lost">Where did you lose the item?</label>
                <input type="text" id="location_lost" name="location_lost" required>
            </div>

            <div class="form-group">
                <label for="proof_of_ownership">Upload proof of ownership (e.g., receipt, serial number, photo):</label>
                <input type="file" id="proof_of_ownership" name="proof_of_ownership" accept="image/*,application/pdf">
            </div>

            <div class="form-group">
                <label for="security_question">Security Question (e.g., contents in the pocket):</label>
                <input type="text" id="security_question" name="security_question" required>
            </div>

            <div class="form-group">
                <label for="personal_id">Upload your ID (student card, national ID, etc.):</label>
                <input type="file" id="personal_id" name="personal_id" accept="image/*,application/pdf" required>
            </div>

            <button type="submit" class="submit-btn">Submit Claim</button>
        </form>
    <?php endif; ?>
</div>

<!-- SweetAlert2 script for form submission -->
<script>
    document.getElementById('claimForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting the traditional way
        const form = e.target;

        // Display SweetAlert success message
        Swal.fire({
            title: 'Claim Submitted!',
            text: 'Your claim has been submitted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(function () {
            // After clicking 'OK', reset the form
            form.reset(); // Reset the form fields
            
            // Optionally, you can manually redirect the user if needed
            // window.location.href = 'some_page.php'; // Replace with the desired page
        });
    });
</script>

<?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
