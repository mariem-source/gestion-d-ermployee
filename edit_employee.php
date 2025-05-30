<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

checkAuth();

$employee = null;
$errors = [];

if (!isset($_GET['id'])) {
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

$stmt = $conn->query("SELECT id, name FROM departments ORDER BY name");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $age = intval($_POST['age']);
    $experience = intval($_POST['experience']);
    $department_id = intval($_POST['department_id']);
    $position = sanitize($_POST['position']);
    $signature = sanitize($_POST['signature']);

    if (empty($name)) {
        $errors[] = 'Employee name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }

    if (empty($position)) {
        $errors[] = 'Position is required';
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE employees SET 
                                   name = :name, 
                                   email = :email, 
                                   phone = :phone, 
                                   age = :age, 
                                   experience = :experience, 
                                   department_id = :department_id, 
                                   position = :position, 
                                   signature = :signature
                                   WHERE id = :id");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':experience', $experience);
            $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':signature', $signature);
            $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Employee updated successfully';
                header('Location: employees.php');
                exit();
            } else {
                $errors[] = 'Failed to update employee';
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $errors[] = 'Email already exists in the system';
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
    <title>Edit Employee - <?php echo APP_NAME; ?></title>
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
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <h2>Edit Employee: <?php echo htmlspecialchars($employee['name']); ?></h2>
                <a href="employees.php" class="btn" style="margin-bottom: 1rem;">Back to Employees</a>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form id="employee-form" action="edit_employee.php?id=<?php echo $employee_id; ?>" method="post" class="form-container">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($employee['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($employee['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" required 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($employee['phone']); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 1; margin-right: 1rem;">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="18" max="70"
                                   value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : htmlspecialchars($employee['age']); ?>">
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="experience">Experience (years)</label>
                            <input type="number" id="experience" name="experience" min="0" max="50"
                                   value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : htmlspecialchars($employee['experience']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['id']; ?>"
                                    <?php 
                                    $selected = false;
                                    if (isset($_POST['department_id'])) {
                                        $selected = ($_POST['department_id'] == $department['id']);
                                    } else {
                                        $selected = ($employee['department_id'] == $department['id']);
                                    }
                                    echo $selected ? 'selected' : ''; 
                                    ?>>
                                    <?php echo htmlspecialchars($department['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position *</label>
                        <input type="text" id="position" name="position" required 
                               value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : htmlspecialchars($employee['position']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="signature">Signature/Academic Title</label>
                        <textarea id="signature" name="signature" rows="2"><?php 
                            echo isset($_POST['signature']) ? htmlspecialchars($_POST['signature']) : htmlspecialchars($employee['signature']); 
                        ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Update Employee</button>
                </form>
            </div>
        </div>
    </main>

  

    <script src="../assets/js/script.js"></script>
</body>
</html>