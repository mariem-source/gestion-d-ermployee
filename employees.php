<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch all employees with their department info
$stmt = $conn->query("
    SELECT e.*, d.name as department_name 
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.id
    ORDER BY e.created_at DESC
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
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
                <h2>Manage Employees</h2>
                <a href="add_employee.php" class="btn">Add New Employee</a>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Age</th>
                            <th>Experience</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                            <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                            <td><?php echo htmlspecialchars($employee['age']); ?></td>
                            <td><?php echo htmlspecialchars($employee['experience']); ?></td>
                            <td><?php echo htmlspecialchars($employee['department_name'] ?? 'Not Assigned'); ?></td>
                            <td><?php echo htmlspecialchars($employee['position']); ?></td>
                            <td class="actions">
                                <a href="edit_employee.php?id=<?php echo $employee['id']; ?>">Edit</a>
                                <a href="delete_employee.php?id=<?php echo $employee['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
    </footer>
</body>
</html>