
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle form submission for adding new department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_department'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (:name, :description)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Department added successfully';
            header('Location: departments.php');
            exit();
        } else {
            $error = 'Failed to add department';
        }
    } else {
        $error = 'Department name is required';
    }
}

// Handle department deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if department has employees
    $stmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE department_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $conn->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Department deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete department';
        }
    } else {
        $_SESSION['error'] = 'Cannot delete department with assigned employees';
    }
    
    header('Location: departments.php');
    exit();
}

// Get all departments
$stmt = $conn->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
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
                <h2>Manage Departments</h2>
                
                <?php
                if (isset($_SESSION['message'])) {
                    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                    unset($_SESSION['message']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                
                <!-- Add Department Form -->
                <div class="form-container" style="margin-bottom: 2rem;">
                    <h3>Add New Department</h3>
                    <form action="departments.php" method="post">
                        <div class="form-group">
                            <label for="name">Department Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_department" class="btn">Add Department</button>
                    </form>
                </div>
                
                <!-- Departments List -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $department): ?>
                        <tr>
                            <td><?php echo $department['id']; ?></td>
                            <td><?php echo htmlspecialchars($department['name']); ?></td>
                            <td><?php echo htmlspecialchars($department['description']); ?></td>
                            <td class="actions">
                                <a href="edit_department.php?id=<?php echo $department['id']; ?>">Edit</a>
                                <a href="departments.php?delete=<?php echo $department['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

   
</body>
</html>