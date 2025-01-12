<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');
$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID'];  // Retrieve StaffID from session

$currentPage = 'assets';

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Assets - Asset Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }

        .container {
            flex-direction: column;
            align-items: center; /* Center content horizontally */
            justify-content: center; /* Center content vertically */
            width: 100%; /* Full width */
            max-width: 1250px;
            height: 100vh; /* Full viewport height */
            position: relative;
            overflow-x: hidden; /* Avoid horizontal scrolling */
            padding-top: 20px; /* Add some space at the top */
        }

        .menu {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            gap: 25px; /* Spacing between buttons */
            justify-content: center; /* Align buttons to the left */
            padding: 15px;
            width: 100%; /* Adjust width if needed */
            max-width: 1250px;
        }

        .dropbtn2 {
            background-color:rgb(64, 156, 255);
            color: #ffffff;
            font-size: 15px;
            font-weight: lighter;
            padding: 9px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            white-space: nowrap; 
        }

        .dropdown2 {
        position: relative;
        display: inline-block;
        }

        .dropdown-content2 {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        min-width: 200px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        }

        .dropdown-content2 a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        }

        .dropdown-content2 a:hover {background-color: #ddd;}

        .dropdown2:hover .dropdown-content2 {display: block;}

        .dropdown2:hover .dropbtn2 {background-color: #0056b3;}
    </style>
</head>

<body>
<div class="side-menu">
    <div class="brand-name">
        <h1>PSTP - Asset Management System</h1>
    </div>
    <div class="profile">
        <p>Hello,</p>
        <h3><?php echo htmlspecialchars($staffName); ?> !</h3>
    </div>
    <ul>
        <li>
            <a href="dashboard.php">
                <i class="fa-solid fa-table-columns"></i> &nbsp; <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="account.php">
                <i class="fa-solid fa-user"></i> &nbsp; Account
            </a>
        </li>
        <li>
            <a href="alerts.php">
                <i class="fa-solid fa-bell"></i> &nbsp; Alerts
            </a>
        </li>
        <li>
            <i class="fa-solid fa-money-check-dollar" style="margin-right: 10px; color: white;"></i> 
            &nbsp; <span style="color: white">Assets</span>
            <div class="dropdown">
                <a class="<?= isActive('assets'); ?>" href="form.php">Form</a>
                <a href="assets_assign.php">Assign</a>
            </div>
        </li>
        <li>
            <a href="reports.php">
                <i class="fa-solid fa-circle-exclamation"></i> &nbsp; <span>Reports</span>
            </a>
        </li>
        <li style="list-style: none; margin-top: 10px;">
            <a href="index.php">
                <i class="fa-solid fa-right-from-bracket" style="margin-right: 10px; color: red;"></i>
                <span style="color: red;">Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="container">
    <div class="menu">
        <div class="dropdown2">
            <button class="dropbtn2">Registration</button>
            <div class="dropdown-content2">
                <a href="#">Penerimaan Aset Alih</a>
                <a href="#">Penolakan Aset Alih</a>
                <a href="#">Daftar Aset Tetap Dibeli</a>
            </div>
        </div>
        
        <div class="dropdown2">
            <button class="dropbtn2">Use, Storage and Inspection</button>
            <div class="dropdown-content2">
                <a href="#">Daftar Pergerakan Aset Alih</a>
                <a href="#">Membawa Keluar Aset</a>
                <a href="#">Aduan Kerosakan Aset Alih</a>
            </div>
        </div>
        
        <div class="dropdown2">
            <button class="dropbtn2">Maintenance</button>
            <div class="dropdown-content2">
                <a href="#">Daftar Penyelenggaraan Aset</a>
            </div>
        </div>
        
        <div class="dropdown2">
            <button class="dropbtn2">Disposal</button>
            <div class="dropdown-content2">
                <a href="disposal.php">Permohonan Pelupusan Aset Tetap</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
