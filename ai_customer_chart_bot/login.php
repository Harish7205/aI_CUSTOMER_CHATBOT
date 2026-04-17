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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $db = Database::getInstance();
        $business = $db->fetchOne("SELECT id, password_hash FROM businesses WHERE email = ? AND status = 'active'", [$email]);
        if ($business && password_verify($password, $business['password_hash'])) {
            $_SESSION['business_id'] = $business['id'];
            header('Location: ' . APP_URL . '/admin/dashboard.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Chatbot Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-split auth-container">
            <div class="auth-features">
                <h2>Build AI-powered support for your business</h2>
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
                            <h1>Welcome back</h1>
                            <p>Sign in to your account</p>
                        </div>
                        <?php if (isset($_GET['registered'])): ?>
                            <div class="alert alert-success">Registration successful! Please log in.</div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="you@company.com">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="••••••••">
                            </div>
                            <button type="submit" class="btn-submit">Log In</button>
                        </form>
                        <div class="auth-footer">
                            <a href="register.php">Don't have an account? Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
