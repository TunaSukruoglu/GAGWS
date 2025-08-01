<?php
// 🚀 SMART DASHBOARD ROUTER - ULTRA SPEED OPTIMIZATION
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

// Determine optimal dashboard based on request
$type = $_GET['type'] ?? 'auto';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Dashboard selection logic
if ($type === 'ultra' || strpos($user_agent, 'Mobile') !== false) {
    // Ultra minimal for mobile or explicit request
    header("Location: dashboard-ultra.php");
    exit;
} elseif ($type === 'lightning' || strpos($user_agent, 'Chrome') !== false) {
    // Lightning fast for Chrome or modern browsers
    header("Location: dashboard-lightning.php");
    exit;
} elseif ($type === 'minimal') {
    // Minimal version
    header("Location: dashboard-minimal.php");
    exit;
} elseif ($type === 'full') {
    // Full featured dashboard
    header("Location: dashboard-admin.php");
    exit;
} else {
    // Auto-detect best option (default to lightning)
    header("Location: dashboard-lightning.php");
    exit;
}
?>
