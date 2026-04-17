<?php
$pageTitle = 'Chat Logs';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];
$botId = (int)($_GET['bot_id'] ?? 0);

$bots = $db->fetchAll("SELECT id, name FROM chatbot_bots WHERE business_id = ?", [$businessId]);
if ($botId && !in_array($botId, array_column($bots, 'id'))) $botId = 0;
if (!$botId && !empty($bots)) $botId = (int)$bots[0]['id'];

$chats = [];
if ($botId) {
    $chats = $db->fetchAll("
        SELECT c.id, c.visitor_name, c.visitor_email, c.started_at,
               (SELECT COUNT(*) FROM chatbot_messages WHERE chat_id = c.id) as msg_count
        FROM chatbot_chats c
        WHERE c.bot_id = ?
        ORDER BY c.started_at DESC
        LIMIT 100
    ", [$botId]);
}
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
        <i class="bi bi-chat-dots" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">Create a chatbot first.</p>
    </div>
</div>
<?php elseif (empty($chats)): ?>
<div class="content-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-chat-dots" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">No chats yet. Embed your chatbot to start receiving conversations.</p>
    </div>
</div>
<?php else: ?>
<div class="content-card">
    <div class="card-body p-0">
        <table class="table-modern">
            <thead><tr><th>ID</th><th>Visitor</th><th>Email</th><th>Started</th><th>Messages</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($chats as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['visitor_name'] ?: 'Anonymous') ?></td>
                    <td><?= htmlspecialchars($c['visitor_email'] ?: '-') ?></td>
                    <td><?= date('M j, Y H:i', strtotime($c['started_at'])) ?></td>
                    <td><?= $c['msg_count'] ?></td>
                    <td><a href="chat-view.php?id=<?= $c['id'] ?>" class="btn-outline-modern btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
