<?php
if (!isset($auth)) {
    require_once 'auth.php';
}
$role = $auth->getUserRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management System</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-content">
            <div class="nav-brand">Leave Management</div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                
                <?php if ($role == 'employee'): ?>
                    <a href="request_leave.php" class="nav-link">Request Leave</a>
                    <a href="my_requests.php" class="nav-link">My Requests</a>
                <?php endif; ?>
                
                <?php if ($role == 'manager'): ?>
                    <a href="manage_requests.php" class="nav-link">Manage Requests</a>
                <?php endif; ?>
                
                <?php if ($role == 'admin'): ?>
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                    <a href="manage_policies.php" class="nav-link">Manage Policies</a>
                <?php endif; ?>
                
                <a href="logout.php" class="nav-link btn-logout">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <br>
