<?php
// Include necessary files
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $brand_id = $_POST['brand_id'] ?? 0;
    $brand_name = $_POST['brand_name'] ?? '';
    $brand_description = $_POST['brand_description'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($brand_id) || !is_numeric($brand_id)) {
        $errors[] = "Invalid brand ID";
    }
    
    if (empty($brand_name)) {
        $errors[] = "Brand name is required";
    }
    
    // Check if brand name already exists for other brands
    $name_check = "SELECT brand_id FROM brands WHERE brand_name = ? AND brand_id != ?";
    $name_stmt = mysqli_prepare($con, $name_check);
    mysqli_stmt_bind_param($name_stmt, "si", $brand_name, $brand_id);
    mysqli_stmt_execute($name_stmt);
    mysqli_stmt_store_result($name_stmt);
    
    if (mysqli_stmt_num_rows($name_stmt) > 0) {
        $errors[] = "Brand name already exists for another brand";
    }
    
    // Get current brand data for logo handling
    $current_data_query = "SELECT brand_logo FROM brands WHERE brand_id = ?";
    $current_data_stmt = mysqli_prepare($con, $current_data_query);
    mysqli_stmt_bind_param($current_data_stmt, "i", $brand_id);
    mysqli_stmt_execute($current_data_stmt);
    $result = mysqli_stmt_get_result($current_data_stmt);
    $current_data = mysqli_fetch_assoc($result);
    $current_logo = $current_data['brand_logo'] ?? '';
    
    // Handle logo upload if provided
    $brand_logo = $current_logo; // Default to current logo
    if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['brand_logo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (!in_array(strtolower($filetype), $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed for logos";
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = '../uploads/brand_logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate a unique filename
            $new_filename = uniqid('brand_') . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $upload_path)) {
                $brand_logo = 'uploads/brand_logos/' . $new_filename;
                
                // Delete old logo if it exists
                if (!empty($current_logo) && file_exists('../' . $current_logo)) {
                    unlink('../' . $current_logo);
                }
            } else {
                $errors[] = "Failed to upload logo";
            }
        }
    }
    
    // If no errors, update brand
    if (empty($errors)) {
        $update_query = "UPDATE brands SET brand_name = ?, description = ?, brand_logo = ? WHERE brand_id = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($update_stmt, "sssi", $brand_name, $brand_description, $brand_logo, $brand_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            set_message("Brand updated successfully!", "success");
        } else {
            set_message("Error updating brand: " . mysqli_error($con), "error");
        }
    } else {
        set_message("Error: " . implode(", ", $errors), "error");
    }
    
    // Redirect back to brands list
    header("Location: brands.php");
    exit();
} else {
    // Not a POST request, redirect to brands list
    header("Location: brands.php");
    exit();
}
?> 