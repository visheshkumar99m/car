<?php
// Include necessary files
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $user_id = $_POST['user_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Validation
    $errors = [];
    
    if (empty($user_id) || !is_numeric($user_id)) {
        $errors[] = "Invalid user ID";
    }
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists for other users
    $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
    $email_stmt = mysqli_prepare($con, $email_check);
    mysqli_stmt_bind_param($email_stmt, "si", $email, $user_id);
    mysqli_stmt_execute($email_stmt);
    mysqli_stmt_store_result($email_stmt);
    
    if (mysqli_stmt_num_rows($email_stmt) > 0) {
        $errors[] = "Email already exists for another user";
    }
    
    // If no errors, update user
    if (empty($errors)) {
        // Check if we're updating the admin status of the current user
        if ($_SESSION['id'] == $user_id && $_SESSION['is_admin'] && !$is_admin) {
            set_message("You cannot remove your own admin privileges.", "error");
        } else {
            // Update query depends on whether password is being changed
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET name = ?, email = ?, password = ?, is_admin = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($update_stmt, "sssii", $name, $email, $password_hash, $is_admin, $user_id);
            } else {
                $update_query = "UPDATE users SET name = ?, email = ?, is_admin = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($update_stmt, "ssii", $name, $email, $is_admin, $user_id);
            }
            
            if (mysqli_stmt_execute($update_stmt)) {
                // If updating the current user, update session data
                if ($_SESSION['id'] == $user_id) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['is_admin'] = $is_admin == 1;
                }
                
                set_message("User updated successfully!", "success");
            } else {
                set_message("Error updating user: " . mysqli_error($con), "error");
            }
        }
    } else {
        set_message("Error: " . implode(", ", $errors), "error");
    }
    
    // Redirect back to users list
    header("Location: users.php");
    exit();
} else {
    // Not a POST request, redirect to users list
    header("Location: users.php");
    exit();
}
?> 