<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');
$staffName = $_SESSION['staff']['Name'];

$currentPage = 'dashboard'; // This can be dynamically set

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
    <title>Dashboard - Asset Management System</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
    <li class="<?= isActive('dashboard'); ?>">
        <a href="dashboard.php">
            <i class="fa-solid fa-table-columns" alt="" ></i> &nbsp; <span>Dashboard</span>
        </a>
    </li>
    <li>
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
<div class="container">
    <div class="content">
        <div class="cards">
            <div class="card">
                <div class="box">
                    <h4>Total Assets</h4>
                    <h5>2194</h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
            <div class="card">
                <div class="box">
                    <h4>Net Asset Value</h4>
                    <h5>RM2194</h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
            <div class="card">
                <div class="box">
                    <h4>Value of Assets</h4>
                    <h5>RM2194</h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
            <div class="card">
                <div class="box">
                    <h4>Purchase in a Year</h4>
                    <h5>2194</h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>