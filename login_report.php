<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('includes/db_connection.php');

// Function to generate access credentials and check status
function generate_access_report() {
    global $con;
    
    $report = [
        'status' => 'ok',
        'message' => 'Access credentials are ready for your project submission.',
        'accounts' => [],
        'issues' => []
    ];
    
    // Check database connection
    if (!$con) {
        $report['status'] = 'error';
        $report['message'] = 'Database connection failed: ' . mysqli_connect_error();
        $report['issues'][] = 'Database connection error';
        return $report;
    }
    
    // Check if users table exists
    $check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($check_table) == 0) {
        $report['status'] = 'error';
        $report['message'] = 'The users table does not exist.';
        $report['issues'][] = 'Missing users table';
        return $report;
    }
    
    // Check for admin account
    $admin_exists = false;
    $admin_query = mysqli_query($con, "SELECT id, name, email, password, is_admin FROM users WHERE email = 'admin@example.com'");
    
    if (mysqli_num_rows($admin_query) > 0) {
        $admin = mysqli_fetch_assoc($admin_query);
        $password_info = password_get_info($admin['password']);
        $is_hashed = $password_info['algo'] !== 0;
        
        if (!$is_hashed) {
            // Fix admin password
            $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
            mysqli_query($con, "UPDATE users SET password = '$admin_password' WHERE id = {$admin['id']}");
            $report['issues'][] = 'Admin password was not hashed properly - fixed automatically';
        }
        
        $admin_exists = true;
        $report['accounts'][] = [
            'type' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'status' => 'Ready'
        ];
    }
    
    // Check for test account
    $test_exists = false;
    $test_query = mysqli_query($con, "SELECT id, name, email, password FROM users WHERE email = 'test@example.com'");
    
    if (mysqli_num_rows($test_query) > 0) {
        $test = mysqli_fetch_assoc($test_query);
        $password_info = password_get_info($test['password']);
        $is_hashed = $password_info['algo'] !== 0;
        
        if (!$is_hashed) {
            // Fix test password
            $test_password = password_hash("test123", PASSWORD_DEFAULT);
            mysqli_query($con, "UPDATE users SET password = '$test_password' WHERE id = {$test['id']}");
            $report['issues'][] = 'Test password was not hashed properly - fixed automatically';
        }
        
        $test_exists = true;
        $report['accounts'][] = [
            'type' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'test123',
            'status' => 'Ready'
        ];
    }
    
    // Create missing accounts
    if (!$admin_exists) {
        $admin_name = "Administrator";
        $admin_email = "admin@example.com";
        $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
        
        $insert_admin = "INSERT INTO users (name, email, password, is_admin) 
                         VALUES ('$admin_name', '$admin_email', '$admin_password', 1)";
        
        if (mysqli_query($con, $insert_admin)) {
            $report['accounts'][] = [
                'type' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'admin123',
                'status' => 'Created'
            ];
            $report['issues'][] = 'Admin account was missing - created automatically';
        } else {
            $report['status'] = 'warning';
            $report['issues'][] = 'Failed to create admin account: ' . mysqli_error($con);
        }
    }
    
    if (!$test_exists) {
        $test_name = "Test User";
        $test_email = "test@example.com";
        $test_password = password_hash("test123", PASSWORD_DEFAULT);
        
        $insert_test = "INSERT INTO users (name, email, password, is_admin) 
                        VALUES ('$test_name', '$test_email', '$test_password', 0)";
        
        if (mysqli_query($con, $insert_test)) {
            $report['accounts'][] = [
                'type' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'test123',
                'status' => 'Created'
            ];
            $report['issues'][] = 'Test account was missing - created automatically';
        } else {
            $report['status'] = 'warning';
            $report['issues'][] = 'Failed to create test account: ' . mysqli_error($con);
        }
    }
    
    // Check login files
    $login_files = [
        'login.php' => file_exists('login.php'),
        'login_page/login_page.php' => file_exists('login_page/login_page.php'),
        'login_direct_fix.php' => file_exists('login_direct_fix.php')
    ];
    
    foreach ($login_files as $file => $exists) {
        if (!$exists) {
            $report['status'] = 'warning';
            $report['issues'][] = "Missing login file: $file";
        }
    }
    
    // Final status
    if (!empty($report['issues']) && $report['status'] == 'ok') {
        $report['status'] = 'warning';
    }
    
    return $report;
}

// Generate report
$access_report = generate_access_report();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Submission Access Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #3563E9;
            margin-bottom: 30px;
            text-align: center;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: none;
            font-weight: bold;
        }
        .status-ok {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .account-card {
            background-color: #e9f7fe;
            border-left: 5px solid #3563E9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .btn-access {
            background-color: #3563E9;
            color: white;
            border: none;
            margin-right: 10px;
        }
        .btn-access:hover {
            background-color: #2a4eb7;
            color: white;
        }
        .copy-btn {
            cursor: pointer;
            color: #3563E9;
            background: none;
            border: none;
            padding: 0;
            margin-left: 5px;
        }
        .copy-btn:hover {
            color: #2a4eb7;
            text-decoration: underline;
        }
        .submission-info {
            background-color: #e9f7fe;
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Project Submission Access Report</h1>
        
        <div class="status-<?php echo $access_report['status']; ?>">
            <?php echo $access_report['message']; ?>
        </div>
        
        <div class="card">
            <div class="card-header">Access Credentials</div>
            <div class="card-body">
                <?php foreach ($access_report['accounts'] as $account): ?>
                <div class="account-card">
                    <h5><?php echo $account['type']; ?> Account <span class="badge bg-success"><?php echo $account['status']; ?></span></h5>
                    <p><strong>Email:</strong> <?php echo $account['email']; ?> <button class="copy-btn" onclick="copyToClipboard('<?php echo $account['email']; ?>')">Copy</button></p>
                    <p><strong>Password:</strong> <?php echo $account['password']; ?> <button class="copy-btn" onclick="copyToClipboard('<?php echo $account['password']; ?>')">Copy</button></p>
                </div>
                <?php endforeach; ?>
                
                <div class="d-flex mt-3">
                    <a href="login.php" class="btn btn-access">Standard Login</a>
                    <a href="login_direct_fix.php" class="btn btn-access">Emergency Login</a>
                </div>
            </div>
        </div>
        
        <?php if (!empty($access_report['issues'])): ?>
        <div class="card">
            <div class="card-header">Issues Found & Fixed</div>
            <div class="card-body">
                <ul>
                    <?php foreach ($access_report['issues'] as $issue): ?>
                    <li><?php echo $issue; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="submission-info">
            <h4>For Your Submission:</h4>
            <p>Include these login credentials in your project submission documentation:</p>
            <div class="p-3 bg-light">
                <p><strong>Admin Account:</strong> admin@example.com / admin123</p>
                <p><strong>Test User Account:</strong> test@example.com / test123</p>
                <p><strong>Access URLs:</strong></p>
                <ul>
                    <li>Standard Login: <code><?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login.php'; ?></code></li>
                    <li>Emergency Login: <code><?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login_direct_fix.php'; ?></code></li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    // Show feedback (optional)
                    alert('Copied: ' + text);
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                });
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 