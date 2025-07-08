<?php
// Detailed database verification script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once('includes/db_connection.php');

// Style the output
echo '<!DOCTYPE html>
<html>
<head>
    <title>Database Verification</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; margin-top: 30px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .fix-button { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Database Verification Script</h1>';

// Function to display table and column structure
function display_table_structure($con, $table_name) {
    echo "<h3>Table Structure: $table_name</h3>";
    
    $result = mysqli_query($con, "DESCRIBE $table_name");
    
    if (!$result) {
        echo "<p class='error'>Error: " . mysqli_error($con) . "</p>";
        return;
    }
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
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
}

// Display database information
echo "<h2>Database Information</h2>";
echo "<p><strong>Database:</strong> $db</p>";
echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>User:</strong> $user</p>";

// Check if we can connect to the database
if (mysqli_connect_errno()) {
    echo "<p class='error'>Failed to connect to MySQL: " . mysqli_connect_error() . "</p>";
    exit();
}

echo "<p class='success'>Connected to database successfully!</p>";

// Get all tables in the database
$tables = [];
$tables_result = mysqli_query($con, "SHOW TABLES");

if (!$tables_result) {
    echo "<p class='error'>Error getting tables: " . mysqli_error($con) . "</p>";
} else {
    echo "<h2>Database Tables</h2>";
    echo "<ul>";
    while ($row = mysqli_fetch_row($tables_result)) {
        $tables[] = $row[0];
        echo "<li>$row[0]</li>";
    }
    echo "</ul>";
}

// Check if the brands table exists
if (in_array('brands', $tables)) {
    echo "<p class='success'>The 'brands' table exists in the database.</p>";
    
    // Display structure of brands table
    display_table_structure($con, 'brands');
    
    // Check if brand_logo column exists
    $check_column = mysqli_query($con, "SHOW COLUMNS FROM brands LIKE 'brand_logo'");
    
    if (mysqli_num_rows($check_column) > 0) {
        echo "<p class='success'>The 'brand_logo' column exists in the brands table.</p>";
    } else {
        echo "<p class='error'>The 'brand_logo' column does NOT exist in the brands table!</p>";
        echo "<p>This is the cause of your error. Do you want to add this column?</p>";
        echo "<a href='direct_fix.php' class='fix-button'>Fix the Issue</a>";
    }
    
    // Show sample data from brands table
    echo "<h3>Sample Data from brands table</h3>";
    $sample_data = mysqli_query($con, "SELECT * FROM brands LIMIT 5");
    
    if (!$sample_data) {
        echo "<p class='error'>Error getting sample data: " . mysqli_error($con) . "</p>";
    } else {
        if (mysqli_num_rows($sample_data) > 0) {
            echo "<table>";
            
            // Get column names
            $fields = mysqli_fetch_fields($sample_data);
            echo "<tr>";
            foreach ($fields as $field) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            
            // Display data
            while ($row = mysqli_fetch_assoc($sample_data)) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    if ($key == 'brand_logo' && !empty($value)) {
                        echo "<td><img src='$value' width='50' height='50' alt='Logo'></td>";
                    } else {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p class='info'>No data found in the brands table.</p>";
        }
    }
} else {
    echo "<p class='error'>The 'brands' table does NOT exist in the database!</p>";
}

// Check the uploads directory
echo "<h2>Upload Directory Check</h2>";
$upload_dirs = [
    'uploads',
    'uploads/brand_logos',
];

echo "<table>";
echo "<tr><th>Directory</th><th>Status</th><th>Permissions</th></tr>";

foreach ($upload_dirs as $dir) {
    echo "<tr>";
    echo "<td>$dir</td>";
    
    if (file_exists($dir)) {
        echo "<td class='success'>Exists</td>";
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        if (is_writable($dir)) {
            echo "<td class='success'>$perms (Writable)</td>";
        } else {
            echo "<td class='error'>$perms (Not Writable)</td>";
        }
    } else {
        echo "<td class='error'>Does Not Exist</td>";
        echo "<td class='error'>N/A</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Direct SQL query to add the column if it's missing
echo "<h2>Manual Fix Commands</h2>";
echo "<p>If you're still having issues, you can run this SQL command directly in phpMyAdmin:</p>";
echo "<pre>ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description;</pre>";

echo "<h2>Database Repair Options</h2>";
echo "<a href='direct_fix.php' class='fix-button'>Run Automatic Fix</a>";

// Show backtrace of the add_brand error
echo "<h2>Error Understanding</h2>";
echo "<p>The error you're seeing is:</p>";
echo "<pre>Fatal error: Uncaught mysqli_sql_exception: Unknown column 'brand_logo' in 'field list' in C:\\xampp\\htdocs\\cars\\home_page\\admin\\add_brand.php:66</pre>";

echo "<p>This happens because:</p>";
echo "<ol>
    <li>Line 66 in add_brand.php tries to insert data into the 'brand_logo' column</li>
    <li>The SQL command being executed is: <code>INSERT INTO brands (brand_name, description, brand_logo) VALUES (?, ?, ?)</code></li>
    <li>The database doesn't have a 'brand_logo' column in the 'brands' table</li>
</ol>";

// Generate all code from the admin page necessary for the brand logo feature
echo "</body></html>";
?> 