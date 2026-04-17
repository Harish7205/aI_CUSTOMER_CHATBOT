<?php
$pageTitle = 'Edit Bot';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$db = Database::getInstance();
$bot = $db->fetchOne("SELECT * FROM chatbot_bots WHERE id = ? AND business_id = ?", [$id, $_SESSION['business_id']]);

if (!$bot) {
    header('Location: ' . APP_URL . '/admin/bots.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->update('chatbot_bots', [
        'name' => trim($_POST['name'] ?? $bot['name']),
        'welcome_message' => trim($_POST['welcome_message'] ?? ''),
        'theme_color' => $_POST['theme_color'] ?? '#2563eb',
        'position' => $_POST['position'] ?? 'bottom-right',
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ], 'id = ?', [$id]);
    header('Location: ' . APP_URL . '/admin/bots.php');
    exit;
}
?>
<div style="max-width:600px">
    <div class="content-card">
        <div class="card-header">Edit Bot: <?= htmlspecialchars($bot['name']) ?></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Bot Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($bot['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Welcome Message</label>
                    <textarea name="welcome_message" class="form-control" rows="3"><?= htmlspecialchars($bot['welcome_message']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Theme Color</label>
                    <input type="color" name="theme_color" value="<?= htmlspecialchars($bot['theme_color']) ?>" class="form-control form-control-color" style="width:60px;height:40px">
                </div>
                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <select name="position" class="form-select">
                        <?php foreach (['bottom-right','bottom-left','top-right','top-left'] as $p): ?>
                        <option value="<?= $p ?>" <?= $bot['position'] === $p ? 'selected' : '' ?>><?= ucwords(str_replace('-',' ',$p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="active" <?= $bot['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-primary-modern">Save</button>
                    <a href="bots.php" class="btn-outline-modern">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
