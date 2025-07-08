<?php
// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Style the output
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Upload Directories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
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
            margin-top: 10px;
        }
        .button:hover {
            background-color: #2a4eb7;
        }
        .button-group {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Create Upload Directories</h1>';

// Define upload directories
$directories = [
    'uploads',
    'uploads/brand_logos',
    'uploads/car_images',
    'uploads/user_avatars'
];

// Create each directory if it doesn't exist
echo '<h2>Directory Status:</h2>';
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

// Navigation links
echo '<div class="button-group">';
echo '<a href="fix_brands_table.php" class="button">Fix Brands Table</a>';
echo '<a href="admin/brands.php" class="button">Go to Brands Management</a>';
echo '</div>';

echo '</body></html>';
?> 