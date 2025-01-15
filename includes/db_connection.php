<?php
$conn = mysqli_connect("localhost", "admin", "admin", "library_db");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
