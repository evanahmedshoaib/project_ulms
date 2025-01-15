<?php
session_start();
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    // Fetch user based on user_id
    $query = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['password']) {
            // Start session and redirect based on role
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: pages/admin_dashboard.php");
                exit;
            } elseif ($user['role'] == 'staff') {
                header("Location: pages/staff_dashboard.php");
                exit;
            } elseif ($user['role'] == 'student') {
                header("Location: pages/student_dashboard.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Invalid password.";
        }
    } else {
        $_SESSION['error_message'] = "User not found.";
    }

    // Redirect back to the login page
    header("Location: index.php");
    exit;
}
?>