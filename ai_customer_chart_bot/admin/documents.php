<?php
$pageTitle = 'Documents';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/DocumentParser.php';

$db = Database::getInstance();
$businessId = $_SESSION['business_id'];
$botId = (int)($_GET['bot_id'] ?? 0);

$bots = $db->fetchAll("SELECT id, name FROM chatbot_bots WHERE business_id = ?", [$businessId]);
if ($botId && !in_array($botId, array_column($bots, 'id'))) $botId = 0;
if (!$botId && !empty($bots)) $botId = (int)$bots[0]['id'];

$documents = [];
if ($botId) {
    $documents = $db->fetchAll("SELECT * FROM chatbot_documents WHERE bot_id = ? ORDER BY created_at DESC", [$botId]);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $uploadBotId = (int)($_POST['bot_id'] ?? 0);
    if (!$uploadBotId || !in_array($uploadBotId, array_column($bots, 'id'))) {
        $error = 'Invalid bot.';
    } elseif (!isset($_FILES['document']['error']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed.';
    } else {
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            $error = 'File type not allowed. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS);
        } elseif ($_FILES['document']['size'] > MAX_FILE_SIZE) {
            $error = 'File too large (max 5MB).';
        } else {
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['document']['name']);
            $filePath = UPLOAD_PATH . '/' . $filename;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $filePath)) {
                $contentText = DocumentParser::extractText($filePath, $_FILES['document']['type']);
                $db->insert('chatbot_documents', [
                    'bot_id' => $uploadBotId,
                    'filename' => $_FILES['document']['name'],
                    'file_path' => $filename,
                    'file_type' => $ext,
                    'content_text' => $contentText,
                    'processed_at' => date('Y-m-d H:i:s')
                ]);
                $success = 'Document uploaded and processed.';
                $botId = $uploadBotId;
                $documents = $db->fetchAll("SELECT * FROM chatbot_documents WHERE bot_id = ? ORDER BY created_at DESC", [$botId]);
            } else {
                $error = 'Could not save file.';
            }
        }
    }
}

if (isset($_GET['delete']) && $botId) {
    $docId = (int)$_GET['delete'];
    $doc = $db->fetchOne("SELECT d.* FROM chatbot_documents d JOIN chatbot_bots b ON d.bot_id = b.id WHERE d.id = ? AND b.business_id = ?", [$docId, $businessId]);
    if ($doc) {
        $fullPath = UPLOAD_PATH . '/' . $doc['file_path'];
        if (file_exists($fullPath)) @unlink($fullPath);
        $db->query("DELETE FROM chatbot_documents WHERE id = ?", [$docId]);
        $success = 'Document deleted.';
        $documents = $db->fetchAll("SELECT * FROM chatbot_documents WHERE bot_id = ? ORDER BY created_at DESC", [$botId]);
    }
}
?>
<div class="page-toolbar">
    <select class="select-modern" onchange="location.href='?bot_id='+this.value">
        <?php foreach ($bots as $b): ?>
        <option value="<?= $b['id'] ?>" <?= $botId == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn-primary-modern" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> Upload</button>
</div>

<?php if ($error): ?><div class="alert-modern danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert-modern success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<?php if (empty($bots)): ?>
<div class="content-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-file-earmark-text" style="font-size:3rem;color:#94a3b8"></i>
        <p class="mt-3 text-muted mb-0">Create a chatbot first, then upload documents.</p>
    </div>
</div>
<?php else: ?>
<div class="content-card">
    <div class="card-body p-0">
        <table class="table-modern">
            <thead><tr><th>Filename</th><th>Type</th><th>Processed</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($documents as $d): ?>
                <tr>
                    <td><i class="bi bi-file-earmark me-2"></i><?= htmlspecialchars($d['filename']) ?></td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($d['file_type']) ?></span></td>
                    <td><?= $d['processed_at'] ? date('M j, Y', strtotime($d['processed_at'])) : '-' ?></td>
                    <td><a href="?bot_id=<?= $botId ?>&delete=<?= $d['id'] ?>" class="text-danger text-decoration-none" onclick="return confirm('Delete this document?')">Delete</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($documents)): ?>
                <tr><td colspan="4" class="text-muted py-4 text-center">No documents. Upload FAQs or company docs to train your bot.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="bot_id" value="<?= $botId ?>">
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0"><h5 class="modal-title fw-semibold">Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Allowed: txt, pdf, doc, docx, md, csv (max 5MB)</p>
                    <input type="file" name="document" class="form-control" accept=".txt,.pdf,.doc,.docx,.md,.csv" required>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0"><button type="button" class="btn-outline-modern" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn-primary-modern">Upload</button></div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
