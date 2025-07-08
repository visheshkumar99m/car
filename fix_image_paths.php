<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
require_once('includes/db_connection.php');

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Image Paths</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fix Car Image Paths</h1>
        <div class="alert alert-info">
            This script will check and fix image paths in the database to ensure they work correctly from any page.
        </div>
        
        <?php
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix'])) {
            echo '<h3>Fixing Image Paths...</h3>';
            
            // Get all cars from the database
            $query = "SELECT car_id, image, image_url FROM cars";
            $result = mysqli_query($con, $query);
            
            $fixed_count = 0;
            $error_count = 0;
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($car = mysqli_fetch_assoc($result)) {
                    $car_id = $car['car_id'];
                    $image = $car['image'];
                    $image_url = $car['image_url'];
                    $fixed = false;
                    
                    // Check and fix image path
                    if (!empty($image) && strpos($image, 'uploads/car_images/') === 0) {
                        $new_image = '../' . $image;
                        $update_query = "UPDATE cars SET image = ? WHERE car_id = ?";
                        $stmt = mysqli_prepare($con, $update_query);
                        mysqli_stmt_bind_param($stmt, "si", $new_image, $car_id);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            echo "<p class='success'>Updated image path for Car ID {$car_id}: {$image} → {$new_image}</p>";
                            $fixed = true;
                            $fixed_count++;
                        } else {
                            echo "<p class='error'>Error updating image path for Car ID {$car_id}: " . mysqli_error($con) . "</p>";
                            $error_count++;
                        }
                    }
                    
                    // Check and fix image_url path
                    if (!empty($image_url) && strpos($image_url, 'uploads/car_images/') === 0) {
                        $new_image_url = '../' . $image_url;
                        $update_query = "UPDATE cars SET image_url = ? WHERE car_id = ?";
                        $stmt = mysqli_prepare($con, $update_query);
                        mysqli_stmt_bind_param($stmt, "si", $new_image_url, $car_id);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            echo "<p class='success'>Updated image_url path for Car ID {$car_id}: {$image_url} → {$new_image_url}</p>";
                            $fixed = true;
                            $fixed_count++;
                        } else {
                            echo "<p class='error'>Error updating image_url path for Car ID {$car_id}: " . mysqli_error($con) . "</p>";
                            $error_count++;
                        }
                    }
                    
                    if (!$fixed) {
                        echo "<p>No changes needed for Car ID {$car_id} (current paths: image='{$image}', image_url='{$image_url}')</p>";
                    }
                }
                
                echo "<h4>Summary: Fixed {$fixed_count} paths, {$error_count} errors</h4>";
            } else {
                echo "<p>No cars found in the database.</p>";
            }
        }
        ?>
        
        <h3>Current Image Paths</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Car ID</th>
                    <th>Car Name</th>
                    <th>Image Path</th>
                    <th>Image URL Path</th>
                    <th>Preview</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get all cars from the database
                $query = "SELECT car_id, car_name, image, image_url FROM cars";
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($car = mysqli_fetch_assoc($result)) {
                        $image_to_display = !empty($car['image_url']) ? $car['image_url'] : $car['image'];
                        
                        echo "<tr>";
                        echo "<td>{$car['car_id']}</td>";
                        echo "<td>{$car['car_name']}</td>";
                        echo "<td>{$car['image']}</td>";
                        echo "<td>{$car['image_url']}</td>";
                        echo "<td><img src='{$image_to_display}' alt='Car Preview' width='100' height='60' style='object-fit: cover;'></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No cars found in the database.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <form method="post" class="mt-4">
            <button type="submit" name="fix" class="btn btn-primary">Fix Image Paths</button>
            <a href="admin/dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </form>
    </div>
</body>
</html> 