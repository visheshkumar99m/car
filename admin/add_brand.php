<?php
// Include necessary files
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $brand_name = $_POST['brand_name'] ?? '';
    $brand_description = $_POST['brand_description'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($brand_name)) {
        $errors[] = "Brand name is required";
    }
    
    // Check if brand name already exists
    $name_check = "SELECT brand_id FROM brands WHERE brand_name = ?";
    $name_stmt = mysqli_prepare($con, $name_check);
    mysqli_stmt_bind_param($name_stmt, "s", $brand_name);
    mysqli_stmt_execute($name_stmt);
    mysqli_stmt_store_result($name_stmt);
    
    if (mysqli_stmt_num_rows($name_stmt) > 0) {
        $errors[] = "Brand name already exists";
    }
    
    // Handle logo upload if provided
    $brand_logo = '';
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
            } else {
                $errors[] = "Failed to upload logo";
            }
        }
    }
    
    // If no errors, insert new brand
    if (empty($errors)) {
        $insert_query = "INSERT INTO brands (brand_name, description, brand_logo) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "sss", $brand_name, $brand_description, $brand_logo);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            set_message("Brand added successfully!", "success");
        } else {
            set_message("Error adding brand: " . mysqli_error($con), "error");
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