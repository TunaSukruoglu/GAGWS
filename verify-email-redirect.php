<?php
// Redirect old verify-email.php requests to activate.php
$token = $_GET['token'] ?? '';
if (!empty($token)) {
    header("Location: activate.php?token=" . urlencode($token), true, 301);
    exit;
} else {
    header("Location: index.php", true, 301);
    exit;
}
?>
