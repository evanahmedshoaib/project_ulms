<?php
// Start the session
session_start();
include '../includes/db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Handle the book reservation
if (isset($_POST['reserve_book'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];
    $reservation_date = date("Y-m-d"); // current date
    $due_date = date("Y-m-d", strtotime("+7 days")); // set due date to 7 days from today

    // Check if the book is already reserved
    $check_query = "SELECT * FROM reservations WHERE book_id = '$book_id' AND returned = 'No'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<p>Sorry, this book is already reserved.</p>";
    } else {
        // Insert a new reservation record
        $reserve_query = "INSERT INTO reservations (book_id, user_id, reservation_date, due_date) 
                          VALUES ('$book_id', '$user_id', '$reservation_date', '$due_date')";

        // Update the book's reserved status
        $update_query = "UPDATE books SET reserved = 'Yes' WHERE book_id = '$book_id'";

        if (mysqli_query($conn, $reserve_query) && mysqli_query($conn, $update_query)) {
            echo "<p>Book reserved successfully!</p>";
            header("Refresh:0");
        } else {
            echo "<p>There was an error reserving the book.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<!-- Main content for viewing books (the table, etc.) -->
<h1>Library Books</h1>
<table>
    <thead>
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
    <?php
    // Fetch all books
    $query = "SELECT * FROM books";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($book = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . ($book['title']) . "</td>";
            echo "<td>" . ($book['author']) . "</td>";
            echo "<td>" . ($book['genre']) . "</td>";
            echo "<td>" . ($book['isbn']) . "</td>";
            echo "<td>" . ($book['reserved'] == 'Yes' ? "Unavailable" : "Available") . "</td>";

            if ($_SESSION['role'] == 'student') {
                if ($book['reserved'] == 'No') {
                    echo "<td>
                            <form method='POST' action='view_books.php'>
                                <input type='hidden' name='book_id' value='{$book['book_id']}'>
                                <button type='submit' name='reserve_book'>Reserve</button>
                            </form>
                          </td>";
                } else {
                    echo "<td>Reserved</td>";
                }
            } else {
                echo "<td>-</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No books available.</td></tr>";
    }
    ?>
    </tbody>
</table>

</body>
</html>
