<?php
/**
 * AI Customer Support Chatbot Builder - Configuration
 */

// Prevent direct access
defined('ABSPATH') || define('ABSPATH', true);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_chatbot_builder');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application
define('APP_URL', 'http://localhost/ai_customer_chart_bot');
define('APP_PATH', dirname(__DIR__));

// OpenAI API (set via environment or .env in production)
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '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');

// File upload
define('UPLOAD_PATH', APP_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['txt', 'pdf', 'doc', 'docx', 'md', 'csv']);

// Session
define('SESSION_LIFETIME', 86400); // 24 hours

// Timezone
date_default_timezone_set('UTC');
