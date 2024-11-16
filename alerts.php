<?php
include 'config.php';
include 'functions.php';
include 'header.php';

// Generate alerts only once per session
if (!isset($_SESSION['alerts_generated_today'])) {
    generateAlerts($conn);
    $_SESSION['alerts_generated_today'] = date('Y-m-d');
}

// Clear the session variable if it's from a previous day
if ($_SESSION['alerts_generated_today'] != date('Y-m-d')) {
    unset($_SESSION['alerts_generated_today']);
}
// Handle mark as read
if (isset($_GET['mark_read'])) {
    $alert_id = mysqli_real_escape_string($conn, $_GET['mark_read']);
    mysqli_query($conn, "UPDATE alerts SET status = 'read' WHERE alert_id = '$alert_id'");
    header('Location: alerts.php');
    exit();
}

// Handle mark all as read
if (isset($_GET['mark_all_read'])) {
    mysqli_query($conn, "UPDATE alerts SET status = 'read' WHERE status = 'unread'");
    header('Location: alerts.php');
    exit();
}

// Generate alerts
generateAlerts($conn);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Library Alerts</h2>
        <div>
            <a href="?mark_all_read=1" class="btn btn-secondary">Mark All as Read</a>
        </div>
    </div>

    <!-- Alert Categories -->
    <div class="mb-4">
        <div class="btn-group">
            <button class="btn btn-outline-primary active" onclick="filterAlerts('all')">All Alerts</button>
            <button class="btn btn-outline-danger" onclick="filterAlerts('overdue')">Overdue Books</button>
            <button class="btn btn-outline-warning" onclick="filterAlerts('membership')">Membership</button>
            <button class="btn btn-outline-info" onclick="filterAlerts('penalties')">Penalties</button>
        </div>
    </div>

    <!-- Unread Alerts -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0">Active Alerts</h5>
        </div>
        <div class="card-body">
            <?php
            $query = "SELECT 
                        a.*,
                        s.first_name,
                        s.last_name,
                        s.contact_number,
                        s.membership_status,
                        s.membership_type,
                        s.membership_expiry,
                        b.title as book_title
                      FROM alerts a
                      LEFT JOIN students s ON a.borrower_id = s.student_id
                      LEFT JOIN books b ON a.book_id = b.book_id
                      WHERE a.status = 'unread' 
                      ORDER BY a.priority DESC, a.created_at DESC";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Previous alert display code remains the same
                    ?>
                    <div class="alert <?php echo $alert_class; ?> alert-item" 
                         data-type="<?php echo $row['alert_type']; ?>"
                         id="alert-<?php echo $row['alert_id']; ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="alert-heading">
                                    <?php echo ucfirst(str_replace('_', ' ', $row['alert_type'])); ?>
                                </h5>
                                <div class="alert-message">
                                    <?php echo $row['message']; ?>
                                </div>
                                <small class="text-muted">
                                    Created: <?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?>
                                </small>
                            </div>
                            <div>
                                <button onclick="markAsRead(<?php echo $row['alert_id']; ?>)" 
                                        class="btn btn-sm btn-light">
                                    Mark as Read
                                </button>
                                <a href="students.php?id=<?php echo $row['borrower_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    View Student
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-center mb-0'>No active alerts</p>";
            }
            ?>
        </div>
    </div>

    <!-- Read Alerts Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Read Alerts</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Created</th>
                        <th>Read Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="read-alerts-table">
                    <?php
                    $read_query = "SELECT 
                                    a.*,
                                    s.first_name,
                                    s.last_name
                                  FROM alerts a
                                  LEFT JOIN students s ON a.borrower_id = s.student_id
                                  WHERE a.status = 'read' 
                                  ORDER BY a.updated_at DESC";
                    $read_result = mysqli_query($conn, $read_query);

                    while ($row = mysqli_fetch_assoc($read_result)) {
                        ?>
                        <tr>
                            <td><?php echo ucfirst(str_replace('_', ' ', $row['alert_type'])); ?></td>
                            <td><?php echo $row['message']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['updated_at'])); ?></td>
                            <td>
                                <a href="students.php?id=<?php echo $row['borrower_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    View Student
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function markAsRead(alertId) {
    fetch(`alerts.php?mark_read=${alertId}`)
        .then(response => response.text())
        .then(() => {
            // Remove from active alerts
            const alertElement = document.getElementById(`alert-${alertId}`);
            alertElement.remove();
            
            // Reload the page to update the read alerts table
            location.reload();
        });
}

function filterAlerts(type) {
    const alerts = document.querySelectorAll('.alert-item');
    alerts.forEach(alert => {
        if (type === 'all' || alert.dataset.type.includes(type)) {
            alert.style.display = 'block';
        } else {
            alert.style.display = 'none';
        }
    });
}
</script>

<?php
include 'footer.php';
mysqli_close($conn);
?>