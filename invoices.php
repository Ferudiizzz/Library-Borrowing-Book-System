<?php
include 'config.php';
include 'header.php';


// Handle Payment
if (isset($_POST['pay_penalty'])) {
    $penalty_id = mysqli_real_escape_string($conn, $_POST['penalty_id']);
    $amount_paid = mysqli_real_escape_string($conn, $_POST['amount_paid']);
    $payment_date = date('Y-m-d H:i:s');
    
    mysqli_query($conn, "UPDATE penalties 
                        SET amount_paid = '$amount_paid',
                            payment_date = '$payment_date',
                            status = 'paid'
                        WHERE penalty_id = '$penalty_id'");
    
    header('Location: invoices.php');
    exit();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penalties and Payments</h2>
        <div>
            <button class="btn btn-success" onclick="printInvoices()">
                <i class="fas fa-print"></i> Print Invoices
            </button>
        </div>
    </div>

    <!-- Unpaid Penalties -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Unpaid Penalties</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Overdue Days</th>
                        <th>Penalty Amount</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT p.*, 
                             CONCAT(s.first_name, ' ', s.last_name) as student_name,
                             b.title as book_title,
                             t.due_date
                             FROM penalties p
                             JOIN borrowingtransactions t ON p.transaction_id = t.transaction_id
                             JOIN students s ON t.borrower_id = s.student_id
                             JOIN books b ON t.book_id = b.book_id
                             WHERE p.status = 'unpaid'
                             ORDER BY p.penalty_id DESC";
                    $result = mysqli_query($conn, $query);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>INV-{$row['penalty_id']}</td>
                                <td>{$row['student_name']}</td>
                                <td>{$row['book_title']}</td>
                                <td>{$row['overdue_days']}</td>
                                <td>$" . number_format($row['penalty_fee'], 2) . "</td>
                                <td>" . date('Y-m-d', strtotime($row['due_date'])) . "</td>
                                <td>
                                    <button class='btn btn-sm btn-primary'
                                            onclick='showPaymentModal({$row['penalty_id']}, {$row['penalty_fee']})'>
                                        Record Payment
                                    </button>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card">
        <div class="card-header">
            <h5>Payment History</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Penalty Amount</th>
                        <th>Amount Paid</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT p.*, 
                             CONCAT(s.first_name, ' ', s.last_name) as student_name,
                             b.title as book_title
                             FROM penalties p
                             JOIN borrowingtransactions t ON p.transaction_id = t.transaction_id
                             JOIN students s ON t.borrower_id = s.student_id
                             JOIN books b ON t.book_id = b.book_id
                             WHERE p.status = 'paid'
                             ORDER BY p.payment_date DESC";
                    $result = mysqli_query($conn, $query);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>INV-{$row['penalty_id']}</td>
                                <td>{$row['student_name']}</td>
                                <td>{$row['book_title']}</td>
                                <td>$" . number_format($row['penalty_fee'], 2) . "</td>
                                <td>$" . number_format($row['amount_paid'], 2) . "</td>
                                <td>" . date('Y-m-d', strtotime($row['payment_date'])) . "</td>
                                <td><span class='badge bg-success'>Paid</span></td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="penalty_id" id="penalty_id">
                        <div class="mb-3">
                            <label class="form-label">Penalty Amount</label>
                            <input type="text" class="form-control" id="penalty_amount" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" class="form-control" name="amount_paid" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="pay_penalty" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showPaymentModal(penaltyId, penaltyAmount) {
    document.getElementById('penalty_id').value = penaltyId;
    document.getElementById('penalty_amount').value = '$' + penaltyAmount.toFixed(2);
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function printInvoices() {
    window.print();
}
</script>

<?php
include 'footer.php';
mysqli_close($conn);
?>