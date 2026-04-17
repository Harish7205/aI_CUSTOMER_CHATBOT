<?php
$pageTitle = 'Embed Code';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];
$botId = (int)($_GET['bot_id'] ?? 0);

$bots = $db->fetchAll("SELECT id, name FROM chatbot_bots WHERE business_id = ?", [$businessId]);
if ($botId && !in_array($botId, array_column($bots, 'id'))) $botId = 0;
if (!$botId && !empty($bots)) $botId = (int)$bots[0]['id'];

$embedUrl = APP_URL . '/widget/chat-widget.js';
$embedCode = $botId ? '<script src="' . $embedUrl . '?bot_id=' . $botId . '"></script>' : '';
?>
<div class="page-toolbar">
    <div></div>
    <select class="select-modern" onchange="location.href='?bot_id='+this.value">
        <?php foreach ($bots as $b): ?><option value="<?= $b['id'] ?>" <?= $botId == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
    </select>
</div>

<?php if (empty($bots)): ?>
<div class="content-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-code-slash" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">Create a chatbot first.</p>
    </div>
</div>
<?php else: ?>
<div class="content-card">
    <div class="card-header">Add this code to your website</div>
    <div class="card-body">
        <p class="text-muted mb-3">Paste this script tag before the closing <code>&lt;/body&gt;</code> of your HTML:</p>
        <pre class="code-block" style="position:relative"><code id="embedCode"><?= htmlspecialchars($embedCode) ?></code></pre>
        <button class="btn-primary-modern mt-2" id="copyBtn" onclick="navigator.clipboard.writeText(document.getElementById('embedCode').textContent); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy', 2000)">Copy</button>
    </div>
</div>

<div class="content-card mt-4">
    <div class="card-header">Preview</div>
    <div class="card-body">
        <p class="text-muted small">Your chatbot will appear as a floating button on your website. Visitors can click to open the chat panel.</p>
        <p class="mb-0"><strong>Widget URL:</strong> <code><?= htmlspecialchars($embedUrl) ?></code></p>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
