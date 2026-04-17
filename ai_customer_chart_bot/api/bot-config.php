<?php
/**
 * Returns bot config for the embed widget (theme, position, welcome message)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$botId = (int)($_GET['bot_id'] ?? 0);
if (!$botId) {
    echo json_encode(['error' => 'bot_id required']);
    exit;
}

$db = Database::getInstance();
$bot = $db->fetchOne("SELECT theme_color, position, welcome_message FROM chatbot_bots WHERE id = ? AND is_active = 1", [$botId]);
if (!$bot) {
    echo json_encode(['error' => 'Bot not found']);
    exit;
}

echo json_encode([
    'theme_color' => $bot['theme_color'],
    'position' => $bot['position'],
    'welcome_message' => $bot['welcome_message']
]);
