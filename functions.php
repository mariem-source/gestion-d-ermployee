<?php
/**
 * Display flash messages
 */
function displayMessages() {
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
}

/**
 * Redirect to a specific URL
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 * @param string $role Required role (optional)
 */
function checkAuth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
    
    if ($role && $_SESSION['role'] != $role) {
        redirect('dashboard.php');
    }
}

/**
 * Get department name by ID
 * @param PDO $conn Database connection
 * @param int $id Department ID
 * @return string Department name
 */
function getDepartmentName($conn, $id) {
    $stmt = $conn->prepare("SELECT name FROM departments WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['name'] : 'Not assigned';
}

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate pagination links
 * @param int $total_items Total number of items
 * @param int $items_per_page Items per page
 * @param int $current_page Current page number
 * @param string $url_base Base URL for links
 */
function generatePagination($total_items, $items_per_page, $current_page, $url_base) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) return '';
    
    $pagination = '<div class="pagination">';
    
    // Previous link
    if ($current_page > 1) {
        $pagination .= '<a href="' . $url_base . '&page=' . ($current_page - 1) . '">&laquo; Previous</a>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? ' class="active"' : '';
        $pagination .= '<a href="' . $url_base . '&page=' . $i . '"' . $active . '>' . $i . '</a>';
    }
    
    // Next link
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . $url_base . '&page=' . ($current_page + 1) . '">Next &raquo;</a>';
    }
    
    $pagination .= '</div>';
    
    return $pagination;
}
?>