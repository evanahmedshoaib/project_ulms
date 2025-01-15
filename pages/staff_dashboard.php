<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../index.php");
    exit();
}

include '../includes/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<h1>Welcome, Staff</h1>


<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<hr>
<!-- Manage Users -->

<!-- Manage Books -->
<section id="manage-books">
    <h2>Manage Books</h2>
    <form method="POST" action="admin_dashboard.php">
        <label for="book_id">Book ID:</label>
        <input type="text" name="book_id"><br>
        <label for="title">Title:</label>
        <input type="text" name="title" required><br>
        <label for="author">Author:</label>
        <input type="text" name="author" required><br>
        <label for="genre">Genre:</label>
        <input type="text" name="genre"><br>
        <label for="isbn">ISBN:</label>
        <input type="text" name="isbn"><br>
        <button type="submit" name="add_book">Add Book</button>
    </form>

    <?php
    // Add or Update Book
    if (isset($_POST['add_book'])) {
        $book_id = $_POST['book_id'];
        $title = $_POST['title'];
        $author = $_POST['author'];
        $genre = $_POST['genre'];
        $isbn = $_POST['isbn'];

        if ($book_id) {
            $query = "UPDATE books SET title='$title', author='$author', genre='$genre', isbn='$isbn' WHERE book_id='$book_id'";
            $message = "Book updated successfully!";
        } else {
            $query = "INSERT INTO books (title, author, genre, isbn, source) VALUES ('$title', '$author', '$genre', '$isbn', 'manual')";
            $message = "Book added successfully!";
        }

        if (mysqli_query($conn, $query)) {
            echo "<p>$message</p>";
            header("Refresh:0");
        } else {
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }

    // Delete Book
    if (isset($_POST['delete_book'])) {
        $book_id = $_POST['book_id'];
        $query = "DELETE FROM books WHERE book_id='$book_id'";
        if (mysqli_query($conn, $query)) {
            echo "<p>Book deleted successfully!</p>";
            header("Refresh:0");
        } else {
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }
    ?>
</section>

</body>
</html>