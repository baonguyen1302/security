<?php
// Simple mode control page — sets a cookie so scenarios can read global mode
// Usage: visit this page and click the buttons to switch between 'vulnerable' and 'secure'

require_once __DIR__ . '/mode_store.php';

$set = $_GET['set'] ?? null;
$back = $_GET['back'] ?? '/';

// Only accept allowed values and set server-side mode
$allowed = ['vulnerable', 'secure'];
if ($set && in_array($set, $allowed, true)) {
  set_mode($set);
  // Redirect to the fixed control page on localhost so caller always lands there
  header('Location: http://localhost:8080/mode.php');
  exit;
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$current = get_mode();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mode Control</title>
    <style>
      body{font-family:Arial,Helvetica,sans-serif;padding:18px;background:#f7fafc}
      .card{max-width:720px;margin:auto;background:#fff;padding:18px;border-radius:8px;border:1px solid #e6eef5}
      .btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none;color:#fff;margin-right:8px}
      .btn.vul{background:#c0392b}
      .btn.sec{background:#2d7a46}
      .note{margin-top:12px;color:#333}
    </style>
  </head>
  <body>
    <div class="card">
      <h1>Mode Control</h1>
      <p>Current global mode: <strong><?php echo h($current); ?></strong></p>

      <p>
        <a class="btn vul" href="?set=vulnerable&back=<?php echo urlencode($_SERVER['HTTP_REFERER'] ?? '/mode.php'); ?>">Set Vulnerable</a>
        <a class="btn sec" href="?set=secure&back=<?php echo urlencode($_SERVER['HTTP_REFERER'] ?? '/mode.php'); ?>">Set Secure</a>
      </p>

      <p class="note">After switching the mode, open the scenario pages (e.g. <a href="/Scenario1/login.php">Scenario 1</a>) — they read the global mode from a cookie.</p>
    </div>
  </body>
</html>
