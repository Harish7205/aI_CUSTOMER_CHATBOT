<?php
$pageTitle = 'Analytics';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];
$botId = (int)($_GET['bot_id'] ?? 0);

$bots = $db->fetchAll("SELECT id, name FROM chatbot_bots WHERE business_id = ?", [$businessId]);
if ($botId && !in_array($botId, array_column($bots, 'id'))) $botId = 0;
if (!$botId && !empty($bots)) $botId = (int)$bots[0]['id'];

$stats = ['total_chats' => 0, 'total_messages' => 0, 'unique_visitors' => 0];
$daily = [];

if ($botId) {
    $analytics = $db->fetchOne("
        SELECT COALESCE(SUM(total_chats),0) as total_chats, COALESCE(SUM(total_messages),0) as total_messages, COALESCE(SUM(unique_visitors),0) as unique_visitors
        FROM chatbot_analytics WHERE bot_id = ?
    ", [$botId]);
    if ($analytics && ((int)$analytics['total_chats'] + (int)$analytics['total_messages']) > 0) {
        $stats = $analytics;
    }

    $daily = $db->fetchAll("
        SELECT date, total_chats, total_messages, unique_visitors
        FROM chatbot_analytics WHERE bot_id = ?
        ORDER BY date DESC LIMIT 30
    ", [$botId]);

    if (empty($daily)) {
        $raw = $db->fetchOne("
            SELECT (SELECT COUNT(*) FROM chatbot_chats WHERE bot_id = ?) as total_chats,
                   (SELECT COUNT(*) FROM chatbot_messages m JOIN chatbot_chats c ON m.chat_id = c.id WHERE c.bot_id = ?) as total_messages
        ", [$botId, $botId]);
        if ($raw && ((int)$raw['total_chats'] + (int)$raw['total_messages']) > 0) {
            $stats = array_merge($stats, $raw);
        }
    }
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
        <i class="bi bi-graph-up" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">Create a chatbot first.</p>
    </div>
</div>
<?php else: ?>
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="label">Total Chats</div>
        <div class="value"><?= number_format($stats['total_chats'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Total Messages</div>
        <div class="value"><?= number_format($stats['total_messages'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Unique Visitors</div>
        <div class="value"><?= number_format($stats['unique_visitors'] ?? 0) ?></div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">Daily Activity (Last 30 days)</div>
    <div class="card-body">
        <?php if (empty($daily)): ?>
        <p class="text-muted mb-0">No analytics data yet. Data is collected as visitors use your chatbot.</p>
        <?php else: ?>
        <table class="table-modern">
            <thead><tr><th>Date</th><th>Chats</th><th>Messages</th><th>Visitors</th></tr></thead>
            <tbody>
                <?php foreach ($daily as $d): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($d['date'])) ?></td>
                    <td><?= $d['total_chats'] ?></td>
                    <td><?= $d['total_messages'] ?></td>
                    <td><?= $d['unique_visitors'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
