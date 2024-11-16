<?php
include 'config.php';
include 'functions.php';
include 'header.php';

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $membership_status = mysqli_real_escape_string($conn, $_POST['membership_status']);
    $membership_type = mysqli_real_escape_string($conn, $_POST['membership_type']);
    $membership_expiry = mysqli_real_escape_string($conn, $_POST['membership_expiry']);

    $update_query = "UPDATE students SET 
        first_name = '$first_name',
        last_name = '$last_name',
        email = '$email',
        contact_number = '$contact_number',
        membership_status = '$membership_status',
        membership_type = '$membership_type',
        membership_expiry = '$membership_expiry'
        WHERE student_id = '$student_id'";

    if (mysqli_query($conn, $update_query)) {
        // Success message
        $success_message = "Student updated successfully!";
    } else {
        // Error message
        $error_message = "Error updating student: " . mysqli_error($conn);
    }
}

// Handle search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Base query
$query = "SELECT 
            student_id,
            first_name,
            last_name,
            email,
            contact_number,
            membership_status,
            membership_type,
            membership_expiry,
            current_borrowed,
            total_penalties,
            created_at
          FROM students 
          WHERE 1=1";

// Add search condition if search term exists
if ($search) {
    $query .= " AND (
        first_name LIKE '%$search%' OR 
        last_name LIKE '%$search%' OR 
        email LIKE '%$search%' OR 
        student_id LIKE '%$search%'
    )";
}

// Add status filter if selected
if ($status_filter) {
    $query .= " AND membership_status = '$status_filter'";
}

$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Handle deletion of student
if (isset($_GET['delete'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM students WHERE student_id = '$student_id'";
    mysqli_query($conn, $delete_query);
    header("Location: students.php"); // Redirect to avoid resubmission on refresh
    exit();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Student Directory</h2>
        <a href="add_student.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Student
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email, or ID" 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                        <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="students.php" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Membership</th>
                            <th>Status</th>
                            <th>Books</th>
                            <th>Penalties</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            // Determine status badge color
                            $status_class = 'bg-success';
                            if ($row['membership_status'] === 'expired') {
                                $status_class = 'bg-danger';
                            } elseif ($row['membership_status'] === 'suspended') {
                                $status_class = 'bg-warning';
                            }

                            // Check if membership is about to expire
                            $expiry_warning = '';
                            if ($row['membership_status'] === 'active') {
                                $days_until_expiry = floor((strtotime($row['membership_expiry']) - time()) / (60 * 60 * 24));
                                if ($days_until_expiry <= 7) {
                                    $expiry_warning = '<br><small class="text-warning">Expires in ' . $days_until_expiry . ' days</small>';
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($row['email']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['contact_number']); ?></small>
                                </td>
                                <td>
                                    <?php echo ucfirst(htmlspecialchars($row['membership_type'])); ?>
                                    <br>
                                    <small class="text-muted">
                                        Expires: <?php echo date('Y-m-d', strtotime($row['membership_expiry'])); ?>
                                        <?php echo $expiry_warning; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['membership_status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?php echo $row['current_borrowed']; ?> books</div>
                                </td>
                                <td>
                                    <?php if ($row['total_penalties'] > 0): ?>
                                        <span class="text-danger">$<?php echo number_format($row['total_penalties'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-success">No penalties</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary edit-btn" 
                                                data-id="<?php echo htmlspecialchars($row['student_id']); ?>"
                                                data-first-name="<?php echo htmlspecialchars($row['first_name']); ?>"
                                                data-last-name="<?php echo htmlspecialchars($row['last_name']); ?>"
                                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                data-contact="<?php echo htmlspecialchars($row['contact_number']); ?>"
                                                data-status="<?php echo htmlspecialchars($row['membership_status']); ?>"
                                                data-type="<?php echo htmlspecialchars($row['membership_type']); ?>"
                                                data-expiry="<?php echo htmlspecialchars($row['membership_expiry']); ?>">
                                            Edit
                                        </button>
                                        <a href="students.php?delete=<?php echo htmlspecialchars($row['student_id']); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this student?')">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($result) == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    No students found matching your criteria
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update the modal form -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_student" value="1">
                    <input type="hidden" name="student_id" id="editStudentId">
                    
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="editFirstName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="editLastName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" id="editContact" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Membership Status</label>
                        <select class="form-select" name="membership_status" id="editStatus">
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Membership Type</label>
                        <select class="form-select" name="membership_type" id="editType">
                            <option value="regular">Regular</option>
                            <option value="premium">Premium</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Membership Expiry</label>
                        <input type="date" class="form-control" name="membership_expiry" id="editExpiry" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update the JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add success/error message display
    <?php if (isset($success_message)): ?>
        alert('<?php echo $success_message; ?>');
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        alert('<?php echo $error_message; ?>');
    <?php endif; ?>

    // Initialize edit buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
            
            // Set form values
            document.getElementById('editStudentId').value = this.dataset.id;
            document.getElementById('editFirstName').value = this.dataset.firstName;
            document.getElementById('editLastName').value = this.dataset.lastName;
            document.getElementById('editEmail').value = this.dataset.email;
            document.getElementById('editContact').value = this.dataset.contact;
            document.getElementById('editStatus').value = this.dataset.status;
            document.getElementById('editType').value = this.dataset.type;
            document.getElementById('editExpiry').value = this.dataset.expiry;
            
            modal.show();
        });
    });
});
</script>

<?php include 'footer.php'; ?>