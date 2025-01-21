
<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_connection.php');

// Database Connection Details (Zati's Database)
$host = "10.144.6.19";
$user = "aisar";
$pass = "abc123";
$db = "ws2";

// Create connection
$conn_zati = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn_zati->connect_error) {
    die("Connection failed: " . $conn_zati->connect_error);
}

$staffName = $_SESSION['staff']['Name'];
$staffID = $_SESSION['staff']['StaffID'];
$staffPosition = $_SESSION['staff']['Position'];

$currentPage = 'admin';

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

$query = "SELECT * FROM STAFF WHERE StaffID = ?";
$stmt = $conn_zati->prepare($query);
$stmt->bind_param("s", $staffID); // Bind the staffID to the prepared statement
$stmt->execute();
$result = $stmt->get_result();
$staffData = $result->fetch_assoc();

// Handle Edit Button Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editDetails'])) {
    header("Location: enable_edit.php");
    exit();
}

// Fetch the count of pending disposals
$sql_pending_count = "SELECT COUNT(*) AS pending_count FROM disposal WHERE ApprovalStatus IS NULL OR ApprovalStatus = ''";
$result_pending_count = $conn->query($sql_pending_count);
$pendingCount = ($result_pending_count && $result_pending_count->num_rows > 0) ? $result_pending_count->fetch_assoc()['pending_count'] : 0;


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
	<style>
        form {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-direction: column; /* Arrange buttons in a column */
            align-items: center; /* Align buttons to the center */
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
<div class="side-menu">
    <div class="brand-name">
        <h1>PSTP - Asset Management System</h1>
    </div>
    <div class="profile">
        <p><?php echo 'Hello, '; ?></p>
        <h3><?php echo htmlspecialchars($staffName); ?><?php echo ' !'; ?></h3>
    </div>
    <ul>
        <li><a href="dashboard.php"><i class="fa-solid fa-table-columns"></i>   <span>Dashboard</span></a></li>
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
                <a href="assets_assign.php">
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
        <li class="<?= isActive('admin'); ?>">
			<a href="backuprecovery.php"><i class="fa-solid fa-circle-exclamation"></i>   
			<span>Admin</span>
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
        <form method="post">
            <button type="submit" name="backup">Backup Database</button>
            <button type="submit" name="recover">Recover Database</button>
        </form>
</div>

</body>
</html>
