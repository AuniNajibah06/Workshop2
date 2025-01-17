<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');

$staffID = $_SESSION['staff']['StaffID'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone_num = trim($_POST['phone_num']);
$position = trim($_POST['position']);
$department = trim($_POST['department']);

$query = "UPDATE STAFF SET Name = ?, Email = ?, Phone_Num = ?, Position = ?, Department_Name = ? WHERE StaffID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $name, $email, $phone_num, $position, $department, $staffID);
if ($stmt->execute()) {
    header("Location: account.php");
} else {
    echo "Failed to update details. Please try again.";
}
?>
