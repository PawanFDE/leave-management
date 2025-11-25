<?php
require_once 'auth.php';
require_once 'config.php';

if (!$auth->isLoggedIn() || $auth->getUserRole() != 'employee') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $auth->getUserId();
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    
    $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $leave_type, $start_date, $end_date, $reason]);
    
    header('Location: my_requests.php');
    exit;
}

// Get leave policies
$policies = $pdo->query("SELECT * FROM leave_policies")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Leave</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a> | 
        <a href="my_requests.php">My Requests</a>
    </div>
    
    <h2>Request Leave</h2>
    
    <form method="POST">
        <div class="form-group">
            <label>Leave Type:</label>
            <select name="leave_type" required>
                <option value="">Select Leave Type</option>
                <?php foreach ($policies as $policy): ?>
                    <option value="<?php echo $policy['leave_type']; ?>">
                        <?php echo $policy['leave_type']; ?> (Max: <?php echo $policy['max_days']; ?> days)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Start Date:</label>
            <input type="date" name="start_date" required>
        </div>
        
        <div class="form-group">
            <label>End Date:</label>
            <input type="date" name="end_date" required>
        </div>
        
        <div class="form-group">
            <label>Reason:</label>
            <textarea name="reason" rows="4" required></textarea>
        </div>
        
        <button type="submit">Submit Request</button>
    </form>
</body>
</html>