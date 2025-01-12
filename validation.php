<?php
include('db_connection.php');
session_start();

// Handle login logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signIn'])) {
    $email = trim($_POST['email']);
    $staffID = trim($_POST['password']); // Staff ID as the password

    $query = "SELECT * FROM STAFF WHERE Email = ? AND StaffID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['staff'] = $result->fetch_assoc();
        header("Location: dashboard.php");
        exit();
    } else {
        // If credentials are invalid, redirect back to login page
        header("Location: index.php?error=invalid_credentials");
        exit();
    }
    $stmt->close();
}
?>
