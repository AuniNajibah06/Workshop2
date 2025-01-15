<?php
session_start();
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include('db_connection.php');

// Get staff information from the session
$staffID = $_SESSION['staff']['StaffID'];

// Fetch all maintenance records
$query_all = "SELECT MaintenanceID, StaffID, AssetID, MaintenanceDate, MaintenanceType, Details, Cost, Status FROM Maintenance";
$result_all = $conn->query($query_all);

// Fetch pending maintenance records
$query_pending = "SELECT MaintenanceID, StaffID, AssetID, MaintenanceDate, MaintenanceType, Details, Cost FROM Maintenance WHERE Status = 'Pending'";
$result_pending = $conn->query($query_pending);

// Handle form submission for status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        foreach ($_POST['MaintenanceID'] as $index => $MaintenanceID) {
            $newStatus = $_POST['Status'][$index];
            $stmt = $conn->prepare("UPDATE Maintenance SET Status = ?, StaffID = ? WHERE MaintenanceID = ?");
            $stmt->bind_param("ssi", $newStatus, $staffID, $MaintenanceID);
            $stmt->execute();
            $stmt->close();
        }
        // Redirect to avoid resubmission on page refresh
        header("Location: maintenance_list.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Maintenance List</h1>

    <!-- First Table: All Maintenance Records -->
    <table>
        <thead>
            <tr>
                <th>Maintenance ID</th>
                <th>Staff ID</th>
                <th>Asset ID</th>
                <th>Maintenance Date</th>
                <th>Maintenance Type</th>
                <th>Details</th>
                <th>Cost</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_all->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['MaintenanceID']) ?></td>
                    <td><?= htmlspecialchars($row['StaffID']) ?></td>
                    <td><?= htmlspecialchars($row['AssetID']) ?></td>
                    <td><?= htmlspecialchars($row['MaintenanceDate']) ?></td>
                    <td><?= htmlspecialchars($row['MaintenanceType']) ?></td>
                    <td><?= htmlspecialchars($row['Details']) ?></td>
                    <td><?= htmlspecialchars($row['Cost']) ?></td>
                    <td><?= htmlspecialchars($row['Status']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Second Table: Pending Maintenance Records -->
    <h2>Pending Maintenance</h2>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Maintenance ID</th>
                    <th>Staff ID</th>
                    <th>Asset ID</th>
                    <th>Maintenance Date</th>
                    <th>Maintenance Type</th>
                    <th>Details</th>
                    <th>Cost</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_pending->fetch_assoc()) { ?>
                    <tr>
                        <td><input type="hidden" name="MaintenanceID[]" value="<?= htmlspecialchars($row['MaintenanceID']) ?>">
                            <?= htmlspecialchars($row['MaintenanceID']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['StaffID']) ?></td>
                        <td><?= htmlspecialchars($row['AssetID']) ?></td>
                        <td><?= htmlspecialchars($row['MaintenanceDate']) ?></td>
                        <td><?= htmlspecialchars($row['MaintenanceType']) ?></td>
                        <td><?= htmlspecialchars($row['Details']) ?></td>
                        <td><?= htmlspecialchars($row['Cost']) ?></td>
                        <td>
                            <select name="Status[]" required>
                                <option value="">Select</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button type="submit" name="update_status" class="btn-submit">Submit</button>
    </form>
</body>
</html>
