<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get total number of employees
$stmt = $conn->query("SELECT COUNT(*) as total_employees FROM employees");
$total_employees = $stmt->fetch(PDO::FETCH_ASSOC)['total_employees'];

// Get total number of departments
$stmt = $conn->query("SELECT COUNT(*) as total_departments FROM departments");
$total_departments = $stmt->fetch(PDO::FETCH_ASSOC)['total_departments'];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <header>
        <h1>Employee Management System</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="dashboard">
            <div class="sidebar">
                <h3>Menu</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="employees.php">Manage Employees</a></li>
                    <li><a href="departments.php">Manage Departments</a></li>
                </ul>
            </div>

            <div class="content">
                <h2>Dashboard</h2>
                <div class="stats">
                    <div class="stat-card">
                        <h3>Total Employees</h3>
                        <p><?php echo $total_employees; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Departments</h3>
                        <p><?php echo $total_departments; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

   
</body>
</html>