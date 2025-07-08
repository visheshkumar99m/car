<?php
// Include database connection
require_once('includes/db_connection.php');

// Display table structure
echo "<h1>Brands Table Structure</h1>";
$result = mysqli_query($con, "DESCRIBE brands");

if (!$result) {
    echo "Error: " . mysqli_error($con);
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

// Show all brands in the database
echo "<h1>Existing Brands</h1>";
$brands_result = mysqli_query($con, "SELECT * FROM brands");

if (!$brands_result) {
    echo "Error: " . mysqli_error($con);
    exit;
}

if (mysqli_num_rows($brands_result) > 0) {
    echo "<table border='1'>";
    echo "<tr>";
    // Get field names
    $fields = mysqli_fetch_fields($brands_result);
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    // Output data
    while ($brand = mysqli_fetch_assoc($brands_result)) {
        echo "<tr>";
        foreach ($brand as $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No brands found in the database.</p>";
}
?> 