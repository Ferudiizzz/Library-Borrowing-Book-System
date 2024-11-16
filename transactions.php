<?php
include 'config.php';
include 'header.php';

// Handle Return Book
if (isset($_GET['return'])) {
    $transaction_id = mysqli_real_escape_string($conn, $_GET['return']);
    
    // Get transaction details
    $query = "SELECT * FROM borrowingtransactions WHERE transaction_id = '$transaction_id'";
    $result = mysqli_query($conn, $query);
    $transaction = mysqli_fetch_assoc($result);
    
    // Calculate overdue days
    $due_date = new DateTime($transaction['due_date']);
    $return_date = new DateTime();
    $diff = $return_date->diff($due_date);
    $overdue_days = ($return_date > $due_date) ? $diff->days : 0;
    
    // Update transaction
    $return_date_str = date('Y-m-d H:i:s');
    $status = $overdue_days > 0 ? 'overdue' : 'returned';
    
    mysqli_query($conn, "UPDATE borrowingtransactions 
                        SET return_date = '$return_date_str', 
                            status = '$status' 
                        WHERE transaction_id = '$transaction_id'");
    
    // Update book status
    mysqli_query($conn, "UPDATE books 
                        SET status = 'available', 
                            available_copies = available_copies + 1 
                        WHERE book_id = '{$transaction['book_id']}'");
    
    // Add penalty if overdue
    if ($overdue_days > 0) {
        $penalty_fee = $overdue_days * 1.00; // $1 per day
        mysqli_query($conn, "INSERT INTO penalties (transaction_id, overdue_days, penalty_fee) 
                            VALUES ('$transaction_id', '$overdue_days', '$penalty_fee')");
    }
    
    header('Location: transactions.php');
    exit();
}

// Handle New Borrowing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrower_id = mysqli_real_escape_string($conn, $_POST['borrower_id']);
    $book_id = mysqli_real_escape_string($conn, $_POST['book_id']);
    $borrow_date = date('Y-m-d H:i:s');
    $due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // 7 days borrowing period
    
    // Check if book is available
    $book_query = "SELECT available_copies FROM books WHERE book_id = '$book_id'";
    $book_result = mysqli_query($conn, $book_query);
    $book = mysqli_fetch_assoc($book_result);
    
    if ($book['available_copies'] > 0) {
        // Create transaction
        mysqli_query($conn, "INSERT INTO borrowingtransactions 
                            (borrower_id, book_id, borrow_date, due_date, status) 
                            VALUES 
                            ('$borrower_id', '$book_id', '$borrow_date', '$due_date', 'borrowed')");
        
        // Update book status
        mysqli_query($conn, "UPDATE books 
                            SET available_copies = available_copies - 1,
                                status = CASE WHEN available_copies - 1 = 0 THEN 'borrowed' ELSE status END
                            WHERE book_id = '$book_id'");
        
        header('Location: transactions.php');
        exit();
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Borrowing Transactions</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBorrowingModal">
            New Borrowing
        </button>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT t.*, 
                             CONCAT(s.first_name, ' ', s.last_name) as student_name,
                             b.title as book_title
                             FROM borrowingtransactions t
                             JOIN students s ON t.borrower_id = s.student_id
                             JOIN books b ON t.book_id = b.book_id
                             ORDER BY t.transaction_id DESC";
                    $result = mysqli_query($conn, $query);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status_class = '';
                        switch($row['status']) {
                            case 'borrowed': $status_class = 'text-primary'; break;
                            case 'returned': $status_class = 'text-success'; break;
                            case 'overdue': $status_class = 'text-danger'; break;
                        }
                        
                        echo "<tr>
                                <td>{$row['transaction_id']}</td>
                                <td>{$row['student_name']}</td>
                                <td>{$row['book_title']}</td>
                                <td>" . date('Y-m-d', strtotime($row['borrow_date'])) . "</td>
                                <td>" . date('Y-m-d', strtotime($row['due_date'])) . "</td>
                                <td>" . ($row['return_date'] ? date('Y-m-d', strtotime($row['return_date'])) : '-') . "</td>
                                <td class='$status_class'>{$row['status']}</td>
                                <td>";
                        if ($row['status'] == 'borrowed') {
                            echo "<a href='?return={$row['transaction_id']}' 
                                   class='btn btn-sm btn-success'
                                   onclick='return confirm(\"Confirm return?\")'>
                                    Return
                                </a>";
                        }
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Borrowing Modal -->
    <div class="modal fade" id="newBorrowingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Borrowing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="borrower_id" required>
                                <?php
                                $students = mysqli_query($conn, "SELECT * FROM students ORDER BY last_name, first_name");
                                while ($student = mysqli_fetch_assoc($students)) {
                                    echo "<option value='{$student['student_id']}'>";
                                    echo "{$student['last_name']}, {$student['first_name']}";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Book</label>
                            <select class="form-select" name="book_id" required>
                                <?php
                                $books = mysqli_query($conn, "SELECT * FROM books WHERE available_copies > 0");
                                while ($book = mysqli_fetch_assoc($books)) {
                                    echo "<option value='{$book['book_id']}'>";
                                    echo "{$book['title']} ({$book['available_copies']} available)";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Borrowing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
mysqli_close($conn);
?>