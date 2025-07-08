<?php
// Include database connection
require_once('includes/db_connection.php');

// Check if column already exists
echo "<h1>Adding brand_logo column to brands table</h1>";
$check_column = mysqli_query($con, "SHOW COLUMNS FROM brands LIKE 'brand_logo'");

if (mysqli_num_rows($check_column) > 0) {
    echo "<p>The 'brand_logo' column already exists in the brands table.</p>";
} else {
    // Add the missing column
    $add_column_query = "ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description";
    
    if (mysqli_query($con, $add_column_query)) {
        echo "<p style='color: green;'>✅ Successfully added 'brand_logo' column to the brands table!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding column: " . mysqli_error($con) . "</p>";
    }
}

// Show updated table structure
echo "<h2>Updated brands table structure:</h2>";
$result = mysqli_query($con, "DESCRIBE brands");

if (!$result) {
    echo "<p style='color: red;'>Error: " . mysqli_error($con) . "</p>";
    exit;
}

// Output table structure
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='admin/brands.php'>Return to Brands Management</a></p>";
?> 