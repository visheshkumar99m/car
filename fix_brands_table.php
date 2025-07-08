<?php
// Include database connection
require_once('includes/db_connection.php');

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Add styling
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Brands Table</title>
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
        button, .button {
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
        button:hover, .button:hover {
            background-color: #2a4eb7;
        }
        .button-group {
            margin-top: 20px;
        }
        .secondary {
            background-color: #6c757d;
        }
        .secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>Fix Brands Table Structure</h1>';

// Check if column already exists
$check_column = mysqli_query($con, "SHOW COLUMNS FROM brands LIKE 'brand_logo'");

if (mysqli_num_rows($check_column) > 0) {
    echo '<div class="message info">The <strong>brand_logo</strong> column already exists in the brands table.</div>';
} else {
    // Add the column if requested
    if (isset($_GET['add_column']) && $_GET['add_column'] == 'true') {
        $add_column_query = "ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description";
        
        if (mysqli_query($con, $add_column_query)) {
            echo '<div class="message success">Successfully added <strong>brand_logo</strong> column to the brands table!</div>';
        } else {
            echo '<div class="message error">Error adding column: ' . mysqli_error($con) . '</div>';
        }
    } else {
        echo '<div class="message error">The <strong>brand_logo</strong> column does not exist in the brands table.</div>';
        echo '<p>This is causing the error when trying to upload brand logos in the admin panel:</p>';
        echo '<div class="message error">Fatal error: Uncaught mysqli_sql_exception: Unknown column \'brand_logo\' in \'field list\'</div>';
        echo '<p>Click the button below to add the missing column:</p>';
        echo '<a href="?add_column=true" class="button">Add brand_logo Column</a>';
    }
}

// Show current table structure
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

// Navigation links
echo '<div class="button-group">';
echo '<a href="admin/brands.php" class="button">Go to Brands Management</a>';
echo '<a href="admin/dashboard.php" class="button secondary">Admin Dashboard</a>';
echo '</div>';

echo '</body></html>';
?> 