<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = "localhost";
$user = "root";
$password = ""; // Default XAMPP password

echo "<h1>Database Sync Tool</h1>";

// Connect to MySQL server
$mysql = @mysqli_connect($host, $user, $password);

if (!$mysql) {
    die("<p style='color: red;'>Failed to connect to MySQL server: " . mysqli_connect_error() . "</p>");
}

echo "<p style='color: green;'>Connected to MySQL server successfully.</p>";

// Check if old database exists
$check_old_db = mysqli_query($mysql, "SHOW DATABASES LIKE 'user_data'");
$old_db_exists = mysqli_num_rows($check_old_db) > 0;

// Check if new database exists
$check_new_db = mysqli_query($mysql, "SHOW DATABASES LIKE 'cars_data'");
$new_db_exists = mysqli_num_rows($check_new_db) > 0;

echo "<p>Old database (user_data) exists: " . ($old_db_exists ? "Yes" : "No") . "</p>";
echo "<p>New database (cars_data) exists: " . ($new_db_exists ? "Yes" : "No") . "</p>";

// Create new database if it doesn't exist
if (!$new_db_exists) {
    echo "<p>Creating new database 'cars_data'...</p>";
    
    if (mysqli_query($mysql, "CREATE DATABASE cars_data")) {
        echo "<p style='color: green;'>Database 'cars_data' created successfully.</p>";
        $new_db_exists = true;
    } else {
        echo "<p style='color: red;'>Error creating database: " . mysqli_error($mysql) . "</p>";
    }
}

// Connect to the new database
if ($new_db_exists) {
    $cars_db = mysqli_connect($host, $user, $password, "cars_data");
    
    if (!$cars_db) {
        echo "<p style='color: red;'>Failed to connect to 'cars_data' database: " . mysqli_connect_error() . "</p>";
    } else {
        echo "<p style='color: green;'>Connected to 'cars_data' database successfully.</p>";
        
        // Check if users table exists
        $check_users = mysqli_query($cars_db, "SHOW TABLES LIKE 'users'");
        $users_exists = mysqli_num_rows($check_users) > 0;
        
        echo "<p>Users table exists in 'cars_data': " . ($users_exists ? "Yes" : "No") . "</p>";
        
        // If users table exists, check its columns
        $columns = array();
        $has_phone_column = false;
        $has_address_column = false;
        
        if ($users_exists) {
            $columns_result = mysqli_query($cars_db, "SHOW COLUMNS FROM users");
            while ($column = mysqli_fetch_assoc($columns_result)) {
                $columns[] = $column['Field'];
                if ($column['Field'] == 'phone') {
                    $has_phone_column = true;
                }
                if ($column['Field'] == 'address') {
                    $has_address_column = true;
                }
            }
            
            echo "<p>Existing columns in users table: " . implode(", ", $columns) . "</p>";
            
            // If phone or address columns don't exist, add them
            if (!$has_phone_column) {
                if (mysqli_query($cars_db, "ALTER TABLE users ADD COLUMN phone VARCHAR(20)")) {
                    echo "<p style='color: green;'>Added 'phone' column to users table</p>";
                    $has_phone_column = true;
                } else {
                    echo "<p style='color: red;'>Failed to add 'phone' column: " . mysqli_error($cars_db) . "</p>";
                }
            }
            
            if (!$has_address_column) {
                if (mysqli_query($cars_db, "ALTER TABLE users ADD COLUMN address TEXT")) {
                    echo "<p style='color: green;'>Added 'address' column to users table</p>";
                    $has_address_column = true;
                } else {
                    echo "<p style='color: red;'>Failed to add 'address' column: " . mysqli_error($cars_db) . "</p>";
                }
            }
        } else {
            // Create users table if it doesn't exist
            echo "<p>Creating users table in 'cars_data'...</p>";
            
            $create_users = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            
            if (mysqli_query($cars_db, $create_users)) {
                echo "<p style='color: green;'>Users table created successfully.</p>";
                $users_exists = true;
                $has_phone_column = true;
                $has_address_column = true;
            } else {
                echo "<p style='color: red;'>Error creating users table: " . mysqli_error($cars_db) . "</p>";
            }
        }
        
        // Migrate users from old database if it exists
        if ($old_db_exists && $users_exists) {
            $user_db = mysqli_connect($host, $user, $password, "user_data");
            
            if (!$user_db) {
                echo "<p style='color: red;'>Failed to connect to 'user_data' database: " . mysqli_connect_error() . "</p>";
            } else {
                echo "<p style='color: green;'>Connected to 'user_data' database successfully.</p>";
                
                // Check if old register_login_data table exists
                $check_old_table = mysqli_query($user_db, "SHOW TABLES LIKE 'register_login_data'");
                $old_table_exists = mysqli_num_rows($check_old_table) > 0;
                
                echo "<p>Old register_login_data table exists: " . ($old_table_exists ? "Yes" : "No") . "</p>";
                
                if ($old_table_exists) {
                    echo "<p>Migrating users from old database...</p>";
                    
                    // Get all users from old table
                    $get_old_users = mysqli_query($user_db, "SELECT * FROM register_login_data");
                    
                    if (!$get_old_users) {
                        echo "<p style='color: red;'>Error retrieving users from old table: " . mysqli_error($user_db) . "</p>";
                    } else {
                        $migrated_count = 0;
                        
                        while ($old_user = mysqli_fetch_assoc($get_old_users)) {
                            // Check if user already exists in new table
                            $email = mysqli_real_escape_string($cars_db, $old_user['email']);
                            $check_user = mysqli_query($cars_db, "SELECT id FROM users WHERE email = '$email'");
                            
                            if (mysqli_num_rows($check_user) == 0) {
                                // User doesn't exist in new table, migrate them
                                $name = mysqli_real_escape_string($cars_db, $old_user['name']);
                                $password = $old_user['password']; // Get original password
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash it properly
                                
                                // Build the query based on available columns
                                $insert_query = "INSERT INTO users (name, email, password";
                                $values_query = "VALUES ('$name', '$email', '$hashed_password'";
                                
                                if ($has_phone_column && isset($old_user['number'])) {
                                    $phone = mysqli_real_escape_string($cars_db, $old_user['number']);
                                    $insert_query .= ", phone";
                                    $values_query .= ", '$phone'";
                                }
                                
                                if ($has_address_column && isset($old_user['address'])) {
                                    $address = mysqli_real_escape_string($cars_db, $old_user['address']);
                                    $insert_query .= ", address";
                                    $values_query .= ", '$address'";
                                }
                                
                                $insert_query .= ") " . $values_query . ")";
                                
                                if (mysqli_query($cars_db, $insert_query)) {
                                    $migrated_count++;
                                    echo "<p>Migrated user: $email</p>";
                                } else {
                                    echo "<p style='color: red;'>Error migrating user $email: " . mysqli_error($cars_db) . "</p>";
                                    echo "<p>Query: $insert_query</p>";
                                }
                            } else {
                                echo "<p>User $email already exists in new database, skipping.</p>";
                            }
                        }
                        
                        echo "<p style='color: green;'>Migration complete. Migrated $migrated_count users.</p>";
                    }
                    
                    mysqli_close($user_db);
                }
            }
        }
        
        // Create admin user if it doesn't exist
        $check_admin = mysqli_query($cars_db, "SELECT id FROM users WHERE email = 'admin@example.com'");
        
        if (mysqli_num_rows($check_admin) == 0) {
            echo "<p>Creating admin user...</p>";
            
            $admin_name = "Administrator";
            $admin_email = "admin@example.com";
            $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
            
            $insert_admin = "INSERT INTO users (name, email, password, is_admin) 
                             VALUES ('$admin_name', '$admin_email', '$admin_password', 1)";
            
            if (mysqli_query($cars_db, $insert_admin)) {
                echo "<p style='color: green;'>Admin user created successfully.</p>";
                echo "<p>Email: admin@example.com<br>Password: admin123</p>";
            } else {
                echo "<p style='color: red;'>Error creating admin user: " . mysqli_error($cars_db) . "</p>";
            }
        } else {
            echo "<p>Admin user already exists.</p>";
            
            // Reset admin password
            $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
            
            if (mysqli_query($cars_db, "UPDATE users SET password = '$admin_password' WHERE email = 'admin@example.com'")) {
                echo "<p style='color: green;'>Admin password reset to 'admin123'</p>";
            } else {
                echo "<p style='color: red;'>Error resetting admin password: " . mysqli_error($cars_db) . "</p>";
            }
        }
        
        // Create test user if it doesn't exist
        $check_test = mysqli_query($cars_db, "SELECT id FROM users WHERE email = 'test@example.com'");
        
        if (mysqli_num_rows($check_test) == 0) {
            echo "<p>Creating test user...</p>";
            
            $test_name = "Test User";
            $test_email = "test@example.com";
            $test_password = password_hash("test123", PASSWORD_DEFAULT);
            
            $insert_test = "INSERT INTO users (name, email, password) 
                            VALUES ('$test_name', '$test_email', '$test_password')";
            
            if (mysqli_query($cars_db, $insert_test)) {
                echo "<p style='color: green;'>Test user created successfully.</p>";
                echo "<p>Email: test@example.com<br>Password: test123</p>";
            } else {
                echo "<p style='color: red;'>Error creating test user: " . mysqli_error($cars_db) . "</p>";
            }
        } else {
            echo "<p>Test user already exists.</p>";
            
            // Reset test password
            $test_password = password_hash("test123", PASSWORD_DEFAULT);
            
            if (mysqli_query($cars_db, "UPDATE users SET password = '$test_password' WHERE email = 'test@example.com'")) {
                echo "<p style='color: green;'>Test password reset to 'test123'</p>";
            } else {
                echo "<p style='color: red;'>Error resetting test password: " . mysqli_error($cars_db) . "</p>";
            }
        }
        
        mysqli_close($cars_db);
    }
}

mysqli_close($mysql);

echo "<h2>Next Steps</h2>";
echo "<p>Now that the databases are synchronized, try logging in with one of these accounts:</p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@example.com / admin123</li>";
echo "<li><strong>Test User:</strong> test@example.com / test123</li>";
echo "</ul>";
echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
?> 