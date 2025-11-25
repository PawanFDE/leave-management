<?php
require_once 'auth.php';
require_once 'config.php';

if (!$auth->isLoggedIn() || $auth->getUserRole() != 'admin') {
    header('Location: login.php');
    exit;
}

// Handle add/update policy
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_policy'])) {
        $leave_type = $_POST['leave_type'];
        $max_days = $_POST['max_days'];
        $description = $_POST['description'];
        
        $stmt = $pdo->prepare("INSERT INTO leave_policies (leave_type, max_days, description) VALUES (?, ?, ?)");
        $stmt->execute([$leave_type, $max_days, $description]);
    } elseif (isset($_POST['update_policy'])) {
        $id = $_POST['policy_id'];
        $max_days = $_POST['max_days'];
        $description = $_POST['description'];
        
        $stmt = $pdo->prepare("UPDATE leave_policies SET max_days = ?, description = ? WHERE id = ?");
        $stmt->execute([$max_days, $description, $id]);
    }
    
    header('Location: manage_policies.php');
    exit;
}

$policies = $pdo->query("SELECT * FROM leave_policies ORDER BY leave_type")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Leave Policies</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">Dashboard</a> | 
        <a href="manage_users.php">Manage Users</a>
    </div>
    
    <h2>Manage Leave Policies</h2>
    
    <h3>Add New Policy</h3>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Leave Type:</label>
                <input type="text" name="leave_type" required>
            </div>
            <div class="form-group">
                <label>Maximum Days:</label>
                <input type="number" name="max_days" required>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>Description:</label>
                <textarea name="description" rows="3"></textarea>
            </div>
        </div>
        <button type="submit" name="add_policy">Add Policy</button>
    </form>
    
    <h3>Existing Policies</h3>
    <table>
        <thead>
            <tr>
                <th>Leave Type</th>
                <th>Max Days</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($policies as $policy): ?>
                <tr>
                    <td><?php echo htmlspecialchars($policy['leave_type']); ?></td>
                    <td><?php echo $policy['max_days']; ?></td>
                    <td><?php echo htmlspecialchars($policy['description']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="policy_id" value="<?php echo $policy['id']; ?>">
                            <input type="number" name="max_days" value="<?php echo $policy['max_days']; ?>" style="width: 80px;">
                            <textarea name="description" rows="2" style="width: 200px;"><?php echo htmlspecialchars($policy['description']); ?></textarea>
                            <button type="submit" name="update_policy">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>