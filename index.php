<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection Details (Zati's Database)
$host = "10.144.6.19";
$user = "aisar";
$pass = "abc123";
$db = "ws2";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully Zati!<br>"; // Removed success message as it might be confusing


// Handle login logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signIn'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    

    $query = "SELECT * FROM STAFF WHERE Email = ? AND StaffID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password); // Bind email and staffID
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        session_start();
        $_SESSION['staff'] = $result->fetch_assoc();
        header("Location: dashboard.php");
        exit(); // Ensure no further code executes after redirect
    } else {
        echo "<script>alert('Invalid email or Staff ID!');</script>";
    }
    $stmt->close();
}

// Handle register logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signUp'])) {
    $staffID = isset($_POST['staffid']) ? trim($_POST['staffid']) : ''; // Get staff ID
    $name = isset($_POST['Name']) ? trim($_POST['Name']) : ''; // Get name
    $email = isset($_POST['email']) ? trim($_POST['email']) : ''; // Get email
    $phoneNum = isset($_POST['phoneno']) ? trim($_POST['phoneno']) : ''; // Get phone number
    $position = isset($_POST['position']) ? trim($_POST['position']) : ''; // Get position
    $departmentName = isset($_POST['departmentname']) ? trim($_POST['departmentname']) : ''; // Get department name

    $query = "SELECT * FROM STAFF WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!');</script>";
    } else {
        $insertQuery = "INSERT INTO STAFF (StaffID, Name, Email, Phone_Num, Position, Department_Name) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssss", $staffID, $name, $email, $phoneNum, $position, $departmentName);
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!');</script>";
        } else {
            echo "<script>alert('Registration failed!');</script>";
        }
    }
    $stmt->close();
}

if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials') {
    echo "<script>alert('Invalid email or Staff ID!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Asset Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }
    body {
        background: linear-gradient(to right, #e2e2e2, #c9d6ff);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .container {
        background: #fff;
        width: 350px;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .form-title {
        font-size: 1.8rem;
        font-weight: bold;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .input-group i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #757575;
    }
    input, select {
        width: 100%;
        padding: 10px 10px 10px 40px;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 5px;
        appearance: none; /* Removes default styles for dropdowns */
        background: #fff;
        cursor: pointer;
    }
    input:focus, select:focus {
        border: 1px solid hsl(327, 90%, 28%);
        outline: none;
    }
    .btn {
        width: 100%;
        padding: 10px;
        font-size: 1.2rem;
        color: #fff;
        background: #6c63ff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .btn:hover {
        background: #5147cc;
    }
    .toggle-link {
        text-align: center;
        margin-top: 1rem;
        font-size: 1rem;
    }
    .toggle-link a {
        text-decoration: none;
        color: #6c63ff;
        font-weight: bold;
    }
    .toggle-link a:hover {
        text-decoration: underline;
    }
    select {
        padding-right: 30px; /* Adds spacing to the dropdown */
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23757575' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px 8px;
    }
    .hidden{
        display: none;
    }
</style>
</head>
<body>

    <div class="container">
        <!-- Login Form -->
        <div id="loginForm">
            <div class="form-title">Sign In</div>
            <form action="" method="POST">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i id="togglePasswordIcon" class="fas fa-lock" onclick="togglePasswordVisibility()" style="cursor: pointer;"></i>
                    <input type="password" name="password" placeholder="Staff ID" required>
                </div>
                <button class="btn" type="submit" name="signIn">Sign In</button>
            </form>
            <div class="toggle-link">
                Don’t have an account? <a href="javascript:void(0)" onclick="toggleForms()">Sign Up</a>
            </div>
        </div>

        <!-- Register Form -->
        <div id="registerForm" class="hidden">
            <div class="form-title">Register</div>
            <form action="" method="POST">
                <div class="input-group">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="staffid" placeholder="Staff ID" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="Name" placeholder="Name" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="phoneno" placeholder="Phone No" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-user-plus"></i>
                    <select name="position" placeholder="Position" required>
                        <option value="" disabled selected>Position</option>
                        <option value="Head of Department">Head of Department</option>
                        <option value="PTj Asset Officer">PTj Asset Officer</option>
                        <option value="Auditor">Auditor</option>
                        <option value="Inspector">Inspector</option>
                        <option value="User">User</option>
                    </select>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-building"></i>
                    <select name="departmentname" placeholder="Department" required>
                        <option value="" disabled selected>Department</option>
                        <option value="FTMK">FTMK</option>
                        <option value="FTKIP">FTKIP</option>
                        <option value="FTKE">FTKE</option>
                        <option value="FTKM">FTKM</option>
                        <option value="FPTT">FPTT</option>
                        <option value="FTKEK">FTKEK</option>
                    </select>
                </div>
                <button class="btn" name="signUp">Sign Up</button>
            </form>
            <div class="toggle-link">
                Already have an account? <a href="javascript:void(0)" onclick="toggleForms()">Sign In</a>
            </div>
        </div>

        <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            loginForm.classList.toggle('hidden');
            registerForm.classList.toggle('hidden');
        }

        function togglePasswordVisibility() {
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-lock');
                toggleIcon.classList.add('fa-unlock');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-unlock');
                toggleIcon.classList.add('fa-lock');
            }
        }
    </script>
    </div>

    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>