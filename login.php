<?php
// login.php - Secure User Login Page
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_msg = "Please enter both your email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error_msg = "Invalid email address or password.";
        }
    }
}

require_once 'header.php';
?>

<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 7rem 1.5rem 3rem 1.5rem;">
    <div class="glass-panel animate-fade-in" style="width: 100%; max-width: 440px; padding: 2.5rem; border-radius: 1.5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <span style="font-size: 2.5rem;">🔑</span>
            <h2 style="font-size: 1.75rem; margin-top: 1rem; color: var(--text-primary);">Welcome Back</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">Sign in to manage your bookings and chats</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div style="background: rgba(248, 113, 113, 0.15); border: 1px solid var(--accent-danger); color: #f87171; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 500;">
                ⚠️ <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; letter-spacing: 0.05em;">Email Address</label>
                <input type="email" name="email" class="glass-input" required placeholder="demo@imara.rw" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.5rem; letter-spacing: 0.05em;">Password</label>
                <input type="password" name="password" class="glass-input" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; margin-top: 0.5rem; border-radius: 9999px;">
                Authenticate & Enter
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; font-size: 0.85rem; color: var(--text-secondary);">
            Don't have an account? 
            <a href="signup.php" style="color: var(--accent-primary); font-weight: 700; text-decoration: underline;">Create account</a>
        </div>
        
        <div class="glass-panel" style="margin-top: 1.5rem; padding: 1rem; font-size: 0.75rem; border-color: rgba(56,189,248,0.2); background: rgba(56,189,248,0.05);">
            <strong style="color: var(--accent-primary);">💡 Quick Demo Landlord Login:</strong><br>
            Email: <code style="color: var(--text-primary);">demo@imara.rw</code><br>
            Password: <code style="color: var(--text-primary);">Demo1234!</code>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
