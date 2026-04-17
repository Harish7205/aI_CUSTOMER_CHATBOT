<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
$business = getCurrentBusiness();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - AI Chatbot Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="<?= APP_URL ?>/admin/dashboard.php" class="brand">
                <i class="bi bi-robot"></i>
                AI Chatbot Builder
            </a>
            <nav>
                <a href="<?= APP_URL ?>/admin/dashboard.php" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a href="<?= APP_URL ?>/admin/bots.php" class="nav-link <?= $currentPage === 'bots' ? 'active' : '' ?>">
                    <i class="bi bi-robot"></i> Chatbots
                </a>
                <a href="<?= APP_URL ?>/admin/documents.php" class="nav-link <?= $currentPage === 'documents' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i> Documents
                </a>
                <a href="<?= APP_URL ?>/admin/faqs.php" class="nav-link <?= $currentPage === 'faqs' ? 'active' : '' ?>">
                    <i class="bi bi-question-circle"></i> FAQs
                </a>
                <a href="<?= APP_URL ?>/admin/chats.php" class="nav-link <?= in_array($currentPage, ['chats','chat-view']) ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i> Chat Logs
                </a>
                <a href="<?= APP_URL ?>/admin/analytics.php" class="nav-link <?= $currentPage === 'analytics' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Analytics
                </a>
                <a href="<?= APP_URL ?>/admin/embed.php" class="nav-link <?= $currentPage === 'embed' ? 'active' : '' ?>">
                    <i class="bi bi-code-slash"></i> Embed Code
                </a>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-header">
                <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                <div class="admin-user">
                    <span class="name"><?= htmlspecialchars($business['name']) ?></span>
                    <a href="<?= APP_URL ?>/logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
