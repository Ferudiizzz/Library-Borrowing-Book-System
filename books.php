<?php
include 'config.php';
include 'header.php';

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM books WHERE book_id = '$id'";
    mysqli_query($conn, $delete_query);
    header('Location: books.php');
    exit();
}

// Handle Add/Edit Book
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author_id = mysqli_real_escape_string($conn, $_POST['author_id']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $publication_year = mysqli_real_escape_string($conn, $_POST['publication_year']);
    $available_copies = mysqli_real_escape_string($conn, $_POST['available_copies']);
    
    if (isset($_POST['book_id'])) {
        // Update existing book
        $book_id = mysqli_real_escape_string($conn, $_POST['book_id']);
        $query = "UPDATE books SET 
                  title='$title', 
                  author_id='$author_id', 
                  category_id='$category_id', 
                  publication_year='$publication_year', 
                  available_copies='$available_copies' 
                  WHERE book_id='$book_id'";
    } else {
        // Add new book
        $query = "INSERT INTO books (title, author_id, category_id, publication_year, available_copies, status) 
                  VALUES ('$title', '$author_id', '$category_id', '$publication_year', '$available_copies', 'available')";
    }
    
    mysqli_query($conn, $query);
    header('Location: books.php');
    exit();
}

// Handle Search Operation
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Books Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
            Add New Book
        </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <form method="GET" class="d-flex">
            <input type="text" class="form-control" name="search" placeholder="Search by Title, Author, or Category" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary ms-2">Search</button>
        </form>
    </div>

    <!-- Books Table -->
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Year</th>
                        <th>Copies</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT b.*, a.name as author_name, c.name as category_name 
                             FROM books b 
                             JOIN authors a ON b.author_id = a.author_id 
                             JOIN categories c ON b.category_id = c.category_id";
                    
                    if ($search) {
                        $query .= " WHERE b.title LIKE '%$search%' 
                                    OR a.name LIKE '%$search%' 
                                    OR c.name LIKE '%$search%'";
                    }

                    $result = mysqli_query($conn, $query);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>{$row['book_id']}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['author_name']}</td>
                                <td>{$row['category_name']}</td>
                                <td>{$row['publication_year']}</td>
                                <td>{$row['available_copies']}</td>
                                <td>{$row['status']}</td>
                                <td>
                                    <button class='btn btn-sm btn-primary edit-book' 
                                            data-bs-toggle='modal' 
                                            data-bs-target='#editBookModal' 
                                            data-book='" . htmlspecialchars(json_encode($row)) . "'>
                                        Edit
                                    </button>
                                    <a href='?delete={$row['book_id']}' 
                                       class='btn btn-sm btn-danger' 
                                       onclick='return confirm(\"Are you sure?\")'>
                                        Delete
                                    </a>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <select class="form-select" name="author_id" required>
                                <?php
                                $authors = mysqli_query($conn, "SELECT * FROM authors");
                                while ($author = mysqli_fetch_assoc($authors)) {
                                    echo "<option value='{$author['author_id']}'>{$author['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <?php
                                $categories = mysqli_query($conn, "SELECT * FROM categories");
                                while ($category = mysqli_fetch_assoc($categories)) {
                                    echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="number" class="form-control" name="publication_year" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Available Copies</label>
                            <input type="number" class="form-control" name="available_copies" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="book_id" id="edit_book_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <select class="form-select" name="author_id" id="edit_author_id" required>
                                <?php
                                mysqli_data_seek($authors, 0);
                                while ($author = mysqli_fetch_assoc($authors)) {
                                    echo "<option value='{$author['author_id']}'>{$author['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <?php
                                mysqli_data_seek($categories, 0);
                                while ($category = mysqli_fetch_assoc($categories)) {
                                    echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="number" class="form-control" name="publication_year" id="edit_publication_year" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Available Copies</label>
                            <input type="number" class="form-control" name="available_copies" id="edit_available_copies" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Set data for editing book
    const editButtons = document.querySelectorAll('.edit-book');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const book = JSON.parse(this.getAttribute('data-book'));
            document.getElementById('edit_book_id').value = book.book_id;
            document.getElementById('edit_title').value = book.title;
            document.getElementById('edit_author_id').value = book.author_id;
            document.getElementById('edit_category_id').value = book.category_id;
            document.getElementById('edit_publication_year').value = book.publication_year;
            document.getElementById('edit_available_copies').value = book.available_copies;
        });
    });
</script>
