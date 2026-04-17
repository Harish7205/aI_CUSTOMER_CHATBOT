<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];

$botCount = $db->fetchOne("SELECT COUNT(*) as c FROM chatbot_bots WHERE business_id = ?", [$businessId])['c'] ?? 0;
$docCount = $db->fetchOne("SELECT COUNT(*) as c FROM chatbot_documents d JOIN chatbot_bots b ON d.bot_id = b.id WHERE b.business_id = ?", [$businessId])['c'] ?? 0;
$faqCount = $db->fetchOne("SELECT COUNT(*) as c FROM chatbot_faqs f JOIN chatbot_bots b ON f.bot_id = b.id WHERE b.business_id = ?", [$businessId])['c'] ?? 0;
$chatCount = $db->fetchOne("SELECT COUNT(*) as c FROM chatbot_chats c JOIN chatbot_bots b ON c.bot_id = b.id WHERE b.business_id = ?", [$businessId])['c'] ?? 0;

$recentChats = $db->fetchAll("
    SELECT c.id, c.visitor_name, c.started_at, b.name as bot_name
    FROM chatbot_chats c
    JOIN chatbot_bots b ON c.bot_id = b.id
    WHERE b.business_id = ?
    ORDER BY c.started_at DESC
    LIMIT 10
", [$businessId]);
?>
<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Chatbots</div>
        <div class="value"><?= $botCount ?></div>
        <a href="bots.php" class="link">Manage bots →</a>
    </div>
    <div class="stat-card">
        <div class="label">Documents</div>
        <div class="value"><?= $docCount ?></div>
        <a href="documents.php" class="link">Upload documents →</a>
    </div>
    <div class="stat-card">
        <div class="label">FAQs</div>
        <div class="value"><?= $faqCount ?></div>
        <a href="faqs.php" class="link">Manage FAQs →</a>
    </div>
    <div class="stat-card">
        <div class="label">Total Chats</div>
        <div class="value"><?= $chatCount ?></div>
        <a href="chats.php" class="link">View logs →</a>
    </div>
</div>

<div class="content-card">
    <div class="card-header">Recent Chats</div>
    <div class="card-body">
        <?php if (empty($recentChats)): ?>
            <p class="text-muted mb-0">No chats yet. Embed your chatbot to start receiving conversations.</p>
        <?php else: ?>
            <table class="table-modern">
                <thead><tr><th>Visitor</th><th>Bot</th><th>Started</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($recentChats as $chat): ?>
                    <tr>
                        <td><?= htmlspecialchars($chat['visitor_name'] ?: 'Anonymous') ?></td>
                        <td><?= htmlspecialchars($chat['bot_name']) ?></td>
                        <td><?= date('M j, H:i', strtotime($chat['started_at'])) ?></td>
                        <td><a href="chat-view.php?id=<?= $chat['id'] ?>" class="btn-outline-modern btn-sm">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
