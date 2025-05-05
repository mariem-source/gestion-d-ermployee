<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

checkAuth();

$department = null;
$errors = [];

if (!isset($_GET['id'])) {
    header('Location: departments.php');
    exit();
}

$department_id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id = :id");
    $stmt->bindParam(':id', $department_id, PDO::PARAM_INT);
    $stmt->execute();
    $department = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$department) {
        $_SESSION['error'] = 'Department not found';
        header('Location: departments.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: departments.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);

    if (empty($name)) {
        $errors[] = 'Department name is required';
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE departments SET 
                                   name = :name, 
                                   description = :description
                                   WHERE id = :id");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $department_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Department updated successfully';
                header('Location: departments.php');
                exit();
            } else {
                $errors[] = 'Failed to update department';
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $errors[] = 'Department name already exists';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <h2>Edit Department: <?php echo htmlspecialchars($department['name']); ?></h2>
                <a href="departments.php" class="btn" style="margin-bottom: 1rem;">Back to Departments</a>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form id="department-form" action="edit_department.php?id=<?php echo $department_id; ?>" method="post" class="form-container">
                    <div class="form-group">
                        <label for="name">Department Name *</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($department['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php 
                            echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($department['description']); 
                        ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn">Update Department</button>
                        <a href="departments.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
                
                <div class="department-stats" style="margin-top: 2rem;">
                    <h3>Department Statistics</h3>
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE department_id = :id");
                    $stmt->bindParam(':id', $department_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $employee_count = $stmt->fetchColumn();
                    ?>
                    <p><strong>Number of Employees:</strong> <?php echo $employee_count; ?></p>
                    
                    <?php if ($employee_count > 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Note: You cannot delete this department while it has assigned employees.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>