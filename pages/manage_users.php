<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
<h1>Welcome, Admin</h1>

<!-- Include Navbar -->
<?php include 'navbar.php'; ?>

<hr>

<!-- Manage Users -->
<section id="manage-users">
    <h2>Manage Users</h2>
    <form method="POST" action="manage_users.php">
        <label for="user_id">User ID:</label>
        <input type="text" name="user_id" required><br>
        <label for="name">Name:</label>
        <input type="text" name="name" required><br>
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        <label for="password">Password:</label>
        <input type="text" name="password" required><br>
        <label for="role">Role:</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="staff">Library Staff</option>
            <option value="student">Student</option>
        </select><br>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <?php
    // Add User
    if (isset($_POST['add_user'])) {
        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $query = "INSERT INTO users (user_id, name, email, password, role) VALUES ('$user_id', '$name', '$email', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            echo "<p>User added successfully!</p>";
        } else {
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }
    ?>

    <h3>Existing Users</h3>
    <table border="1">
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Password</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM users");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
            <form method='POST' action='admin_dashboard.php'>
                <td>
                    <input type='text' name='user_id' value='{$row['user_id']}'>
                    <input type='hidden' name='original_user_id' value='{$row['user_id']}'>
                </td>
                <td><input type='text' name='name' value='{$row['name']}'></td>
                <td><input type='text' name='email' value='{$row['email']}'></td>
                <td><input type='text' name='password' value='{$row['password']}'></td>
                <td>
                    <select name='role'>
                        <option value='admin' " . ($row['role'] === 'admin' ? 'selected' : '') . ">Admin</option>
                        <option value='staff' " . ($row['role'] === 'staff' ? 'selected' : '') . ">Library Staff</option>
                        <option value='student' " . ($row['role'] === 'student' ? 'selected' : '') . ">Student</option>
                    </select>
                </td>
                <td>
                    <button type='submit' name='update_user'>Update</button>
                    <button type='submit' name='delete_user'>Delete</button>
                </td>
            </form>
          </tr>";
        }
        ?>
    </table>

    <?php
    // Update User
    if (isset($_POST['update_user'])) {
        $original_user_id = $_POST['user_id']; // This is from the form's current user_id input
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Find the original user ID for this row
        $original_id = $_POST['original_user_id'];

        $query = "UPDATE users SET user_id='$original_user_id', name='$name', email='$email', password='$password', role='$role' WHERE user_id='$original_id'";

        if (mysqli_query($conn, $query)) {
            echo "<p>User updated successfully!</p>";
            header("Refresh:0");
        } else {
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }

    // Delete User
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $query = "DELETE FROM users WHERE user_id='$user_id'";
        if (mysqli_query($conn, $query)) {
            echo "<p>User deleted successfully!</p>";
            header("Refresh:0");
        } else {
            echo "<p>Error: " . mysqli_error($conn) . "</p>";
        }
    }
    ?>
</section>

<hr>
</body>
</html>