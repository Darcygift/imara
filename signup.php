<?php
// signup.php - Secure User Signup Page
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role']; // landlord or tenant
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please provide a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_msg = "This email address is already registered.";
        } else {
            // Hash password and insert user
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, verified) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $hashed_pass, $role, 0]);
                
                // Retrieve the new user and set session
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_name'] = $name;
                
                header("Location: dashboard.php");
                exit;
            } catch (PDOException $e) {
                $error_msg = "Registration failed. Please try again. " . $e->getMessage();
            }
        }
    }
}

require_once 'header.php';
?>

<div style="min-height: 85vh; display: flex; align-items: center; justify-content: center; padding: 7.5rem 1.5rem 3.5rem 1.5rem;">
    <div class="glass-panel animate-fade-in" style="width: 100%; max-width: 480px; padding: 2.5rem; border-radius: 1.5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <span style="font-size: 2.5rem;">✨</span>
            <h2 style="font-size: 1.75rem; margin-top: 1rem; color: var(--text-primary);">Create Account</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">Start exploring or listing premium properties</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div style="background: rgba(248, 113, 113, 0.15); border: 1px solid var(--accent-danger); color: #f87171; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 500;">
                ⚠️ <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="signup.php" style="display: flex; flex-direction: column; gap: 1.15rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; letter-spacing: 0.05em;">Full Name *</label>
                <input type="text" name="name" class="glass-input" required placeholder="e.g. Darcy Gift" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; letter-spacing: 0.05em;">Email Address *</label>
                <input type="email" name="email" class="glass-input" required placeholder="e.g. name@domain.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; letter-spacing: 0.05em;">Phone Number</label>
                <input type="text" name="phone" class="glass-input" placeholder="e.g. +250 788 000 000" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; letter-spacing: 0.05em;">I am signing up as a *</label>
                <select name="role" class="glass-input" style="cursor: pointer;">
                    <option value="tenant" <?php echo (isset($_POST['role']) && $_POST['role'] === 'tenant') ? 'selected' : ''; ?>>Tenant (looking for properties)</option>
                    <option value="landlord" <?php echo (isset($_POST['role']) && $_POST['role'] === 'landlord') ? 'selected' : ''; ?>>Landlord / Agent (listing properties)</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; letter-spacing: 0.05em;">Password *</label>
                    <input type="password" name="password" class="glass-input" required placeholder="••••••••">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; letter-spacing: 0.05em;">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="glass-input" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; margin-top: 0.5rem; border-radius: 9999px;">
                Create Account & Log In
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-secondary);">
            Already have an account? 
            <a href="login.php" style="color: var(--accent-primary); font-weight: 700; text-decoration: underline;">Sign in instead</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
