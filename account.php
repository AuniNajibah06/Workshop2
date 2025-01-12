<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');
$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID'];  // Retrieve StaffID from session

$currentPage = 'account'; 

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

$query = "SELECT * FROM STAFF WHERE StaffID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $staffID);  // Bind the staffID to the prepared statement
$stmt->execute();
$result = $stmt->get_result();
$staffData = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Account - Asset Management System</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const img = document.getElementById('profilePreview');
                img.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
<div class="side-menu">
    <div class="brand-name">
        <h1>PSTP - Asset Management System</h1>
    </div>
    <div class="profile">
    <p><?php echo 'Hello, '; ?></p> <h3><?php echo htmlspecialchars($staffName); ?><?php echo ' !'; ?></h3>
    </div>
    
    <ul>
    <li>
        <a href="dashboard.php">
            <i class="fa-solid fa-table-columns" alt="" ></i> &nbsp; <span>Dashboard</span>
        </a>
    </li>
    <li class="<?= isActive('account'); ?>">
        <a href="account.php">
            <i class="fa-solid fa-user" alt="" ></i> &nbsp; Account
        </a>    
    </li>
    <li>
        <a href="alerts.php">
            <i class="fa-solid fa-bell" alt="" ></i> &nbsp; Alerts
        </a>
    </li>
    <li>
            <i class="fa-solid fa-money-check-dollar" alt="" ></i> &nbsp; Assets
                <div class="dropdown">
                    <a href="form.php">Form</a>
                    <a href="assets_assign.php">Assign</a>
                </div>
        </a>
    </li>
    <li>
        <a href="reports.php">
            <i class="fa-solid fa-circle-exclamation" alt="" ></i> &nbsp; <span>Reports</span>
        </a>
    </li>
    <li style="list-style:none; margin-top:10px;">
    <a href="index.php">
        <i class="fa-solid fa-right-from-bracket" style="margin-right:10px; color:red;"></i> 
        <span style="color:red;">Logout</span>
    </a>
    </li>
    </ul>
</div>
<div class="container_account">
    <div class="account-info">
        <h2>Account Details</h2>
        <p><strong>Staff ID:</strong> <?php echo $staffData['StaffID']; ?></p>
        <p><strong>Name:</strong> <?php echo $staffData['Name']; ?></p>
        <p><strong>Email:</strong> <?php echo $staffData['Email']; ?></p>
        <p><strong>Phone Number:</strong> <?php echo $staffData['Phone_Num']; ?></p>
        <p><strong>Position:</strong> <?php echo $staffData['Position']; ?></p>
        <p><strong>Department:</strong> <?php echo $staffData['Department_Name']; ?></p>
    </div>
</div>

</body>
</html>
