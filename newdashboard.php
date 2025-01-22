<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');
$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID'];

$currentPage = 'dashboard'; // This can be dynamically set

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

// Zati's Database Connection Details
$zati_host = "10.144.6.19";
$zati_user = "aisar";
$zati_pass = "abc123";
$zati_db = "ws2";


// Create connection
$conn_zati = new mysqli($zati_host, $zati_user, $zati_pass, $zati_db);

// Check connection
if ($conn_zati->connect_error) {
    die("Connection failed: " . $conn_zati->connect_error);
}

// 1. Number of Assets (Zati's DB)
$sql_total_assets = "SELECT COUNT(*) AS total_assets FROM ws2.asset";
$result_total_assets = $conn_zati->query($sql_total_assets);
$totalAssets = 0;
if($result_total_assets && $result_total_assets->num_rows > 0){
  $row = $result_total_assets->fetch_assoc();
    $totalAssets = $row['total_assets'] ?? 0;
}


// 2. Assets On Loan (My DB)
$sql_assets_on_loan = "SELECT COUNT(*) AS assets_on_loan FROM borrowerasset WHERE status = 'borrowed'";
$result_assets_on_loan = $conn->query($sql_assets_on_loan);
$assetsOnLoan = 0;
if($result_assets_on_loan && $result_assets_on_loan->num_rows > 0){
     $row = $result_assets_on_loan->fetch_assoc();
    $assetsOnLoan =  $row['assets_on_loan'] ?? 0;
}



// 3. Assets Available (calculated)
$assetsAvailable = $totalAssets - $assetsOnLoan;

// 4. Value of Assets (Zati's DB)
$sql_value_of_assets = "SELECT SUM(Original_Purchase_Price) AS total_value FROM ws2.equipment";
$result_value_of_assets = $conn_zati->query($sql_value_of_assets);
$valueOfAssets = 0;
if ($result_value_of_assets && $result_value_of_assets->num_rows > 0) {
   $row = $result_value_of_assets->fetch_assoc();
    $valueOfAssets = $row && $row['total_value'] !== null ? $row['total_value'] : 0;
}


// 5. Net Assets Value (Zati's DB, with depreciation calculation)
$sql_assets = "SELECT asset.Date_Received, Equipment.Original_Purchase_Price as original_price
            FROM ws2.asset
             INNER JOIN ws2.Equipment ON asset.Manufacturer_Series = Equipment.Manufacturer_Series";
$result_assets = $conn_zati->query($sql_assets);
$netAssetsValue = 0;

if ($result_assets && $result_assets->num_rows > 0) {
    while ($row = $result_assets->fetch_assoc()) {
           $purchaseDate = new DateTime($row['Date_Received']);
          $today = new DateTime();
           $diffYears = (int)$today->diff($purchaseDate)->format('%y');
         $originalPrice = $row['original_price'];
        $depreciationRate = min($diffYears * 0.09, 0.9);
            $currentPrice = $originalPrice * (1 - $depreciationRate);

       $netAssetsValue += $currentPrice > 1 ? $currentPrice : 1;
   }
}
$netAssetsValue = round($netAssetsValue, 2);

// 6. Purchase in This Year (Zati's DB)
$currentYear = date('Y');
 $sql_purchase_this_year = "SELECT SUM(Equipment.Original_Purchase_Price) AS total_purchase FROM ws2.asset
             INNER JOIN ws2.Equipment ON asset.Manufacturer_Series = Equipment.Manufacturer_Series WHERE YEAR(asset.Date_Received) = '$currentYear'";
$result_purchase_this_year = $conn_zati->query($sql_purchase_this_year);
 $purchaseThisYear = 0;
 if ($result_purchase_this_year && $result_purchase_this_year->num_rows > 0) {
        $row = $result_purchase_this_year->fetch_assoc();
        $purchaseThisYear = $row['total_purchase'] ?? 0;
     }

// 7. Overdue Loan (My DB)
$sql_overdue_loan = "SELECT COUNT(*) AS overdue_count FROM borrowerasset WHERE EndDate < CURDATE() AND status = 'borrowed'";
$result_overdue_loan = $conn->query($sql_overdue_loan);
$overdueLoan = 0;
 if ($result_overdue_loan && $result_overdue_loan->num_rows > 0) {
        $row = $result_overdue_loan->fetch_assoc();
      $overdueLoan = $row['overdue_count'] ?? 0;
    }

// 8. Disposal Records (My DB)
$sql_disposal_records = "SELECT COUNT(*) AS disposal_count FROM disposal WHERE ApprovalStatus = 'Disposal Approved'";
$result_disposal_records = $conn->query($sql_disposal_records);
$disposalRecords = 0;
 if ($result_disposal_records && $result_disposal_records->num_rows > 0) {
        $row = $result_disposal_records->fetch_assoc();
      $disposalRecords = $row['disposal_count'] ?? 0;
}

// Fetch the count of pending disposals
$sql_pending_count = "SELECT COUNT(*) AS pending_count FROM disposal WHERE ApprovalStatus IS NULL OR ApprovalStatus = ''";
$result_pending_count = $conn->query($sql_pending_count);
$pendingCount = ($result_pending_count && $result_pending_count->num_rows > 0) ? $result_pending_count->fetch_assoc()['pending_count'] : 0;


// Fetch assets borrowed by the user from YOUR database first
$sql_borrowed_by_user_local = "SELECT AssetID, StartDate, EndDate FROM borrowerasset WHERE StaffID = ? AND Status = 'borrowed'";
$stmt_borrowed_by_user_local = $conn->prepare($sql_borrowed_by_user_local);
$stmt_borrowed_by_user_local->bind_param("s", $staffID);
$stmt_borrowed_by_user_local->execute();
$result_borrowed_by_user_local = $stmt_borrowed_by_user_local->get_result();

$borrowedAssetsByUser = [];
if ($result_borrowed_by_user_local->num_rows > 0) {
    while ($row_local = $result_borrowed_by_user_local->fetch_assoc()) {
         $assetID = $row_local['AssetID'];
      // Fetch asset details from Zati's database
        $sql_asset_details = "SELECT e.Type AS asset_type, e.Brand_Model AS asset_brand
                        FROM ws2.asset a
                        JOIN ws2.equipment e ON a.Manufacturer_Series = e.Manufacturer_Series
                        WHERE a.AssetID = ?";
        $stmt_asset_details = $conn_zati->prepare($sql_asset_details);
         $stmt_asset_details->bind_param("s", $assetID);
          $stmt_asset_details->execute();
          $result_asset_details = $stmt_asset_details->get_result();

        $assetDetails = ($result_asset_details && $result_asset_details->num_rows > 0) ? $result_asset_details->fetch_assoc() : ['asset_type' => 'Unknown', 'asset_brand' => 'Unknown'];

        $borrowedAssetsByUser[] = array_merge($row_local, $assetDetails);
          $stmt_asset_details->close();
    }
}
$stmt_borrowed_by_user_local->close();

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
         .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
              gap: 20px;
            padding: 20px;
            margin-top: 30px;
            max-width: 1250px;
             width: 100%;
        }
        .card {
           background-color: #ffffff;
              border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            width: 280px; /* Or any width that fit your requirement */
              height: 120px;
            padding: 15px;
          display: flex;
            flex-direction: row;
          justify-content: space-between;
           align-items: center;
        }
         .card .box {
            text-align: left;
            line-height: 1.3;
        }
        .card .box h4 {
            margin: 0;
            font-size: 1.1em;
            font-weight: bold;
        }
        .card .box h5 {
            margin: 0;
            margin-top: 4px;
            font-size: 1.5em;
            font-weight: bold;
        }

        .card .icon-case {
             display: flex;
             align-items: center;
             font-size: 22px;
         }
         .card .icon-case i {
             margin-left:10px;
         }
           .asset-button {
            background-color: rgb(64, 156, 255);
            color: #ffffff;
            font-size: 15px;
            font-weight: lighter;
            padding: 9px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            white-space: nowrap;
             text-decoration: none;
        }
       .asset-button.active, .asset-button:hover {
             background-color: #0056b3; /* Darker color when active or hovered */
        }
     .asset-nav {
            display: flex;
            gap: 10px; /* Space between buttons */
              margin-bottom: 15px; /* Space above the table */
      }
    .borrowed-table {
        width: 110%;
        border-collapse: collapse;
         margin-top: 20px;
         margin-bottom: 20px;
         background-color: white;
         box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
         border-radius: 10px;
         overflow-x: auto;
        }
    .borrowed-table th,
    .borrowed-table td {
        border: 1px solid #ddd;
        padding: 10px;
         text-align: left;
        }
     .borrowed-table th {
        background-color: #D6EEEE;
     }
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
             overflow-x: auto;
        }
        .card-disposal h7 {
        color: black;
        font-weight: bold;
        font-size: 25px;
        margin: 10px; /* Adds padding from the box edges */
        position: relative; /* Ensures it stays inside the container */
        margin-bottom: 20px; /* Lower the heading */
    }

    .h7{
        margin-top: 10px;
    }
    </style>

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
            <li  class="<?= isActive('dashboard'); ?>"><a href="dashboard.php"><i class="fa-solid fa-table-columns"></i>   <span>Dashboard</span></a></li>
            <li><a href="account.php"><i class="fa-solid fa-user"></i>   Account</a></li>
            <?php if ($_SESSION['staff']['Position'] === 'Head of Department') : ?>
                <li>
                    <a href="approval.php"><i class="fa-solid fa-file-circle-check"></i>  Approval
                    <?php if ($pendingCount > 0): ?>
                        <span style="background-color: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 12px; vertical-align: middle; margin-left: 2px;"><?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if ($_SESSION['staff']['Position'] === 'Head of Department' || $_SESSION['staff']['Position'] === 'PTj Asset Officer') : ?>
                <li style="list-style: none;">
                    <a href="http://10.144.6.19/login-website/assign.php">
                        <i class="fa-solid fa-bell"></i>   Assigns
                    </a>
            </li>
            <?php endif; ?>
        <?php if ($_SESSION['staff']['Position'] === 'User') : ?>
            <li style="list-style:none;">
                <a href="borrow.php"><i class="fa-solid fa-table-columns"></i>  <span>Borrow</span></a>
            </li>
        <?php else: ?>
            <li>
                <i class="fa-solid fa-money-check-dollar"></i>   Assets
            <div class="dropdown">
                    <a href="form.php">Application</a>
                    <a href="borrowed_assets.php">Borrowed Asset</a>
                </div>
            </li>
            <?php endif; ?>
            <?php if ($_SESSION['staff']['Position'] === 'Head of Department') : ?>
            <li>
                <a href="backuprecovery.php">
                    <i class="fa-solid fa-circle-exclamation"></i>   <span>Admin</span>
                </a>
            </li>
            <?php endif; ?>
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
                    <h4>Number of Assets</h4>
                    <h5><?php echo htmlspecialchars($totalAssets); ?></h5>
                </div>
                <div class="icon-case">
                     <i class="fa-solid fa-list"></i>
                </div>
            </div>
            <div class="card">
            <a href="borrowed_assets.php" style="color: inherit; display:flex; flex-direction: row; justify-content: space-between; align-items: center;">
                <div class="box">
                    <h4>Assets On Loan</h4>
                    <h5><?php echo htmlspecialchars($assetsOnLoan); ?></h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                </a>
            </div>
            <div class="card">
               <a href="assets_available.php" style="color: inherit; display:flex; flex-direction: row; justify-content: space-between; align-items: center;">
                <div class="box">
                    <h4 >Assets Available</h4>
                     <h5><?php echo htmlspecialchars($assetsAvailable); ?></h5>
                </div>
                <div class="icon-case" style="margin-left:0px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                 </a>
            </div>
            <div class="card">
                <div class="box">
                    <h4>Value of Assets</h4>
                    <h5>RM<?php echo htmlspecialchars(number_format($valueOfAssets, 2)); ?></h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
            <div class="card">
                <div class="box">
                    <h4>Net Assets Value</h4>
                    <h5>RM<?php echo htmlspecialchars(number_format($netAssetsValue, 2)); ?></h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
            <div class="card">
                <div class="box">
                    <h4>Purchases This Year</h4>
                    <h5>RM<?php echo htmlspecialchars(number_format($purchaseThisYear, 2)); ?></h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-list"></i>
                </div>
            </div>
             <div class="card">
                <a href="overdueloan.php" style="color: inherit; display:flex; flex-direction: row; justify-content: space-between; align-items: center;">
                <div class="box">
                    <h4>Overdue Loan</h4>
                    <h5><?php echo htmlspecialchars($overdueLoan); ?> Items</h5>
                </div>
                <div class="icon-case">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                </a>
            </div>
            <div class="card">
                <a href="disposal_records.php" style="color: inherit; display:flex; flex-direction: row; justify-content: space-between; align-items: center;">
                <div class="box">
                    <h4>Disposal Records</h4>
                   <h5><?php echo htmlspecialchars($disposalRecords); ?> Items</h5>
                </div>
               <div class="icon-case">
                <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                </a>
            </div>
        </div>
             <div class="cards-disposal" style="padding: 15px 15px;margin-top:15px;">
                <div class="card-disposal">
                       <h7>Assets You Are Currently Borrowing</h7>
                         <?php if(!empty($errors)): ?>
                           <div style="color: red; margin-bottom: 10px; padding: 10px; background-color: #ffe0e0; border: 1px solid #ffaaaa; border-radius: 5px;">
                              <ul>
                            <?php foreach ($errors as $error): ?>
                               <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                               </ul>
                            </div>
                          <?php endif; ?>
                     <table class="borrowed-table">
                        <thead>
                            <tr>
                                <th style="background-color: #D6EEEE">ASSET ID</th>
                                <th style="background-color: #D6EEEE">TYPE OF ASSET</th>
                                <th style="background-color: #D6EEEE">BRAND OF ASSET</th>
                                <th style="background-color: #D6EEEE">BORROWING DATE</th>
                                 <th style="background-color: #D6EEEE">RETURN DATE</th>
                            </tr>
                        </thead>
                       <tbody>
                            <?php if (!empty($borrowedAssetsByUser)): ?>
                                <?php foreach ($borrowedAssetsByUser as $asset): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($asset['AssetID']); ?></td>
                                         <td><?php echo htmlspecialchars($asset['asset_type']); ?></td>
                                        <td><?php echo htmlspecialchars($asset['asset_brand']); ?></td>
                                         <td><?php echo htmlspecialchars($asset['StartDate']); ?></td>
                                        <td><?php echo htmlspecialchars($asset['EndDate']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No assets currently borrowed.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                  </div>
            </div>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
$conn_zati->close();
?>
