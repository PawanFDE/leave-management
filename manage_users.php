<?php
require_once 'auth.php';
require_once 'config.php';

if (!$auth->isLoggedIn() || $auth->getUserRole() != 'admin') {
    header('Location: index.php');
    exit;
}

// Handle add user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = $_POST['email'];
        $role = $_POST['role'];
        $manager_id = $_POST['manager_id'] ?: null;
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, manager_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $role, $manager_id]);
        
        header('Location: manage_users.php');
        exit;
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        // Prevent deleting self
        if ($user_id != $auth->getUserId()) {
            // First, update any users who have this user as their manager
            $stmt = $pdo->prepare("UPDATE users SET manager_id = NULL WHERE manager_id = ?");
            $stmt->execute([$user_id]);
            
            // Then delete related leave requests
            $stmt = $pdo->prepare("DELETE FROM leave_requests WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Finally delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        
        header('Location: manage_users.php');
        exit;
    }
}

// Get all users and managers
$users = $pdo->query("SELECT u.*, m.username as manager_name FROM users u LEFT JOIN users m ON u.manager_id = m.id ORDER BY u.role, u.username")->fetchAll();
$managers = $pdo->query("SELECT * FROM users WHERE role = 'manager'")->fetchAll();

require_once 'header.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2>Manage Users</h2>
    
    <h3>Add New User</h3>
    <form method="POST">
        <div class="grid grid-2">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required placeholder="Username">
                <small id="username-feedback" style="display: block; margin-top: 5px; font-size: 0.875rem;"></small>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Password">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Email address">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="employee">Employee</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Manager (for employees)</label>
                <select name="manager_id">
                    <option value="">Select Manager</option>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?php echo $manager['id']; ?>"><?php echo $manager['username']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
    </form>
</div>

<div class="card">
    <h3>Existing Users</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Manager</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($user['username']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $user['role'] == 'admin' ? 'rejected' : ($user['role'] == 'manager' ? 'pending' : 'approved'); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['manager_name'] ?? '-'); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <?php if ($user['id'] != $auth->getUserId()): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const feedback = document.getElementById('username-feedback');
    const submitBtn = document.querySelector('button[name="add_user"]');

    let timeout = null;

    usernameInput.addEventListener('input', function() {
        const username = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(timeout);

        if (username.length > 0) {
            // Set a small delay to avoid too many requests while typing
            timeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('username', username);

                fetch('check_username.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        feedback.textContent = 'Username already exists';
                        feedback.style.color = '#dc2626'; // Red color
                        submitBtn.disabled = true;
                        usernameInput.style.borderColor = '#dc2626';
                    } else {
                        feedback.textContent = 'Username available';
                        feedback.style.color = '#16a34a'; // Green color
                        submitBtn.disabled = false;
                        usernameInput.style.borderColor = '#16a34a';
                    }
                })
                .catch(error => console.error('Error:', error));
            }, 300); // 300ms delay
        } else {
            feedback.textContent = '';
            submitBtn.disabled = false;
            usernameInput.style.borderColor = '';
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>