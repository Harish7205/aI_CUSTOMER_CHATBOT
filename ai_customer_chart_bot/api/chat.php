<?php
/**
 * Chat API - handles messages from the embed widget
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$botId = (int)($input['bot_id'] ?? $_POST['bot_id'] ?? 0);
$message = trim($input['message'] ?? $_POST['message'] ?? '');
$chatId = (int)($input['chat_id'] ?? $_POST['chat_id'] ?? 0);
$visitorId = $input['visitor_id'] ?? $_POST['visitor_id'] ?? '';
$visitorName = trim($input['visitor_name'] ?? $_POST['visitor_name'] ?? '');
$visitorEmail = trim($input['visitor_email'] ?? $_POST['visitor_email'] ?? '');

if (!$botId || !$message) {
    echo json_encode(['error' => 'bot_id and message required']);
    exit;
}

$db = Database::getInstance();
$bot = $db->fetchOne("SELECT * FROM chatbot_bots WHERE id = ? AND is_active = 1", [$botId]);
if (!$bot) {
    echo json_encode(['error' => 'Bot not found']);
    exit;
}

// Create or get chat session
if (!$chatId) {
    $db->insert('chatbot_chats', [
        'bot_id' => $botId,
        'visitor_id' => $visitorId,
        'visitor_name' => $visitorName ?: null,
        'visitor_email' => $visitorEmail ?: null
    ]);
    $chatId = (int)$db->getConnection()->lastInsertId();
} else {
    $chat = $db->fetchOne("SELECT id FROM chatbot_chats WHERE id = ? AND bot_id = ?", [$chatId, $botId]);
    if (!$chat) {
        echo json_encode(['error' => 'Invalid chat']);
        exit;
    }
}

// Save user message
$db->insert('chatbot_messages', ['chat_id' => $chatId, 'role' => 'user', 'content' => $message]);

// Build context from documents and FAQs
$docs = $db->fetchAll("SELECT content_text FROM chatbot_documents WHERE bot_id = ? AND content_text IS NOT NULL AND content_text != ''", [$botId]);
$faqs = $db->fetchAll("SELECT question, answer FROM chatbot_faqs WHERE bot_id = ?", [$botId]);

$context = "You are a helpful customer support assistant. Use the following knowledge base to answer questions.\n\n";
$context .= "IMPORTANT: Do NOT repeat or echo the welcome message. Answer the user's actual question.\n";
$context .= "If the answer is NOT in the knowledge base, politely say you don't have that information and suggest they contact support (email or phone). Do not make up answers.\n\n";

if (!empty($docs)) {
    $context .= "## Company Documents:\n";
    foreach ($docs as $d) {
        $context .= substr($d['content_text'], 0, 8000) . "\n\n";
    }
}
if (!empty($faqs)) {
    $context .= "## FAQs:\n";
    foreach ($faqs as $f) {
        $context .= "Q: " . $f['question'] . "\nA: " . $f['answer'] . "\n\n";
    }
}

// Get recent conversation for context
$history = $db->fetchAll("SELECT role, content FROM chatbot_messages WHERE chat_id = ? ORDER BY id DESC LIMIT 10", [$chatId]);
$history = array_reverse($history);

$messages = [
    ['role' => 'system', 'content' => $context],
];
foreach ($history as $h) {
    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
}

// Call OpenAI
$apiKey = OPENAI_API_KEY;
$responseText = 'Sorry, I could not process your request. Please ensure the OpenAI API key is configured.';

if ($apiKey && $apiKey !== 'your-openai-api-key-here') {
    $payload = [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 500,
        'temperature' => 0.7
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $resp) {
        $data = json_decode($resp, true);
        $responseText = $data['choices'][0]['message']['content'] ?? $responseText;
    } else {
        $responseText = 'I encountered an error. Please try again.';
    }
}

// Save assistant response
$db->insert('chatbot_messages', ['chat_id' => $chatId, 'role' => 'assistant', 'content' => $responseText]);

// Update analytics (simple daily aggregate)
$today = date('Y-m-d');
$db->query("INSERT INTO chatbot_analytics (bot_id, date, total_chats, total_messages, unique_visitors) 
    VALUES (?, ?, 1, 1, 1) 
    ON DUPLICATE KEY UPDATE total_messages = total_messages + 1", [$botId, $today]);

echo json_encode([
    'chat_id' => $chatId,
    'message' => $responseText,
    'visitor_id' => $visitorId
]);
