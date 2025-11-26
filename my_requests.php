<?php
require_once 'auth.php';
require_once 'config.php';

if (!$auth->isLoggedIn() || $auth->getUserRole() != 'employee') {
    header('Location: index.php');
    exit;
}

$user_id = $auth->getUserId();
$requests = $pdo->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY created_at DESC");
$requests->execute([$user_id]);
$requests = $requests->fetchAll();

require_once 'header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>My Leave Requests</h2>
        <a href="request_leave.php" class="btn btn-primary">New Request</a>
    </div>
    
    <?php if (empty($requests)): ?>
        <p>No leave requests found.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Manager Notes</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['leave_type']); ?></td>
                            <td>
                                <?php echo $request['start_date']; ?> to <?php echo $request['end_date']; ?>
                            </td>
                            <td><?php echo htmlspecialchars($request['reason']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $request['status']; ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($request['manager_notes'] ?? '-'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>