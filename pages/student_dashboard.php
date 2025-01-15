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
<h1>Welcome, </h1>

<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<hr>

<!-- Search Books -->
<section id="search-books">
    <h2>Search Books</h2>
    <form method="GET" action="student_dashboard.php">
        <label for="search_query">Search:</label>
        <input type="text" name="search_query" placeholder="Enter title, author, or genre">
        <button type="submit" name="search">Search</button>
    </form>

    <?php
    if (isset($_GET['search'])) {
        $search_query = $_GET['search_query'];
        $query = "SELECT * FROM books WHERE title LIKE '%$search_query%' OR author LIKE '%$search_query%' OR genre LIKE '%$search_query%'";
        $result = mysqli_query($conn, $query);

        echo "<h3>Search Results</h3>";
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr> 
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>ISBN</th>
                        <th>Availability</th>
                        <th>Reserve</th>
                    </tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                $availability = $row['reserved'] == 'Yes' ? "Unavailable" : "Available";  // Availability check
                echo "<tr>
                        
                        <td>{$row['title']}</td>
                        <td>{$row['author']}</td>
                        <td>{$row['genre']}</td>
                        <td>{$row['isbn']}</td>
                        <td>$availability</td>";

                // Show Reserve button only if the book is not reserved
                if ($row['reserved'] == 'No') {
                    echo "<td>
                            <form method='POST' action='student_dashboard.php'>
                                <input type='hidden' name='book_id' value='{$row['book_id']}'>
                                <button type='submit' name='reserve_book'>Reserve</button>
                            </form>
                          </td>";
                } else {
                    echo "<td>Reserved</td>";  // If the book is reserved, show 'Reserved'
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No results found.</p>";
        }
    }


    // Reserve a book
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
</section>

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



<hr>

<!-- View Fines -->
<section id="fines">
    <h2>View Fines</h2>
    <?php
    // Calculate fines based on overdue books
    // Ensure we're looking at books that are not returned and are overdue
    $base_fine = 20;
    $query = "SELECT SUM(DATEDIFF(CURDATE(), r.due_date) * $base_fine) AS fine
              FROM reservations r
              WHERE r.user_id = '$user_id' 
              AND r.returned = 'No' 
              AND r.due_date < CURDATE()";  // Only consider overdue books

    // Execute the query
    $result = mysqli_query($conn, $query);

    // Check if a result is returned
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $fine = $row['fine'] ? $row['fine'] : 0;  // If no fine, set to 0
        echo "<p>Total Fine: $fine BDT</p>";
    } else {
        echo "<p>Error in calculating fine.</p>";
    }
    ?>
</section>
</body>
</html>