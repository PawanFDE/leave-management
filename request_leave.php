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
    
    // Calculate requested days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $requested_days = $interval->days + 1;

    // Get max days for this leave type
    $stmt = $pdo->prepare("SELECT max_days FROM leave_policies WHERE leave_type = ?");
    $stmt->execute([$leave_type]);
    $policy = $stmt->fetch();
    $max_days = $policy['max_days'];

    // Calculate used days (approved + pending)
    $stmt = $pdo->prepare("
        SELECT SUM(DATEDIFF(end_date, start_date) + 1) as used_days 
        FROM leave_requests 
        WHERE user_id = ? 
        AND leave_type = ? 
        AND status IN ('approved', 'pending')
    ");
    $stmt->execute([$user_id, $leave_type]);
    $result = $stmt->fetch();
    $used_days = $result['used_days'] ?: 0;

    if (($used_days + $requested_days) > $max_days) {
        $error = "Insufficient leave balance. You have used $used_days out of $max_days days for $leave_type. Requesting $requested_days days would exceed your limit.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $leave_type, $start_date, $end_date, $reason]);
        
        header('Location: my_requests.php');
        exit;
    }
}

// Get leave policies
$policies = $pdo->query("SELECT * FROM leave_policies")->fetchAll();
?>

<?php require_once 'header.php'; ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Request Leave</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Leave Type</label>
            <select name="leave_type" required>
                <option value="">Select Leave Type</option>
                <?php foreach ($policies as $policy): ?>
                    <option value="<?php echo $policy['leave_type']; ?>">
                        <?php echo $policy['leave_type']; ?> (Max: <?php echo $policy['max_days']; ?> days)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="grid grid-2">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" required>
            </div>
            
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Reason</label>
            <textarea name="reason" rows="4" required placeholder="Please provide a reason for your leave request..."></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>