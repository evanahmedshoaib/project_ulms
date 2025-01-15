<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

include '../includes/db_connection.php';
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<h1>Welcome, Student</h1>


<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<hr>
<!-- My Reservations -->
<section id="my-reservations">
    <h2>My Reservations</h2>
    <table border="1">
        <tr>
            <th>Book ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Reserved Date</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>
        <?php
        // Ensure the student_id is correctly taken from the session
        $query = "SELECT b.book_id, b.title, b.author, r.reservation_date, r.due_date,
                         CASE 
                            WHEN r.returned = 'No' THEN 'Not Returned'
                            ELSE 'Returned'
                         END AS status
                  FROM books b
                  JOIN reservations r ON b.book_id = r.book_id
                  WHERE r.user_id = '$user_id'";  // Ensure correct user_id here

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Check if there are any reservations for the logged-in user
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                          <td>{$row['book_id']}</td>
                          <td>{$row['title']}</td>
                          <td>{$row['author']}</td>
                          <td>{$row['reservation_date']}</td>
                          <td>{$row['due_date']}</td> <!-- Display Due Date -->
                          <td>{$row['status']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No reservations found.</td></tr>";  // Adjust column span
        }
        ?>
    </table>
</section>

</body>
</html>

