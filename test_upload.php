<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if file was uploaded
    if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['test_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (!in_array(strtolower($filetype), $allowed)) {
            $message = 'Error: Only JPG, JPEG, PNG, GIF and WEBP files are allowed';
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = 'uploads/test/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate a unique filename
            $new_filename = 'test_' . uniqid() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['test_image']['tmp_name'], $upload_path)) {
                $message = 'File uploaded successfully to: ' . $upload_path;
                $success = true;
                $uploaded_file = $upload_path;
            } else {
                $message = 'Error: Failed to upload file';
            }
        }
    } else {
        $message = 'Error: Please select a file to upload';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Image Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .card {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Image Upload</h1>
        <p>This page tests if image uploads are working correctly on your server.</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($uploaded_file) && $success): ?>
            <div class="card">
                <div class="card-header">Uploaded Image</div>
                <div class="card-body">
                    <img src="<?php echo $uploaded_file; ?>" alt="Uploaded image" class="img-fluid" style="max-height: 300px;">
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">Upload Form</div>
            <div class="card-body">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="test_image" class="form-label">Select an image to upload</label>
                        <input type="file" class="form-control" id="test_image" name="test_image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">Upload Directory Information</div>
            <div class="card-body">
                <?php
                // Display upload directory details
                $upload_base = 'uploads/';
                echo '<p>Upload base directory: ' . realpath($upload_base) . '</p>';
                
                // Check if directory exists and is writable
                if (file_exists($upload_base)) {
                    echo '<p>Upload directory exists: <span class="text-success">Yes</span></p>';
                    
                    if (is_writable($upload_base)) {
                        echo '<p>Upload directory is writable: <span class="text-success">Yes</span></p>';
                    } else {
                        echo '<p>Upload directory is writable: <span class="text-danger">No</span> (Please fix permissions)</p>';
                    }
                } else {
                    echo '<p>Upload directory exists: <span class="text-danger">No</span> (Creating it now...)</p>';
                    
                    // Try to create it
                    if (mkdir($upload_base, 0777, true)) {
                        echo '<p>Created upload directory: <span class="text-success">Yes</span></p>';
                    } else {
                        echo '<p>Created upload directory: <span class="text-danger">No</span> (Permission issue)</p>';
                    }
                }
                
                // Display existing upload subdirectories
                $dirs = glob($upload_base . '*', GLOB_ONLYDIR);
                if (!empty($dirs)) {
                    echo '<p>Existing upload subdirectories:</p>';
                    echo '<ul>';
                    foreach ($dirs as $dir) {
                        echo '<li>' . basename($dir) . ' (' . (is_writable($dir) ? 'writable' : 'not writable') . ')</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No subdirectories found in uploads directory.</p>';
                }
                ?>
            </div>
        </div>
        
        <a href="admin/dashboard.php" class="btn btn-secondary mt-4">Go to Admin Dashboard</a>
    </div>
</body>
</html> 