<?php
//include 'db_connection.php';
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');
$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID']; // Retrieve StaffID from session

$currentPage = 'assets';

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $AssetID = isset($_POST['AssetID']) ? htmlspecialchars($_POST['AssetID']) : null;
    $Category = isset($_POST['Category']) ? htmlspecialchars($_POST['Category']) : null;
    $Location = isset($_POST['Location']) ? htmlspecialchars($_POST['Location']) : null;
    $StaffID = isset($_POST['StaffID']) ? htmlspecialchars($_POST['StaffID']) : null;
    $Status = isset($_POST['Status']) ? htmlspecialchars($_POST['Status']) : null;

    // Handle multiple rows of equipment data
    $assetData3 = isset($_POST['asset_data']) ? $_POST['asset_data'] : [];
    $MaintenanceID = isset($_POST['MaintenanceID']) ? $_POST['MaintenanceID'] : null;
    $MaintenanceDate = isset($_POST['MaintenanceDate']) ? $_POST['MaintenanceDate'] : null;
    $MaintenanceType = isset($_POST['Date_Received']) ? $_POST['Date_Received'] : null;
    $Details = isset($_POST['Details']) ? $_POST['Details'] : null;
    $Invoice = isset($_POST['Invoice']) ? $_POST['Invoice'] : null;
    $SupplierID = isset($_POST['SupplierID']) ? $_POST['SupplierID'] : null;
    $Cost = isset($_POST['Cost']) ? $_POST['Cost'] : null;

    echo "<pre>POST Data: ";
    var_dump($_POST);
    echo "</pre>";

    try {
        $conn->begin_transaction();

        foreach ($_POST['asset_data']['MaintenanceID'] as $index => $MaintenanceID) {
            $MaintenanceDate = htmlspecialchars($_POST['asset_data']['MaintenanceDate'][$index]);
            $MaintenanceType = htmlspecialchars($_POST['asset_data']['MaintenanceType'][$index]);
            $Details = htmlspecialchars($_POST['asset_data']['Details'][$index]);
            $Invoice = htmlspecialchars($_POST['asset_data']['Invoice'][$index]);
            $SupplierID = htmlspecialchars($_POST['asset_data']['SupplierID'][$index]);
            $Cost = htmlspecialchars($_POST['asset_data']['Cost'][$index]);

            // Insert into Equipment table
            $sql_maintenance = "INSERT INTO Maintenance (MaintenanceID, MaintenanceDate, MaintenanceType, Details, Invoice, Cost) 
                             VALUES ('$MaintenanceID', '$MaintenanceDate', '$MaintenanceType', '$Details', '$Invoice', '$Cost')";
            if ($conn->query($sql_maintenance) === FALSE) {
                throw new Exception("Maintenance Query Error: " . $conn->error);
            }

            // Update query
            $sql_assetupdate = " UPDATE Asset SET 
            Location = '$Location', 
            WHERE AssetID = '$assetID'";

            // Execute query
            if ($conn->query($sql_assetupdate) === FALSE) {
                throw new Exception("Asset Update Error: " . $conn->error);
            }  
        }

        $conn->commit();
       // echo "All data inserted successfully.";
       header("Location: maintenance.php?success=true"); // Redirect with a success parameter
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
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
    <title>Assets - Asset Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Existing styles */
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
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1250px;
            position: relative;
            overflow-x: hidden;
            padding-top: 20px;
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

        .cards-disposal {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            width: 1205px;
            background: white;
            margin: 10px;
            margin-top: 35px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border: none;
            border-radius: 10px;
        }
        .card-disposal h7 {
        color: black;
        font-weight: bold;
        font-size: 25px;
        margin: 10px; /* Adds padding from the box edges */
        position: relative; /* Ensures it stays inside the container */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        #formControls {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        #addRowBtn, #submitBtn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }
        #addRowBtn {
            background-color: #28a745;
        }
        #submitBtn {
            background-color: black;
            margin-bottom: 30px;
        }
        #requesterDetails {
            flex-direction:column-reverse;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            width: 100%;
            margin-top: 30px;
        }
        #requesterDetails input {
            padding: 7px;
            border: 2px solid #ccc;
            border-radius: 6px;
            width: 200px;
            margin-top: 5px;
        }
    </style>
    <script>
        function addRow() {
            const table = document.getElementById("assetTable");
            const row = table.insertRow(-1); // Add row at the end
            row.innerHTML = `
                <td><input type="text" name="asset_data[MaintenanceID][]" required></td>
                <td><input type="text" name="asset_data[MaintenanceDate][]" required></td>
                <td><input type="text" name="asset_data[MaintenanceType][]" required></td>
                <td><input type="text" name="asset_data[Details][]" required></td>
                <td>
                <select name="AssetID" required>
                    <option value="">Select Invoice</option>
                    <?php
                        $result = $conn->query("SELECT AssetID, Local_Order_No FROM Asset");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['AssetID'] . "'>" . $row['Local_Order_No'] . "</option>";
                        }
                    ?>
                </select>
                </td>                
                <td>
                    <select name="asset_data[SupplierID][]" required>
                    <option value="">Select Supplier</option>
                <?php
                    $result = $conn->query("SELECT SupplierID, Supplier_Name FROM Supplier");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['SupplierID'] . "'>" . $row['Supplier_Name'] . "</option>";
                    }
                ?>
                    </select>
                </td>
                <td><input type="text" name="asset_data[Cost][]" required></td>

                <td><button type="button" onclick="deleteRow(this)">Remove</button></td>
            `;
        }

        function deleteRow(button) {
            const row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    </script>
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
                <a href="maintenance.php">Daftar Penyelenggaraan Aset</a>
            </div>
        </div>
        
        <div class="dropdown2">
            <button class="dropbtn2">Disposal</button>
            <div class="dropdown-content2">
                <a href="disposal.php">Permohonan Pelupusan Aset Tetap</a>
            </div>
        </div>
</div>

<div class="content">
        <div class="cards-disposal">
            <div class="card-disposal">
<form action="maintenance.php" method="POST">

    <h2>Movable Asset Maintenance Register</h2>
    <h2>KEW.PA-16</h2>

    <table>
        <tr>
            <th>No. Registration Series</th>
            <td>
            <select name="AssetID" id = "AssetID" required>
                <option value="">Select AssetID</option>
                <?php
                    $result = $conn->query("SELECT AssetID FROM Asset");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['AssetID'] . "'>" . $row['AssetID'] . "</option>";
                    }
                ?>
            </select>
            </td>            
        </tr>
        <tr>
            <th>Category</th>
            <td><input type="text" id="Category" name="Category" readonly></td>         
        </tr>
        <tr>
            <th>Location</th>
            <td><input type="text" id="Location" name="Location" readonly></td>
        </tr>
        <tr>
            <th>Staff</th>
            <td><input type="text" id="StaffID" name="StaffID" readonly></td>        
        </tr>
    </table>

    <table id="assetTable">
        <thead>
        <tr>
            <th>MaintenanceID</th>
            <th>Date</th>
            <th>Maintenance Type B/C</th>
            <th>Maintenance Details</th>
            <th>Local Order No</th>
            <th>Name of Company/Department Who Maintains</th>
            <th>Cost</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="text" name="asset_data[MaintenanceID][]" required pattern="[A-Za-z0-9-]+" title="Only letters, numbers, and hyphens are allowed."></td>
                <td><input type="date" name="asset_data[MaintenanceDate][]" required> </td> 
                <td><input type="text" name="asset_data[MaintenanceType][]" required pattern="[A-Za-z0-9-]+" title="Only letters, numbers, and hyphens are allowed."></td>
                <td><input type="text" name="asset_data[Details][]" required pattern="[A-Za-z0-9-]+" title="Only letters, numbers, and hyphens are allowed."></td>
                <td>
                <select name="AssetID" required>
                    <option value="">Select Invoice</option>
                    <?php
                        $result = $conn->query("SELECT AssetID, Local_Order_No FROM Asset");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['AssetID'] . "'>" . $row['Local_Order_No'] . "</option>";
                        }
                    ?>
                </select>
                </td>
                <td>
                    <select name="asset_data[SupplierID][]" required>
                    <option value="">Select Supplier</option>
                <?php
                    $result = $conn->query("SELECT SupplierID, Supplier_Name FROM Supplier");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['SupplierID'] . "'>" . $row['Supplier_Name'] . "</option>";
                    }
                ?>
                    </select>
                </td>
                <td><input type="number" name="Cost" required> </td> 

                <td><button type="button" onclick="deleteRow(this)">Remove</button></td>
            </tr>
        </tbody>
    </table>
    <button type="button" onclick="addRow()">Add Row</button>
    <br><br>
    <button type="submit">Submit</button>
    <?php
        if (isset($_GET['success']) && $_GET['success'] === 'true') {
            echo "<p>All data inserted successfully!</p>";
        }
    ?>
    </div>
        </div>
</div>
</form>
</body>
</html>