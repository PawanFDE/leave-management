<?php
require_once 'auth.php';
require_once 'config.php';

if (!$auth->isLoggedIn() || $auth->getUserRole() != 'employee') {
    header('Location: login.php');
    exit;
}

$user_id = $auth->getUserId();
$requests = $pdo->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC");
$requests->execute([$user_id]);
$requests = $requests->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Leave Requests</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .pending { color: #ffc107; }
        .approved { color: #28a745; }
        .rejected { color: #dc3545; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a> | 
        <a href="request_leave.php">Request Leave</a>
    </div>
    
    <h2>My Leave Requests</h2>
    
    <?php if (empty($requests)): ?>
        <p>No leave requests found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Manager Notes</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['leave_type']); ?></td>
                        <td><?php echo $request['start_date']; ?></td>
                        <td><?php echo $request['end_date']; ?></td>
                        <td><?php echo htmlspecialchars($request['reason']); ?></td>
                        <td class="<?php echo $request['status']; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($request['manager_notes'] ?? 'N/A'); ?></td>
                        <td><?php echo $request['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>