<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection Details (Zati's Database)
$host = "10.144.6.19";
$user = "aisar";
$pass = "abc123";
$db = "ws2";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle register logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signUp'])) {
    $staffID = trim($_POST['staffid']);
    $name = trim($_POST['Name']);
    $email = trim($_POST['email']);
    $phoneNum = trim($_POST['phoneno']);
    $position = trim($_POST['position']);
    $departmentName = trim($_POST['departmentname']);

    // Check if email already exists
    $query = "SELECT * FROM STAFF WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!');</script>";
    } else {
        // Insert new user into the database
        $insertQuery = "INSERT INTO STAFF (StaffID, Name, Email, Phone_Num, Position, Department_Name) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssss", $staffID, $name, $email, $phoneNum, $position, $departmentName);
        if ($stmt->execute()) {
           echo "<script>alert('Registration successful! Please sign in.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Registration failed! Please try again.');</script>";
        }
    }
    $stmt->close();
}
?>