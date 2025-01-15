<?php
session_start();
include 'includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$book_id = $_GET['book_id'];
$user_id = $_SESSION['user_id'];

// Debugging
error_log("Book ID: $book_id, User ID: $user_id");

// Check if the book is already reserved
$check_query = "SELECT * FROM reservations WHERE book_id = '$book_id' AND returned = 'No'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    error_log("Error: Book is already reserved.");
    echo "Sorry, this book is already reserved.";
} else {
    // Reserve the book by inserting a new reservation
    $reservation_date = date("Y-m-d"); // current date
    $due_date = date("Y-m-d", strtotime("+7 days")); // due date is 7 days from now

    $reserve_query = "INSERT INTO reservations (book_id, user_id, reservation_date, due_date) 
                      VALUES ('$book_id', '$user_id', '$reservation_date', '$due_date')";

    // Update the books table to mark the book as reserved
    $update_query = "UPDATE books SET reserved = 'Yes' WHERE book_id = '$book_id'";

    if (mysqli_query($conn, $reserve_query) && mysqli_query($conn, $update_query)) {
        error_log("Book reserved successfully.");
        echo "Book reserved successfully!";
    } else {
        error_log("Query failed: " . mysqli_error($conn));
        echo "Error in reservation. Please try again.";
    }
}

header("Location: view_books.php");
exit();
?>
