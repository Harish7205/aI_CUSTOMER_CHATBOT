<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
startSession();

if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = Database::getInstance();
        $existing = $db->fetchOne("SELECT id FROM businesses WHERE email = ?", [$email]);
        if ($existing) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->insert('businesses', [
                'name' => $name,
                'email' => $email,
                'password_hash' => $hash,
                'status' => 'active'
            ]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AI Chatbot Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-split auth-container">
            <div class="auth-features">
                <h2>Start your AI support journey</h2>
                <ul>
                    <li><i class="bi bi-robot"></i> Train chatbots on your FAQs & documents</li>
                    <li><i class="bi bi-globe"></i> Embed on any website in minutes</li>
                    <li><i class="bi bi-graph-up"></i> Track chats and analytics</li>
                </ul>
            </div>
            <div class="auth-container">
                <div class="auth-card">
                    <div class="card-body">
                        <div class="auth-logo">
                            <i class="bi bi-robot"></i>
                            <h1>Create account</h1>
                            <p>Get started in seconds</p>
                        </div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Business Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="My Company">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="you@company.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8" placeholder="••••••••">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••">
                            </div>
                            <button type="submit" class="btn-submit">Create Account</button>
                        </form>
                        <div class="auth-footer">
                            <a href="login.php">Already have an account? Log in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
