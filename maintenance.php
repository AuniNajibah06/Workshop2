<?php
// Include database connection
include 'db_connection.php';
include 'integration.php';

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

// Get staff details from session
$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID'];
/*if (!(str_starts_with($staffID, 'PT')||str_starts_with($staffID,'HD'))){
	echo "<h1>Access Denied</h1>";
	exit();
}*/
$currentPage = 'maintenance3';

function isActive($page) {
	global $currentPage;
	return $currentPage == $page ? 'active': '';
}

// Handle form submission to insert maintenance details

	$conn1->begin_transaction();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_maintenance'])) {
		//$mtid = NULL;
        $Staff= NULL;
		$AssetID = $_POST['RegSeries'];
    	$MaintenanceDate = $_POST['MaintenanceDate'];
   	 	$MaintenanceType = $_POST['MaintenanceType'];
    	$Details = $_POST['Details'];
    	$Cost = $_POST['Cost'];
    	//$Status = 'Pending'; // Assuming DEFAULT is a valid database default value

    	// Insert maintenance details
    	$stmt = $conn1->prepare("INSERT INTO Maintenance (StaffID, RequestorID, AssetID, MaintenanceDate, MaintenanceType, Details, Cost) VALUES (?, ?, ?, ?, ?, ?, ?)");
    	$stmt->bind_param("ssssssd", $Staff, $staffID, $AssetID, $MaintenanceDate, $MaintenanceType, $Details, $Cost);

    	if ($stmt->execute()) {
			$conn1->commit();
        	echo "<script>alert('Maintenance request submitted successfully!');</script>";
    	} else {
        	echo "<script>alert('Submission failed. Please try again.');</script>";
    	}
        
	
		// Redirect to avoid resubmission on page refresh
        //header("Location: display_maintenance.php");
        //exit();
    }
}

//Initialize
$assetDetails = "";
$error = "";

//Check the user if Staff is PT or not. PT can see ALL, Others only their assets
if (strpos($staffID, 'PT') === 0) {
	//Handle search all
	if ($_SERVER["REQUEST_METHOD"] === "POST"&& isset($_POST['search_asset'])) {
		$assetSearch = $conn_zaty->real_escape_string($_POST['assetSearch']);

		//$searchasset = "SELECT AssetID, Category, Location, Description FROM asset WHERE StaffID LIKE '%$assetSearch%' OR AssetID LIKE '%$assetSearch%'";
		$searchasset = "SELECT T.AssetID, T.Category, T.Location, E.Type, E.Brand_Model 
		FROM asset T
		JOIN equipment E ON T.Manufacturer_Series = E.Manufacturer_Series
		WHERE StaffID LIKE '%$assetSearch%' OR AssetID LIKE '%$assetSearch%'"; 
		$result = $conn_zaty->query($searchasset);

		if ($result->num_rows > 0) {
			$assetDetails = $result->fetch_all(MYSQLI_ASSOC);
		} else {
			$error = "No Assets Registered for the search term";
			echo "$error";
		}
	}
} else {
	//Handle search asset by staff
	if ($_SERVER["REQUEST_METHOD"] === "POST"&& isset($_POST['search_asset'])) {
		$assetSearch = $conn1->real_escape_string($_POST['assetSearch']);

		//$searchasset = "SELECT AssetID, Category, Location, Description FROM asset WHERE StaffID LIKE '%$assetSearch%' OR AssetID LIKE '%$assetSearch%'";
		$searchasset = "SELECT T.AssetID, T.Category, T.Location, E.Type, E.Brand_Model 
		FROM asset T
		JOIN equipment E ON T.Manufacturer_Series = E.Manufacturer_Series
		WHERE StaffID LIKE '$staffID' AND AssetID LIKE '%$assetSearch%'"; 
		$result = $conn1->query($searchasset);

		if ($result->num_rows > 0) {
			$assetDetails = $result->fetch_all(MYSQLI_ASSOC);
		} else {
			$error = "No Assets Registered for the search term";
			echo "$error";
		}
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
		h2 {
			color: #007bff;
			margin-bottom: 15px;
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

    	.cards-disposal{
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
		.table-container {
			flex-wrap: wrap;
			width:1205px;
            background: white;
            padding: 20px 15px;
            border-radius: 8px;
			margin: 10px;
            margin-bottom: 35px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
			border: none;
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
        	background-color: #007bff;
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

	<div class="table-container">
		<h2>Search Assets</h2>
				<form method = "POST">
					<h8>Search </h8>
					<label for="assetSearch">Asset ID or Staff ID:</label>
					<input type="text" id="assetSearch" name="assetSearch" placeholder="Search Asset" required>
					<input type="submit" name="search_asset" value="Search">
				</form>

				<?php if ($assetDetails): ?>
					<table>
						<tr>
							<th>AssetID</th>
							<th>Category</th>
							<th>Location</th>
							<th>Type</th>
							<th>Brand Model</th>

						</tr>
						<?php foreach ($assetDetails as $asset): ?>
							<tr>
								<td><?= htmlspecialchars($asset['AssetID']); ?></td>
								<td><?= htmlspecialchars($asset['Category']); ?></td>
								<td><?= htmlspecialchars($asset['Location']); ?></td>
								<td><?= htmlspecialchars($asset['Type']); ?></td>
								<td><?= htmlspecialchars($asset['Brand_Model']); ?></td>
							</tr>
							<?php endforeach; ?>
					</table>
					<?php elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($error)): ?>
						<p><?= htmlspecialchars($error); ?></p>
				<?php endif; ?>
	</div>
	<div class="content">
    	<div class="cards-disposal">
        	<div class="card-disposal">
			
				<form method="POST">

				<h2>Movable Asset Maintenance Register (KEW.PA-16)</h2>

				<!--<form method="POST"> -->
				<table>
    				<tr>
        				<th>No. Registration Series</th>
        				<td><input type="text" name="RegSeries" required></td>       	 
    				</tr>
				</table>
				<table id="assetTable">
    				<thead>
    					<tr>
        					<th>Date</th>
        					<th>Maintenance Type</th>
        					<th>Maintenance Details</th>
        					<th>Cost</th>
    					</tr>
    				</thead>
    				<tbody>
        				<tr>
            				<td><input type="date" name="MaintenanceDate" required></td>
            				<td><select name="MaintenanceType" required>
                    			<option value="Repair">Repair</option>
                    			<option value="Prevention">Prevention</option>
                				</select>
            				</td>
            				<td><input type="text" name="Details" required pattern="[A-Za-z0-9-]+" title="Only letters, numbers, and hyphens are allowed."></td>
            				<td><input type="double" name="Cost" required> </td>
        				</tr>
    				</tbody>
				</table>
				<br><br>
				<button type="submit" id="submitBtn" name="submit_maintenance">Submit</button>
				</form>
			</div>
    	</div>
	</div>
</div>		
</body>
</html> 
