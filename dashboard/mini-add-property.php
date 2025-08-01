<?php
session_start();
$_SESSION['user_id'] = 13;
?>
<!DOCTYPE html>
<html>
<head><title>Mini Add Property</title></head>
<body>
<h1>🎯 Mini Add Property Test</h1>
<p>Session: <?php echo $_SESSION['user_id']; ?></p>
<form method="post">
    <input type="text" name="title" placeholder="İlan Başlığı">
    <button type="submit">Test Submit</button>
</form>
</body>
</html>
