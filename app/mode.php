<?php
// Simple mode control page — sets a cookie so scenarios can read global mode
// Usage: visit this page and click the buttons to switch between 'vulnerable' and 'secure'

require_once __DIR__ . '/mode_store.php';

$set = $_GET['set'] ?? null;
$back = $_GET['back'] ?? '/';
// Access control: only allow requests coming from the server IP (or loopback)
// Remote IP: prefer X-Forwarded-For (first entry) then X-Real-IP then REMOTE_ADDR
$remote = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (strpos($remote, ',') !== false) { $remote = trim(explode(',', $remote)[0]); }

// Server IP (may be container IP); include loopback and IPv6 loopback
$server_ip = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
$allowed_ips = [$server_ip, '127.0.0.1', '::1'];

// Try detect Docker gateway (default route) inside container and allow it
function detect_default_gateway_ip() {
  $route = @file_get_contents('/proc/net/route');
  if ($route === false) return null;
  $lines = explode("\n", $route);
  foreach ($lines as $line) {
    $cols = preg_split('/\s+/', trim($line));
    if (count($cols) >= 3 && $cols[1] === '00000000') {
      $gwHex = $cols[2];
      $gw = long2ip(hexdec(substr($gwHex,6,2) . substr($gwHex,4,2) . substr($gwHex,2,2) . substr($gwHex,0,2)));
      return $gw;
    }
  }
  return null;
}

$gw = detect_default_gateway_ip();
if ($gw) { $allowed_ips[] = $gw; }

// Also allow additional IPs from app/.mode_allow if present (one IP per line)
$allow_file = __DIR__ . '/.mode_allow';
if (is_readable($allow_file)) {
  $lines = file($allow_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $ln) {
    $ip = trim($ln);
    if ($ip !== '') $allowed_ips[] = $ip;
  }
}

// Normalize and check
$allowed_ips = array_values(array_unique($allowed_ips));
if (!in_array($remote, $allowed_ips, true)) {
  http_response_code(403);
  echo "<h1>403 Forbidden</h1><p>Access to mode control is restricted. Your IP (" . htmlspecialchars($remote) . ") is not allowed.</p>";
  exit;
}

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
