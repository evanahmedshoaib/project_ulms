<?php
session_start();

// Include the database connection
include '../includes/db_connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../index.php");
    exit();
}

// Handle Add Book
if (isset($_POST['add_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $isbn = $_POST['isbn'];
    $reserved = 'No'; // New books are not reserved by default

    $query = "INSERT INTO books (book_id, title, author, genre, isbn, reserved) 
              VALUES ('$book_id', '$title', '$author', '$genre', '$isbn', '$reserved')";
    if (mysqli_query($conn, $query)) {
        echo "<p>Book added successfully!</p>";
    } else {
        echo "<p>Error adding book: " . mysqli_error($conn) . "</p>";
    }
}

// Handle Update Book
if (isset($_POST['update_book'])) {
    $original_book_id = $_POST['original_book_id']; // Original book ID for WHERE clause
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $isbn = $_POST['isbn'];
    $reserved = $_POST['reserved'];

    $query = "UPDATE books 
              SET book_id='$book_id', title='$title', author='$author', genre='$genre', isbn='$isbn', reserved='$reserved' 
              WHERE book_id='$original_book_id'";
    if (mysqli_query($conn, $query)) {
        echo "<p>Book updated successfully!</p>";
    } else {
        echo "<p>Error updating book: " . mysqli_error($conn) . "</p>";
    }
}

// Handle Delete Book
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['original_book_id'];
    $query = "DELETE FROM books WHERE book_id='$book_id'";
    if (mysqli_query($conn, $query)) {
        echo "<p>Book deleted successfully!</p>";
    } else {
        echo "<p>Error deleting book: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<section id="manage-books">
    <h2>Manage Books</h2>
    <form method="POST" action="">
        <label for="book_id">Book ID:</label>
        <input type="text" name="book_id" required><br>
        <label for="title">Title:</label>
        <input type="text" name="title" required><br>
        <label for="author">Author:</label>
        <input type="text" name="author" required><br>
        <label for="genre">Genre:</label>
        <input type="text" name="genre" required><br>
        <label for="isbn">ISBN:</label>
        <input type="text" name="isbn" required><br>
        <button type="submit" name="add_book">Add Book</button>
    </form>

    <h3>Books</h3>
    <table border="1">
        <tr>
            <th>Book ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Genre</th>
            <th>ISBN</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        // Fetch all books from the database
        $query = "SELECT * FROM books";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                <form method='POST' action=''>
                    <td>
                        <input type='text' name='book_id' value='{$row['book_id']}' required>
                        <input type='hidden' name='original_book_id' value='{$row['book_id']}'>
                    </td>
                    <td><input type='text' name='title' value='{$row['title']}' required></td>
                    <td><input type='text' name='author' value='{$row['author']}' required></td>
                    <td><input type='text' name='genre' value='{$row['genre']}' required></td>
                    <td><input type='text' name='isbn' value='{$row['isbn']}' required></td>
                    <td>
                        <select name='reserved' required>
                            <option value='No' " . ($row['reserved'] === 'No' ? 'selected' : '') . ">No</option>
                            <option value='Yes' " . ($row['reserved'] === 'Yes' ? 'selected' : '') . ">Yes</option>
                        </select>
                    </td>
                    <td>
                        <button type='submit' name='update_book'>Update</button>
                        <button type='submit' name='delete_book'>Delete</button>
                    </td>
                </form>
            </tr>";
        }
        ?>
    </table>
</section>

</body>
</html>
