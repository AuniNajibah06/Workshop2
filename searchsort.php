<?php
$servername = "localhost";
$username = "root";
$password = ""; // Update according to your setup
$dbname = "";
$port = 3307; // Default MariaDB port for XAMPP

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all tracking data
$sql = "SELECT * FROM tracking";
$result = $conn->query($sql);

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $trackingID = $conn->real_escape_string($_POST['tracking_id']);
    $deleteSql = "DELETE FROM tracking WHERE TrackingID = '$trackingID'";
    if ($conn->query($deleteSql) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    exit;
}

// Handle insert
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insert'])) {
    $trackingID = $_POST['tracking_id']; // Use provided TrackingID for new entry
    $status = $_POST['status'];
    $updatedTime = $_POST['updated_time'];
    $location = $_POST['location'];

    $insertSql = "INSERT INTO tracking (TrackingID, Status, UpdatedTime, Location)
                  VALUES ('$trackingID', '$status', '$updatedTime', '$location')";

    if ($conn->query($insertSql) === TRUE) {
        echo "New tracking record created successfully.";
    } else {
        echo "Error: " . $insertSql . "<br>" . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    exit;
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $trackingID = $_POST['tracking_id'];
    $status = $_POST['status'];
    $updatedTime = $_POST['updated_time'];
    $location = $_POST['location'];

    $updateSql = "UPDATE tracking SET 
                  Status='$status',
                  UpdatedTime='$updatedTime',
                  Location='$location'
                  WHERE TrackingID='$trackingID'";

    if ($conn->query($updateSql) === TRUE) {
        echo "Tracking record updated successfully.";
    } else {
        echo "Error: " . $updateSql . "<br>" . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
    exit;
} 

// Search and Sort
$searchQuery = "";
$sortColumn = "TrackingID";
$order = "ASC";

if (isset($_GET['search'])) {
    $searchQuery = $conn->real_escape_string($_GET['search']);
}

if (isset($_GET['sort_by'])) {
    $sortColumn = $conn->real_escape_string($_GET['sort_by']);
}

if (isset($_GET['order']) && $_GET['order'] === "DESC") {
    $order = "DESC";
}

$sql = "SELECT * FROM tracking 
        WHERE TrackingID LIKE '%$searchQuery%'
        ORDER BY $sortColumn $order";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tracking Management</title>
    <style>
        body {
            background-color: #e0f7fa; /* Soft blue background */
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        h1 {
            color: #005f73; /* Darker blue for headings */
            margin-bottom: 20px;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #ffffff; /* White background */
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #005f73; /* Header background */
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* Alternating row colors */
        }
        input[type="submit"] {
            background-color: #005f73; /* Button color */
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #004d61; /* Darker shade on hover */
        }
        form {
            display: inline;
        }
        .insert-button {
            background-color: #00796b;
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .insert-button:hover {
            background-color: #004d40;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Tracking Management</h1>

<!-- Search and Sort Controls -->
<form method="GET">
    <input type="text" name="search" placeholder="Search Tracking ID" value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit">Search</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="text-decoration: none; color: #005f73; margin-left: 10px;">Clear</a>
</form>

<form method="GET">
    <label for="sort_by">Sort by:</label>
    <select id="sort_by" name="sort_by">
        <option value="TrackingID" <?php echo $sortColumn === "TrackingID" ? "selected" : ""; ?>>Tracking ID</option>
        <option value="Status" <?php echo $sortColumn === "Status" ? "selected" : ""; ?>>Status</option>
        <option value="UpdatedTime" <?php echo $sortColumn === "UpdatedTime" ? "selected" : ""; ?>>Updated Time</option>
        <option value="Location" <?php echo $sortColumn === "Location" ? "selected" : ""; ?>>Location</option>
    </select>
    <label>
        <input type="radio" name="order" value="ASC" <?php echo $order === "ASC" ? "checked" : ""; ?>> Ascending
    </label>
    <label>
        <input type="radio" name="order" value="DESC" <?php echo $order === "DESC" ? "checked" : ""; ?>> Descending
    </label>
    <button type="submit">Sort</button>
</form>

<!-- Button to open the insert modal -->
<div style="text-align: center; margin: 10px 0;">
    <button class="insert-button" onclick="openInsertModal()">Insert New Tracking</button>
</div>

<table>
    <tr>
        <th>Tracking ID</th>
        <th>Status</th>
        <th>Updated Time</th>
        <th>Location</th>
        <th>Actions</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['TrackingID']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                <td><?php echo htmlspecialchars($row['UpdatedTime']); ?></td>
                <td><?php echo htmlspecialchars($row['Location']); ?></td>
                <td>
                    <button onclick="openUpdateModal('<?php echo $row['TrackingID']; ?>', '<?php echo $row['Status']; ?>', '<?php echo $row['UpdatedTime']; ?>', '<?php echo $row['Location']; ?>')">Update</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="tracking_id" value="<?php echo $row['TrackingID']; ?>">
                        <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this record?');">
                    </form>
                </td>

                
                
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">No records found</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Insert Modal -->
<div id="insertModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeInsertModal()">&times;</span>
        <h2>Insert New Tracking</h2>
        <form method="POST">
            <label for="tracking_id">Tracking ID:</label>
            <input type="text" id="tracking_id" name="tracking_id" required><br><br>

            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Order Placed">Order Placed</option>
                <option value="Picked Up">Picked Up</option>
                <option value="In Transit">In Transit</option>
                <option value="Arrived at Distribution Center">Arrived at Distribution Center</option>
                <option value="Out for Delivery">Out for Delivery</option>
                <option value="Delivered">Delivered</option>
                <option value="Delivery Attempted">Delivery Attempted</option>
                <option value="Held at Pickup Location">Held at Pickup Location</option>
                <option value="Returned to Sender">Returned to Sender</option>
                <option value="Customs Hold">Customs Hold</option>
                <option value="Failed">Failed</option>
            </select><br><br>

            <label for="updated_time">Updated Time:</label>
            <input type="datetime-local" id="updated_time" name="updated_time" required><br><br>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required><br><br>

            <input type="submit" name="insert" value="Insert">
        </form>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeUpdateModal()">&times;</span>
        <h2>Update Tracking</h2>
        <form method="POST">
            <input type="hidden" id="update_tracking_id" name="tracking_id">
            <label for="update_status">Status:</label>
            <select id="update_status" name="status">
            <option value="Order Placed">Order Placed</option>
            <option value="Picked Up">Picked Up</option>
            <option value="In Transit">In Transit</option>
            <option value="Arrived at Distribution Center">Arrived at Distribution Center</option>
            <option value="Out for Delivery">Out for Delivery</option>
            <option value="Delivered">Delivered</option>
            <option value="Delivery Attempted">Delivery Attempted</option>
            <option value="Held at Pickup Location">Held at Pickup Location</option>
            <option value="Returned to Sender">Returned to Sender</option>
            <option value="Customs Hold">Customs Hold</option>
            <option value="Failed">Failed</option>
            </select><br><br>

            <label for="update_updated_time">Updated Time:</label>
            <input type="datetime-local" id="update_updated_time" name="updated_time" required><br><br>

            <label for="update_location">Location:</label>
            <input type="text" id="update_location" name="location" required><br><br>

            <input type="submit" name="update" value="Update">
        </form>
    </div>
</div>

<script>
    // Modal Scripts
    function openInsertModal() {
        document.getElementById('insertModal').style.display = 'block';
    }

    function closeInsertModal() {
        document.getElementById('insertModal').style.display = 'none';
    }

    function openUpdateModal(trackingID, status, updatedTime, location) {
        document.getElementById('update_tracking_id').value = trackingID;
        document.getElementById('update_status').value = status;
        document.getElementById('update_updated_time').value = updatedTime;
        document.getElementById('update_location').value = location;
        document.getElementById('updateModal').style.display = 'block';
    }

    function closeUpdateModal() {
        document.getElementById('updateModal').style.display = 'none';
    }
</script>

</body>
</html>

<?php
$conn->close();
?>
