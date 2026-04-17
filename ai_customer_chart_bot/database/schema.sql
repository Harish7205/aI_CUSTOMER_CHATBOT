-- AI Customer Support Chatbot Builder - Database Schema
-- Run this in MySQL to create all required tables

CREATE DATABASE IF NOT EXISTS ai_chatbot_builder;
USE ai_chatbot_builder;

-- Businesses (multi-tenant support)
CREATE TABLE businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    website_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'suspended', 'pending') DEFAULT 'active'
);

-- Chatbot bots (each business can have multiple bots)
CREATE TABLE chatbot_bots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    welcome_message TEXT,
    theme_color VARCHAR(7) DEFAULT '#2563eb',
    position ENUM('bottom-right', 'bottom-left', 'top-right', 'top-left') DEFAULT 'bottom-right',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Uploaded documents for knowledge base
CREATE TABLE chatbot_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    content_text LONGTEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bot_id) REFERENCES chatbot_bots(id) ON DELETE CASCADE
);

-- FAQ entries
CREATE TABLE chatbot_faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_id INT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bot_id) REFERENCES chatbot_bots(id) ON DELETE CASCADE
);

-- Chat sessions
CREATE TABLE chatbot_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_id INT NOT NULL,
    visitor_id VARCHAR(100),
    visitor_name VARCHAR(255),
    visitor_email VARCHAR(255),
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (bot_id) REFERENCES chatbot_bots(id) ON DELETE CASCADE
);

-- Chat messages
CREATE TABLE chatbot_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chatbot_chats(id) ON DELETE CASCADE
);

-- Analytics (aggregated stats)
CREATE TABLE chatbot_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_id INT NOT NULL,
    date DATE NOT NULL,
    total_chats INT DEFAULT 0,
    total_messages INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    avg_response_time DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bot_date (bot_id, date),
    FOREIGN KEY (bot_id) REFERENCES chatbot_bots(id) ON DELETE CASCADE
);

-- Embed codes (for tracking which sites use the widget)
CREATE TABLE chatbot_embed_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_id INT NOT NULL,
    domain VARCHAR(255),
    embed_code LONGTEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bot_id) REFERENCES chatbot_bots(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_business_status ON businesses(status);
CREATE INDEX idx_bot_business ON chatbot_bots(business_id);
CREATE INDEX idx_chat_bot ON chatbot_chats(bot_id);
CREATE INDEX idx_chat_started ON chatbot_chats(started_at);
CREATE INDEX idx_analytics_bot_date ON chatbot_analytics(bot_id, date);
