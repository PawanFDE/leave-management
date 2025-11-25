<?php
require_once 'auth.php';
require_once 'config.php';

if (!$auth->isLoggedIn() || $auth->getUserRole() != 'manager') {
    header('Location: login.php');
    exit;
}

$manager_id = $auth->getUserId();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['action'];
    $notes = $_POST['notes'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, manager_notes = ? WHERE id = ?");
    $stmt->execute([$status, $notes, $request_id]);
    
    header('Location: manage_requests.php');
    exit;
}

// Get pending requests for employees managed by this manager
$requests = $pdo->prepare("
    SELECT lr.*, u.username 
    FROM leave_requests lr 
    JOIN users u ON lr.user_id = u.id 
    WHERE u.manager_id = ? AND lr.status = 'pending'
    ORDER BY lr.created_at DESC
");
$requests->execute([$manager_id]);
$requests = $requests->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Leave Requests</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
    </div>
    
    <h2>Manage Leave Requests</h2>
    
    <?php if (empty($requests)): ?>
        <p>No pending leave requests.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['username']); ?></td>
                        <td><?php echo htmlspecialchars($request['leave_type']); ?></td>
                        <td><?php echo $request['start_date']; ?></td>
                        <td><?php echo $request['end_date']; ?></td>
                        <td><?php echo htmlspecialchars($request['reason']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <textarea name="notes" placeholder="Optional notes" rows="2" style="width: 200px; margin-bottom: 5px;"></textarea>
                                <div class="actions">
                                    <button type="submit" name="action" value="approved" class="btn btn-approve">Approve</button>
                                    <button type="submit" name="action" value="rejected" class="btn btn-reject">Reject</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>