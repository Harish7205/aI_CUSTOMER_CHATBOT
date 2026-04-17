<?php
/**
 * Authentication helper
 */

require_once __DIR__ . '/../config/database.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['business_id']);
}

function getCurrentBusiness(): ?array {
    if (!isLoggedIn()) return null;
    $db = Database::getInstance();
    return $db->fetchOne("SELECT id, name, email, website_url FROM businesses WHERE id = ? AND status = 'active'", [$_SESSION['business_id']]);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function logout(): void {
    startSession();
    session_destroy();
}
