<?php
// Include database connection
require_once('includes/db_connection.php');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if orders table exists
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'orders'");
$orders_table_exists = mysqli_num_rows($table_check) > 0;

// If orders table doesn't exist, show setup notification
if (!$orders_table_exists) {
    // Keep the setup page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CarWale - Car Dealership</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="css/styles.css">
        <style>
            .setup-notification {
                background-color: #f8f9fa;
                border-left: 4px solid #3563E9;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .setup-btn {
                background-color: #3563E9;
                color: white;
                border: none;
                padding: 8px 15px;
                border-radius: 4px;
                text-decoration: none;
                margin-top: 10px;
                display: inline-block;
            }
            .setup-btn:hover {
                background-color: #2a4ebf;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="container mt-4">
            <div class="setup-notification">
                <h4>Database Setup Required</h4>
                <p>The orders table is missing from your database. This table is required for the admin dashboard to function properly.</p>
                <a href="create_orders_table.php" class="setup-btn">Create Orders Table</a>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} else {
    // Redirect to the front page
    header("Location: front_page/front_page.php");
    exit;
}
?> 