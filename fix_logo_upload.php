<?php
// Include database connection
require_once('includes/db_connection.php');

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Style the output
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Logo Upload Issues</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #3563E9;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            background-color: #3563E9;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #2a4eb7;
        }
        .success-button {
            background-color: #28a745;
        }
        .success-button:hover {
            background-color: #218838;
        }
        .button-group {
            margin-top: 20px;
        }
        .code {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Fix Logo Upload Issues</h1>
    <p>This tool will fix the issues with brand logo uploads in the admin dashboard.</p>';

// Check if we should run the fix
$fix_applied = false;
if (isset($_GET['apply_fix']) && $_GET['apply_fix'] == 'true') {
    $fix_applied = true;
    
    echo '<h2>Applying Fixes...</h2>';
    
    // Step 1: Check and add brand_logo column
    $check_column = mysqli_query($con, "SHOW COLUMNS FROM brands LIKE 'brand_logo'");
    
    if (mysqli_num_rows($check_column) > 0) {
        echo '<div class="message info">Step 1: The <strong>brand_logo</strong> column already exists in the brands table.</div>';
    } else {
        // Add the column
        $add_column_query = "ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description";
        
        if (mysqli_query($con, $add_column_query)) {
            echo '<div class="message success">Step 1: Successfully added <strong>brand_logo</strong> column to the brands table!</div>';
        } else {
            echo '<div class="message error">Step 1: Error adding column: ' . mysqli_error($con) . '</div>';
        }
    }
    
    // Step 2: Create upload directories
    echo '<h3>Creating Upload Directories</h3>';
    
    $directories = [
        'uploads',
        'uploads/brand_logos',
        'uploads/car_images',
        'uploads/user_avatars'
    ];
    
    echo '<table>';
    echo '<tr><th>Directory Path</th><th>Status</th><th>Permissions</th></tr>';
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            // Try to create the directory
            $success = mkdir($dir, 0777, true);
            if ($success) {
                chmod($dir, 0777); // Ensure the directory is writable
                echo '<tr><td>' . $dir . '</td><td><span class="message success">Created</span></td>';
            } else {
                echo '<tr><td>' . $dir . '</td><td><span class="message error">Failed to create</span></td>';
            }
        } else {
            echo '<tr><td>' . $dir . '</td><td><span class="message info">Already exists</span></td>';
        }
        
        // Check permissions
        if (file_exists($dir)) {
            $perms = substr(sprintf('%o', fileperms($dir)), -4);
            if (is_writable($dir)) {
                echo '<td>' . $perms . ' (Writable)</td></tr>';
            } else {
                echo '<td>' . $perms . ' <span class="message error">(Not writable)</span></td></tr>';
            }
        } else {
            echo '<td>N/A</td></tr>';
        }
    }
    
    echo '</table>';
    
    echo '<div class="message success">Fix completed! You should now be able to upload brand logos in the admin dashboard.</div>';
} else {
    // Display the error and offer to fix it
    echo '<div class="message error">
        <strong>Error:</strong> Fatal error: Uncaught mysqli_sql_exception: Unknown column \'brand_logo\' in \'field list\' in C:\\xampp\\htdocs\\cars\\home_page\\admin\\add_brand.php:66
    </div>';
    
    echo '<h2>Problem Explanation</h2>';
    echo '<p>This error occurs because the <strong>brands</strong> table in your database is missing the <strong>brand_logo</strong> column.</p>';
    
    echo '<h3>Detailed Explanation:</h3>';
    echo '<ol>
        <li>When you try to add a brand with a logo in the admin dashboard, the system tries to insert data into a column called <strong>brand_logo</strong>.</li>
        <li>However, this column doesn\'t exist in your database, which causes the error.</li>
        <li>This tool will add the missing column and create the necessary upload directories.</li>
    </ol>';
    
    // Check current table structure
    echo '<h2>Current Brands Table Structure:</h2>';
    $result = mysqli_query($con, "DESCRIBE brands");
    
    if (!$result) {
        echo '<div class="message error">Error: ' . mysqli_error($con) . '</div>';
    } else {
        echo '<table>';
        echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['Field'] . '</td>';
            echo '<td>' . $row['Type'] . '</td>';
            echo '<td>' . $row['Null'] . '</td>';
            echo '<td>' . $row['Key'] . '</td>';
            echo '<td>' . ($row['Default'] ?? 'NULL') . '</td>';
            echo '<td>' . $row['Extra'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    // Solution
    echo '<h2>Solution</h2>';
    echo '<p>Click the button below to fix the issue:</p>';
    echo '<a href="?apply_fix=true" class="button success-button">Fix Logo Upload Issues</a>';
    
    // For advanced users
    echo '<h3>Manual Fix (Alternative):</h3>';
    echo '<p>If you prefer to fix this manually, you can run the following SQL command in phpMyAdmin:</p>';
    echo '<div class="code">ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description;</div>';
}

// Navigation footer
echo '<div class="button-group">';
if ($fix_applied) {
    echo '<a href="admin/brands.php" class="button success-button">Go to Brands Management</a>';
}
echo '<a href="admin/dashboard.php" class="button">Admin Dashboard</a>';
echo '<a href="front_page/front_page.php" class="button">Back to Home</a>';
echo '</div>';

echo '</body></html>';
?> 