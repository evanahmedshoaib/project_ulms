<?php
session_start();

// Include the database connection
include '../includes/db_connection.php';

// Check if the user is logged in and is an admin or staff
if (!isset($_SESSION['user_id'])  ||($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../index.php");
    exit();
}

// Handle Update Reservation Status
if (isset($_POST['update_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $status = $_POST['status'];

    // Fetch the book ID associated with the reservation
    $reservation_query = "SELECT book_id FROM reservations WHERE reservation_id = '$reservation_id'";
    $reservation_result = mysqli_query($conn, $reservation_query);

    if ($reservation_row = mysqli_fetch_assoc($reservation_result)) {
        $book_id = $reservation_row['book_id'];

        // Update the reservation status
        $update_reservation_query = "UPDATE reservations SET returned='$status' WHERE reservation_id='$reservation_id'";
        $update_reservation_result = mysqli_query($conn, $update_reservation_query);

        if ($update_reservation_result) {
            // Update the `books` table based on the returned status
            if ($status === 'Yes') {
                // If returned, make the book available
                $update_book_query = "UPDATE books SET reserved='No', reserved_by=NULL WHERE book_id='$book_id'";
            } else {
                // If not returned, keep it reserved by the current user
                $update_book_query = "UPDATE books SET reserved='Yes', reserved_by=(SELECT user_id FROM reservations WHERE reservation_id='$reservation_id') WHERE book_id='$book_id'";
            }

            if (mysqli_query($conn, $update_book_query)) {
                echo "<p>Reservation and book status updated successfully!</p>";
            } else {
                echo "<p>Error updating book status: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p>Error updating reservation: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Error fetching reservation details.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<section id="manage-reservations">
    <h2>Manage Reservations</h2>
    <table border="1">
        <tr>
            <th>Reservation ID</th>
            <th>Book ID</th>
            <th>Book Title</th>
            <th>User ID</th>
            <th>Reservation Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        // Fetch all reservations
        $query = "SELECT r.reservation_id, r.book_id, b.title AS book_title, r.user_id, 
                         r.reservation_date, r.due_date, r.returned 
                  FROM reservations r
                  JOIN books b ON r.book_id = b.book_id";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                <form method='POST' action=''>
                    <td>{$row['reservation_id']}</td>
                    <td>{$row['book_id']}</td>
                    <td>{$row['book_title']}</td>
                    <td>{$row['user_id']}</td>
                    <td>{$row['reservation_date']}</td>
                    <td>{$row['due_date']}</td>
                    <td>
                        <select name='status'>
                            <option value='No' " . ($row['returned'] === 'No' ? 'selected' : '') . ">Not Returned</option>
                            <option value='Yes' " . ($row['returned'] === 'Yes' ? 'selected' : '') . ">Returned</option>
                        </select>
                    </td>
                    <td>
                        <input type='hidden' name='reservation_id' value='{$row['reservation_id']}'>
                        <button type='submit' name='update_reservation'>Update</button>
                    </td>
                </form>
            </tr>";
        }
        ?>
    </table>
</section>

</body>
</html>
