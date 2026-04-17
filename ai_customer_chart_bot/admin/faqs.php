<?php
$pageTitle = 'FAQs';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];
$botId = (int)($_GET['bot_id'] ?? 0);

$bots = $db->fetchAll("SELECT id, name FROM chatbot_bots WHERE business_id = ?", [$businessId]);
if ($botId && !in_array($botId, array_column($bots, 'id'))) $botId = 0;
if (!$botId && !empty($bots)) $botId = (int)$bots[0]['id'];

$faqs = [];
if ($botId) {
    $faqs = $db->fetchAll("SELECT * FROM chatbot_faqs WHERE bot_id = ? ORDER BY sort_order, id", [$botId]);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $faqBotId = (int)($_POST['bot_id'] ?? 0);
    if (!$faqBotId || !in_array($faqBotId, array_column($bots, 'id'))) {
        $error = 'Invalid bot.';
    } elseif ($action === 'add') {
        $q = trim($_POST['question'] ?? '');
        $a = trim($_POST['answer'] ?? '');
        if ($q && $a) {
            $db->insert('chatbot_faqs', ['bot_id' => $faqBotId, 'question' => $q, 'answer' => $a]);
            $success = 'FAQ added.';
            $botId = $faqBotId;
            $faqs = $db->fetchAll("SELECT * FROM chatbot_faqs WHERE bot_id = ? ORDER BY sort_order, id", [$botId]);
        } else {
            $error = 'Question and answer required.';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $faq = $db->fetchOne("SELECT id FROM chatbot_faqs f JOIN chatbot_bots b ON f.bot_id = b.id WHERE f.id = ? AND b.business_id = ?", [$id, $businessId]);
        if ($faq) {
            $db->update('chatbot_faqs', [
                'question' => trim($_POST['question'] ?? ''),
                'answer' => trim($_POST['answer'] ?? '')
            ], 'id = ?', [$id]);
            $success = 'FAQ updated.';
            $faqs = $db->fetchAll("SELECT * FROM chatbot_faqs WHERE bot_id = ? ORDER BY sort_order, id", [$botId]);
        }
    }
}

if (isset($_GET['delete']) && $botId) {
    $id = (int)$_GET['delete'];
    $faq = $db->fetchOne("SELECT id FROM chatbot_faqs f JOIN chatbot_bots b ON f.bot_id = b.id WHERE f.id = ? AND b.business_id = ?", [$id, $businessId]);
    if ($faq) {
        $db->query("DELETE FROM chatbot_faqs WHERE id = ?", [$id]);
        $success = 'FAQ deleted.';
        $faqs = $db->fetchAll("SELECT * FROM chatbot_faqs WHERE bot_id = ? ORDER BY sort_order, id", [$botId]);
    }
}
?>
<div class="page-toolbar">
    <select class="select-modern" onchange="location.href='?bot_id='+this.value">
        <?php foreach ($bots as $b): ?><option value="<?= $b['id'] ?>" <?= $botId == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
    </select>
    <button class="btn-primary-modern" data-bs-toggle="modal" data-bs-target="#addFaqModal"><i class="bi bi-plus-lg"></i> Add FAQ</button>
</div>

<?php if ($error): ?><div class="alert-modern danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert-modern success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<?php if (empty($bots)): ?>
<div class="content-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-question-circle" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">Create a chatbot first.</p>
    </div>
</div>
<?php else: ?>
<div class="content-card">
    <div class="card-body p-0">
        <?php foreach ($faqs as $f): ?>
        <div class="faq-item" style="padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:flex-start;gap:1rem">
            <div class="flex-grow-1">
                <strong style="display:block;margin-bottom:0.25rem"><?= htmlspecialchars($f['question']) ?></strong>
                <p class="mb-0 small text-muted"><?= htmlspecialchars(substr($f['answer'], 0, 120)) ?><?= strlen($f['answer']) > 120 ? '...' : '' ?></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn-outline-modern btn-sm" data-bs-toggle="modal" data-bs-target="#editFaqModal" onclick="editFaq(<?= htmlspecialchars(json_encode($f)) ?>)">Edit</button>
                <a href="?bot_id=<?= $botId ?>&delete=<?= $f['id'] ?>" class="btn-outline-modern btn-sm text-danger" style="border-color:#fecaca" onclick="return confirm('Delete this FAQ?')">Delete</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($faqs)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-question-circle" style="font-size:2rem"></i>
            <p class="mt-2 mb-0">No FAQs. Add questions and answers to help your bot respond.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="addFaqModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="bot_id" value="<?= $botId ?>">
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0"><h5 class="modal-title fw-semibold">Add FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Question</label><input type="text" name="question" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Answer</label><textarea name="answer" class="form-control" rows="3" required></textarea></div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0"><button type="button" class="btn-outline-modern" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-modern">Add</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editFaqModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="bot_id" value="<?= $botId ?>">
                <input type="hidden" name="id" id="editId">
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0"><h5 class="modal-title fw-semibold">Edit FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Question</label><input type="text" name="question" id="editQuestion" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Answer</label><textarea name="answer" id="editAnswer" class="form-control" rows="3" required></textarea></div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0"><button type="button" class="btn-outline-modern" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-modern">Save</button></div>
            </form>
        </div>
    </div>
</div>
<script>
function editFaq(f) {
    document.getElementById('editId').value = f.id;
    document.getElementById('editQuestion').value = f.question;
    document.getElementById('editAnswer').value = f.answer;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
