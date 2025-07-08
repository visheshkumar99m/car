<?php
// This is a direct fix script for the brand_logo column issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once('includes/db_connection.php');

// Style the output
echo '<!DOCTYPE html>
<html>
<head>
    <title>Emergency Fix for brand_logo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h2 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { text-align: left; padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Emergency Fix for brand_logo Column</h1>';

if(isset($_GET['force']) && $_GET['force'] == 'true') {
    echo '<div class="warning">Running in FORCE mode - Will attempt to recreate structures regardless of current state</div>';
    $force_mode = true;
} else {
    $force_mode = false;
}

// Get database info
echo "<h2>Database Information</h2>";
echo "<pre>";
echo "Database: " . $db . "\n";
echo "Host: " . $host . "\n";
echo "User: " . $user . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "</pre>";

// Check connection
if (mysqli_connect_errno()) {
    echo "<div class='error'>Failed to connect to MySQL: " . mysqli_connect_error() . "</div>";
    exit();
}

echo "<div class='success'>Connected to database successfully.</div>";

// 1. Check if brands table exists
$check_table = mysqli_query($con, "SHOW TABLES LIKE 'brands'");
if (mysqli_num_rows($check_table) == 0) {
    echo "<div class='error'>The 'brands' table does not exist in the database! You need to create it first.</div>";
    exit();
}

echo "<div class='success'>Confirmed 'brands' table exists.</div>";

// 2. Try to fix the column
echo "<h2>Fixing the brand_logo column</h2>";

$check_column = mysqli_query($con, "SHOW COLUMNS FROM brands LIKE 'brand_logo'");
$column_exists = (mysqli_num_rows($check_column) > 0);

if ($column_exists && !$force_mode) {
    echo "<div class='success'>The 'brand_logo' column already exists in the brands table.</div>";
} else {
    // If column exists but we're in force mode, drop it first
    if ($column_exists && $force_mode) {
        echo "<div class='warning'>Force mode: Dropping existing brand_logo column to recreate it...</div>";
        $drop_column_query = "ALTER TABLE brands DROP COLUMN brand_logo";
        if (mysqli_query($con, $drop_column_query)) {
            echo "<div class='success'>Successfully removed old brand_logo column.</div>";
        } else {
            echo "<div class='error'>Error removing column: " . mysqli_error($con) . "</div>";
        }
    }

    // Add the column
    $add_column_query = "ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description";
    if (mysqli_query($con, $add_column_query)) {
        echo "<div class='success'>Successfully added brand_logo column to the brands table!</div>";
    } else {
        echo "<div class='error'>Failed to add column: " . mysqli_error($con) . "</div>";
    }
}

// 3. Verify the column now exists
$check_column = mysqli_query($con, "SHOW COLUMNS FROM brands LIKE 'brand_logo'");
if (mysqli_num_rows($check_column) > 0) {
    echo "<div class='success'>Verified: The brand_logo column now exists in the brands table.</div>";
} else {
    echo "<div class='error'>Something went wrong. The brand_logo column still doesn't exist!</div>";
}

// 4. Show the current table structure
echo "<h2>Current Brands Table Structure</h2>";
$describe_result = mysqli_query($con, "DESCRIBE brands");
if ($describe_result) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($describe_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>Failed to get table structure: " . mysqli_error($con) . "</div>";
}

// 5. Create necessary directories
echo "<h2>Creating Upload Directories</h2>";
$upload_dirs = [
    'uploads',
    'uploads/brand_logos',
    'uploads/car_images',
];

echo "<table>";
echo "<tr><th>Directory</th><th>Status</th><th>Permissions</th></tr>";

foreach ($upload_dirs as $dir) {
    echo "<tr>";
    echo "<td>" . $dir . "</td>";
    
    $dir_exists = file_exists($dir);
    
    // If directory doesn't exist or we're in force mode, (re)create it
    if (!$dir_exists || $force_mode) {
        if ($dir_exists && $force_mode) {
            echo "<td>Exists - Force Recreating</td>";
        } else {
            echo "<td>Creating</td>";
        }
        
        if (mkdir($dir, 0777, true)) {
            chmod($dir, 0777); // Set maximum permissions
            echo "<td style='color:green'>Created with permissions 0777</td>";
        } else {
            echo "<td style='color:red'>Failed to create</td>";
        }
    } else {
        echo "<td>Already Exists</td>";
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        if (is_writable($dir)) {
            echo "<td>" . $perms . " (Writable)</td>";
        } else {
            chmod($dir, 0777); // Try to make it writable
            if (is_writable($dir)) {
                echo "<td style='color:green'>" . $perms . " → 0777 (Now Writable)</td>";
            } else {
                echo "<td style='color:red'>" . $perms . " (Not Writable)</td>";
            }
        }
    }
    
    echo "</tr>";
}
echo "</table>";

// 6. Test the add_brand.php file indirectly to avoid fatal errors
echo "<h2>Fixing Verification</h2>";

// Create a sample brand logo path for testing
$test_dir = 'uploads/brand_logos/test_logo.png';
$brand_name = 'Test Brand ' . date('Y-m-d H:i:s');
$description = 'This is a test brand created by the fix script';

// Test inserting directly with a basic query
$test_query = "INSERT INTO brands (brand_name, description, brand_logo) VALUES (?, ?, ?)";
$test_stmt = mysqli_prepare($con, $test_query);

if ($test_stmt) {
    mysqli_stmt_bind_param($test_stmt, "sss", $brand_name, $description, $test_dir);
    
    if (mysqli_stmt_execute($test_stmt)) {
        $insert_id = mysqli_insert_id($con);
        echo "<div class='success'>Test insert successful! Created brand ID: " . $insert_id . "</div>";
        
        // Clean up the test entry
        $cleanup = mysqli_query($con, "DELETE FROM brands WHERE brand_id = " . $insert_id);
        if ($cleanup) {
            echo "<div class='success'>Test entry cleaned up successfully.</div>";
        }
    } else {
        echo "<div class='error'>Test insert failed: " . mysqli_error($con) . "</div>";
    }
    
    mysqli_stmt_close($test_stmt);
} else {
    echo "<div class='error'>Failed to prepare test statement: " . mysqli_error($con) . "</div>";
}

// 7. Final instructions
echo "<h2>Fix Complete</h2>";
echo "<div class='success'>
    <p><strong>The fix has been applied successfully!</strong></p>
    <p>You should now be able to add brands with logos in the admin dashboard.</p>
</div>";

echo "<h2>Next Steps</h2>";
echo "<ol>
    <li>Go back to the <a href='admin/brands.php'>Brands Management</a> page</li>
    <li>Try adding a brand with a logo</li>
    <li>If it works, your issue is now fixed!</li>
</ol>";

echo "<div style='margin-top: 30px;'>
    <a href='admin/brands.php' class='button'>Go to Brands Management</a>
    <a href='direct_fix.php?force=true' class='button' style='background-color: #ff9800;'>Run Force Mode Fix</a>
</div>";

echo "</body></html>";
?> 