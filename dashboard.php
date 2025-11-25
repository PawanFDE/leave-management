<?php
require_once 'auth.php';

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$role = $auth->getUserRole();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Leave Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .header { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .nav { background: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .content { background: white; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Leave Management System</h1>
        <p>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $role; ?>) | 
           <a href="logout.php">Logout</a>
        </p>
    </div>
    
    <div class="nav">
        <?php if ($role == 'employee'): ?>
            <a href="request_leave.php">Request Leave</a>
            <a href="my_requests.php">My Leave Requests</a>
        <?php elseif ($role == 'manager'): ?>
            <a href="manage_requests.php">Manage Leave Requests</a>
        <?php elseif ($role == 'admin'): ?>
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_policies.php">Manage Leave Policies</a>
        <?php endif; ?>
    </div>
    
    <div class="content">
        <h2>Dashboard</h2>
        <p>Select an option from the navigation menu above.</p>
    </div>
</body>
</html>