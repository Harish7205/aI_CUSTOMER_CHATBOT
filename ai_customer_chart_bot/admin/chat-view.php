<?php
$pageTitle = 'Chat View';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
$db = Database::getInstance();
$businessId = $_SESSION['business_id'];

$chat = $db->fetchOne("
    SELECT c.*, b.name as bot_name
    FROM chatbot_chats c
    JOIN chatbot_bots b ON c.bot_id = b.id
    WHERE c.id = ? AND b.business_id = ?
", [$id, $businessId]);

if (!$chat) {
    header('Location: ' . APP_URL . '/admin/chats.php');
    exit;
}

$messages = $db->fetchAll("SELECT role, content, created_at FROM chatbot_messages WHERE chat_id = ? ORDER BY id", [$id]);
?>
<div class="mb-4">
    <a href="chats.php" class="btn-outline-modern" style="text-decoration:none">&larr; Back to chats</a>
</div>
<div class="content-card">
    <div class="card-header">
        Chat #<?= $chat['id'] ?> &middot; <?= htmlspecialchars($chat['bot_name']) ?>
        <?php if ($chat['visitor_name']): ?> &middot; <?= htmlspecialchars($chat['visitor_name']) ?><?php endif; ?>
        &middot; <?= date('M j, Y H:i', strtotime($chat['started_at'])) ?>
    </div>
    <div class="card-body" style="max-height:70vh;overflow-y:auto">
        <?php foreach ($messages as $m): ?>
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge <?= $m['role'] === 'user' ? 'bg-primary' : 'bg-secondary' ?>"><?= ucfirst($m['role']) ?></span>
                <small class="text-muted"><?= date('H:i:s', strtotime($m['created_at'])) ?></small>
            </div>
            <div style="padding:0.75rem;background:<?= $m['role'] === 'user' ? '#eff6ff' : '#f8fafc' ?>;border-radius:8px">
                <?= nl2br(htmlspecialchars($m['content'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
