<?php
$pageTitle = 'Chatbots';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];
$bots = $db->fetchAll("SELECT * FROM chatbot_bots WHERE business_id = ? ORDER BY created_at DESC", [$businessId]);
?>
<div class="page-toolbar">
    <div></div>
    <button class="btn-primary-modern" data-bs-toggle="modal" data-bs-target="#createBotModal"><i class="bi bi-plus-lg"></i> Create Bot</button>
</div>

<div class="bot-grid">
    <?php foreach ($bots as $bot): ?>
    <div class="bot-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="bot-name mb-0"><?= htmlspecialchars($bot['name']) ?></h5>
            <span class="badge <?= $bot['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $bot['is_active'] ? 'Active' : 'Inactive' ?></span>
        </div>
        <p class="bot-desc"><?= htmlspecialchars(substr($bot['welcome_message'] ?? '', 0, 100)) ?><?= strlen($bot['welcome_message'] ?? '') > 100 ? '...' : '' ?></p>
        <div class="bot-actions">
            <a href="bot-edit.php?id=<?= $bot['id'] ?>" class="btn-outline-modern">Edit</a>
            <a href="documents.php?bot_id=<?= $bot['id'] ?>" class="btn-outline-modern">Documents</a>
            <a href="faqs.php?bot_id=<?= $bot['id'] ?>" class="btn-outline-modern">FAQs</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($bots)): ?>
<div class="content-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-robot" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">No chatbots yet. Create your first bot to get started.</p>
        <button class="btn-primary-modern mt-3" data-bs-toggle="modal" data-bs-target="#createBotModal"><i class="bi bi-plus-lg"></i> Create Bot</button>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="createBotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)">
            <form method="POST" action="bot-create.php">
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0">
                    <h5 class="modal-title fw-semibold">Create Chatbot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bot Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Support Bot">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Welcome Message</label>
                        <textarea name="welcome_message" class="form-control" rows="3" placeholder="Hi! How can I help you today?">Hi! How can I help you today?</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theme Color</label>
                        <input type="color" name="theme_color" value="#2563eb" class="form-control form-control-color" style="width:60px;height:40px">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-select">
                            <option value="bottom-right">Bottom Right</option>
                            <option value="bottom-left">Bottom Left</option>
                            <option value="top-right">Top Right</option>
                            <option value="top-left">Top Left</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0">
                    <button type="button" class="btn-outline-modern" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-modern">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
