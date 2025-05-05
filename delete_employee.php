<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

checkAuth();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Employee ID is missing';
    header('Location: employees.php');
    exit();
}

$employee_id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = :id");
    $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        $_SESSION['error'] = 'Employee not found';
        header('Location: employees.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: employees.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Employee deleted successfully';
            header('Location: employees.php');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to delete employee';
            header('Location: employees.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: employees.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Employee - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1><?php echo APP_NAME; ?></h1>
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
                <h2>Delete Employee</h2>
                <a href="employees.php" class="btn" style="margin-bottom: 1rem;">Back to Employees</a>
                
                <div class="confirmation-box">
                    <h3>Are you sure you want to delete this employee?</h3>
                    
                    <div class="employee-details">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                        <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
                        <p><strong>Department:</strong> 
                            <?php 
                            if ($employee['department_id']) {
                                echo htmlspecialchars(getDepartmentName($conn, $employee['department_id']));
                            } else {
                                echo 'Not assigned';
                            }
                            ?>
                        </p>
                    </div>
                    
                    <form action="delete_employee.php?id=<?php echo $employee_id; ?>" method="post">
                        <div class="form-actions">
                            <button type="submit" name="confirm_delete" class="btn btn-danger">Confirm Delete</button>
                            <a href="employees.php" class="btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
    </footer>
</body>
</html>