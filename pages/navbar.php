<?php
// Include the database connection
include '../includes/db_connection.php';

// Handle the search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search_query'];
    $query = "SELECT * FROM books WHERE title LIKE '%$search_query%' OR author LIKE '%$search_query%' OR genre LIKE '%$search_query%'";
    $result = mysqli_query($conn, $query);
} else {
    // Default query to fetch all books
    $result = null;
}

// Handle Delete Book
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $query = "DELETE FROM books WHERE book_id='$book_id'";
    if (mysqli_query($conn, $query)) {
        echo "<p>Book deleted successfully!</p>";
    } else {
        echo "<p>Error deleting book: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" >ULMS</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($_SESSION['role'] == 'student') { ?>
                    <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_books.php">View Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="student_reservations.php">View Reservations</a></li>
                <?php } ?>

                <?php if ($_SESSION['role'] == 'admin') { ?>
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_books.php">View Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_reservations.php">View Reservations</a></li>
                <?php } ?>

                <?php if ($_SESSION['role'] == 'staff') { ?>
                    <li class="nav-item"><a class="nav-link" href="staff_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_books.php">View Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_reservations.php">View Reservations</a></li>
                <?php } ?>
            </ul>

            <!-- Search Bar -->
            <div class="d-flex align-items-center">
                <form class="d-none d-lg-flex me-3" id="search-form" method="GET" action="<?= basename($_SERVER['PHP_SELF']); ?>">
                    <div class="input-group">
                        <input class="form-control border-0 bg-light" type="text" name="search_query" placeholder="Search" value="<?= htmlspecialchars($search_query) ?>">
                        <button class="btn btn-outline-light" type="submit" name="search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                <form class="d-flex d-lg-none" method="GET" action="<?= basename($_SERVER['PHP_SELF']); ?>">
                    <input class="form-control form-control-sm me-2" type="text" name="search_query" placeholder="Search" value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn btn-outline-light btn-sm" type="submit" name="search">Go</button>
                </form>
            </div>

            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>


<!-- Display Search Results -->
<?php if ($search_query && $result): ?>
    <div class="container mt-3">
        <h3>Search Results for "<?= htmlspecialchars($search_query) ?>"</h3>
        <table class="table table-striped">
            <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Genre</th>
                <th>ISBN</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($book = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['genre']) ?></td>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= ($book['reserved'] == 'Yes' ? 'Unavailable' : 'Available') ?></td>
                        <td>
                            <?php if ($_SESSION['role'] == 'student' && $book['reserved'] == 'No'): ?>
                                <form method="POST" action="view_books.php">
                                    <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                    <button class="btn btn-sm btn-success" type="submit" name="reserve_book">Reserve</button>
                                </form>
                            <?php elseif ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff'): ?>
                                <form method="POST" action="manage_books.php">
                                    <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                    <button class="btn btn-sm btn-danger" type='submit' name='delete_book'>Delete</button>
                                </form>
                            <?php elseif ($book['reserved'] == 'Yes'): ?>
                                <span class="text-danger">Reserved</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No books found matching the search criteria.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<script>
    document.getElementById('toggle-search').addEventListener('click', function () {
        const searchForm = document.getElementById('search-form');
        searchForm.classList.toggle('d-none'); // Toggle visibility
        searchForm.classList.toggle('d-flex'); // Ensure proper alignment
    });
</script>
