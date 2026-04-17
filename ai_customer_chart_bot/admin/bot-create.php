<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/bots.php');
    exit;
}

$db = Database::getInstance();
$db->insert('chatbot_bots', [
    'business_id' => $_SESSION['business_id'],
    'name' => trim($_POST['name'] ?? 'New Bot'),
    'welcome_message' => trim($_POST['welcome_message'] ?? 'Hi! How can I help you today?'),
    'theme_color' => $_POST['theme_color'] ?? '#2563eb',
    'position' => $_POST['position'] ?? 'bottom-right',
    'is_active' => 1
]);

header('Location: ' . APP_URL . '/admin/bots.php');
exit;
