<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');

// Get staff information from the session
$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID'];

$currentPage = 'maintenance_disp';

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

// Fetch all maintenance records
$query_all = "SELECT MaintenanceID, StaffID, RequestorID, AssetID, MaintenanceDate, MaintenanceType, Details, Cost, Status FROM Maintenance";
$result_all = $conn1->query($query_all);

// Fetch pending maintenance records
$query_pending = "SELECT MaintenanceID, StaffID, RequestorID, AssetID, MaintenanceDate, MaintenanceType, Details, Cost FROM Maintenance WHERE Status = 'Pending'";
$result_pending = $conn1->query($query_pending);

// Handle form submission for status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        foreach ($_POST['MaintenanceID'] as $index => $MaintenanceID) {
            $newStatus = $_POST['Status'][$index];

            // Check if staffID is PI
            if (strpos($staffID, 'HD') === 0) {
                if (strpos($newStatus, 'Pending') === 0) {
                    $Staff = NULL;
                    $stmt = $conn1->prepare("UPDATE Maintenance SET Status = ?, StaffID = ? WHERE MaintenanceID = ?");
                    $stmt->bind_param("ssi", $newStatus, $Staff, $MaintenanceID);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $conn1->prepare("UPDATE Maintenance SET Status = ?, StaffID = ? WHERE MaintenanceID = ?");
                    $stmt->bind_param("ssi", $newStatus, $staffID, $MaintenanceID);
                    $stmt->execute();
                    $stmt->close();
                    echo "<script>alert('Request update successful!');</script>";
                }
            } else {
                echo "You do not have privilege to change the status for :" . htmlspecialchars($MaintenanceID);
            }
        }
        // Redirect to avoid resubmission on page refresh
        header("Location: display_maintenance.php");
        exit();
    }
}

// Initialize
$maintenanceDetails = "";
$error = "";

// Handle search
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search_maintenance'])) {
    $maintenanceSearch = $conn1->real_escape_string($_POST['maintenanceSearch']);

    $searchMain = "SELECT MaintenanceID, StaffID, RequestorID, AssetID, MaintenanceDate, MaintenanceType, Details, Cost, Status FROM Maintenance WHERE AssetID LIKE '%$maintenanceSearch%' OR RequestorID LIKE '%$maintenanceSearch%'";
    $result = $conn1->query($searchMain);

    if ($result->num_rows > 0) {
        $maintenanceDetails = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "No Assets Registered for the search term";
        echo "<script>alert('$error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Maintenance Records - Asset Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Existing styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1250px;
            position: relative;
            overflow-x: hidden;
            padding-top: 20px;
        }
        h3 {
            color: #007bff;
            margin-bottom: 15px;
        }
        .table-container {
            flex-wrap: wrap;
            width: 1205px;
            background: white;
            padding: 20px 15px;
            border-radius: 8px;
            margin: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border: none;
        }
        table {
            width: 80%;
            max-width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
            margin-top: 10px;
            font-size:14px;
            display: block;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid black;
        }
        th {
            background-color: #f1f1f1;
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
        button, input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #0056b3;
        }
        @media print {

        body * {
        visibility: hidden; /* Hide everything */
        }  
        #printableArea, #printableArea * {
        visibility: visible; /* Show only the printable area */
        }
        #printableArea {
        position: absolute; /* Position it to avoid layout issues */
        left: 0;
        top: 0;
        width: 100%;
        }
        }
        @page {
            size: landscape;
            margin: 10mm;
        }
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
            <li><a href="dashboard.php"><i class="fa-solid fa-table-columns"></i> &nbsp; <span>Dashboard</span></a></li>
            <li><a href="account.php"><i class="fa-solid fa-user"></i> &nbsp; Account</a></li>
            <li><a href="alerts.php"><i class="fa-solid fa-bell"></i> &nbsp; Alerts</a></li>
            <li>
                <i class="fa-solid fa-money-check-dollar" style="margin-right: 10px; color: white;"></i>
                &nbsp; <span style="color: white">Assets</span>
                <div class="dropdown">
                    <a class="<?= isActive('assets'); ?>" href="form.php">Form</a>
                    <a href="assets_assign.php">Assign</a>
                </div>
            </li>
            <li><a href="reports.php"><i class="fa-solid fa-circle-exclamation"></i> &nbsp; <span>Reports</span></a></li>
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
                    <a href="accept.php">Penerimaan Aset Alih</a>
                    <a href="decline.php">Penolakan Aset Alih</a>
                    <a href="Reg.php">Daftar Aset Tetap Dibeli</a>
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
                    <a href="maintenance1.php">Daftar Penyelenggaraan Aset</a>
                    <a href="maintenance_list.php">Senarai Permohonanan</a>
                </div>
            </div>
            <div class="dropdown2">
                <button class="dropbtn2">Disposal</button>
                <div class="dropdown-content2">
                    <a href="disposal.php">Permohonan Pelupusan Aset Tetap</a>
                </div>
            </div>
        </div>

        <!-- Maintenance Records Section -->
        <div class="table-container">
            <h3>Search Maintenance</h3>
            <form method="POST">
                <label for="maintenanceSearch">Asset ID/ Staff ID:</label>
                <input type="text" id="maintenanceSearch" name="maintenanceSearch" placeholder="Search Maintenance" required>
                <input type="submit" name="search_maintenance" value="Search">
            </form>

            <?php if ($maintenanceDetails): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Officer</th>
                            <th>Requestor ID</th>
                            <th>Asset ID</th>
                            <th>Maintenance Date</th>
                            <th>Maintenance Type</th>
                            <th>Details</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maintenanceDetails as $maint): ?>
                            <tr>
                                <td><?= htmlspecialchars($maint['MaintenanceID']); ?></td>
                                <td><?= htmlspecialchars($maint['StaffID']); ?></td>
                                <td><?= htmlspecialchars($maint['RequestorID']); ?></td>
                                <td><?= htmlspecialchars($maint['AssetID']); ?></td>
                                <td><?= htmlspecialchars($maint['MaintenanceDate']); ?></td>
                                <td><?= htmlspecialchars($maint['MaintenanceType']); ?></td>
                                <td><?= htmlspecialchars($maint['Details']); ?></td>
                                <td><?= htmlspecialchars($maint['Cost']); ?></td>
                                <td><?= htmlspecialchars($maint['Status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($error)): ?>
                <p><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </div>

        <!-- Pending Maintenance Section -->
        <div class="table-container">
            <h3>Pending Maintenance Requests</h3>
            <form method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Officer</th>
                            <th>Requestor ID</th>
                            <th>Asset ID</th>
                            <th>Maintenance Date</th>
                            <th>Maintenance Type</th>
                            <th>Details</th>
                            <th>Cost</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_pending->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="MaintenanceID[]" value="<?= htmlspecialchars($row['MaintenanceID']); ?>">
                                    <?= htmlspecialchars($row['MaintenanceID']); ?>
                                </td>
                                <td><?= htmlspecialchars($row['StaffID']); ?></td>
                                <td><?= htmlspecialchars($row['RequestorID']); ?></td>
                                <td><?= htmlspecialchars($row['AssetID']); ?></td>
                                <td><?= htmlspecialchars($row['MaintenanceDate']); ?></td>
                                <td><?= htmlspecialchars($row['MaintenanceType']); ?></td>
                                <td><?= htmlspecialchars($row['Details']); ?></td>
                                <td><?= htmlspecialchars($row['Cost']); ?></td>
                                <td>
                                    <select name="Status[]" required>
                                        <option value="Pending">Pending</option>
                                        <option value="Accepted">Accepted</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <input type="submit" name="update_status" value="Update Status">
            </form>
        </div>

        <!-- All Maintenance Section -->
        <div class="table-container" id="printableArea">
            <h3>All Maintenance Records</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Officer</th>
                        <th>Requestor ID</th>
                        <th>Asset ID</th>
                        <th>Maintenance Date</th>
                        <th>Maintenance Type</th>
                        <th>Details</th>
                        <th>Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_all->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['MaintenanceID']); ?></td>
                            <td><?= htmlspecialchars($row['StaffID']); ?></td>
                            <td><?= htmlspecialchars($row['RequestorID']); ?></td>
                            <td><?= htmlspecialchars($row['AssetID']); ?></td>
                            <td><?= htmlspecialchars($row['MaintenanceDate']); ?></td>
                            <td><?= htmlspecialchars($row['MaintenanceType']); ?></td>
                            <td><?= htmlspecialchars($row['Details']); ?></td>
                            <td><?= htmlspecialchars($row['Cost']); ?></td>
                            <td><?= htmlspecialchars($row['Status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div style="display: flex; justify-content: space-between; align-items: center;">
            <button onclick="window.print()" style="margin-left: auto;">Print</button>
            </div>
        </div>

    </div>
</body>
</html>
