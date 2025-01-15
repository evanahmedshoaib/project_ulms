<?php
include 'includes/db_connection.php';

// URL of the API from which books will be fetched
$api_url = "https://openlibrary.org/search.json?q=science"; // Replace with your actual API URL

// Fetch the data from the API
$response = file_get_contents($api_url);
if ($response === FALSE) {
    die("Error fetching data from API.");
}

// Decode the JSON response from the API
$data = json_decode($response, true);

// Loop through each book and insert it into the database
foreach ($data['docs'] as $book) {
    $title = mysqli_real_escape_string($conn, $book['title']);
    $author = isset($book['author_name'][0]) ? mysqli_real_escape_string($conn, $book['author_name'][0]) : "Unknown Author";
    $isbn = isset($book['isbn'][0]) ? mysqli_real_escape_string($conn, $book['isbn'][0]) : null;
    $genre = "Science"; // Example genre
    $thumbnail = "";

    // Skip book if ISBN is missing or empty
    if (empty($isbn)) {
        echo "Skipping book '$title' due to missing ISBN.<br>";
        continue;
    }

    // Check if the book already exists (based on ISBN)
    $check_query = "SELECT * FROM books WHERE isbn='$isbn'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo "Skipping book '$title' with duplicate ISBN: $isbn<br>";
        continue; // Skip inserting if the book already exists
    }

    // If the book doesn't exist, insert it into the database
    $query = "INSERT INTO books (title, author, genre, isbn, source, reserved) 
              VALUES ('$title', '$author', '$genre', '$isbn', 'api', 'No')";

    if (mysqli_query($conn, $query)) {
        echo "Book '$title' added successfully.<br>";
    } else {
        echo "Error adding book '$title': " . mysqli_error($conn) . "<br>";
    }
}

echo "Books successfully populated!";
?>