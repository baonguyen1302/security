<?php
// Scenario 1 - Login bypass (SQLi) demo + prepared-statement defense
// Uses ../config.php for DB connection ($conn)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../mode_store.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$mode = get_mode();
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <title>Scenario1 — Login SQLi demo</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="style.css">
  </head>
  <body class="page">
    <header class="site-header">
      <h1>Scenario 1 — Login bypass (SQL Injection)</h1>
      <p class="lead">Use the <strong>Vulnerable</strong> form to demonstrate how concatenated SQL can be bypassed. Use the <strong>Secure</strong> form to see the prepared-statement defense.</p>
    </header>

    <div class="card">
      <form method="post" id="loginForm">

        <div class="form-row">
          <label>Username
            <input name="username" type="text" autocomplete="username" />
          </label>
        </div>

        <div class="form-row">
          <label>Password
            <input type="password" name="password" autocomplete="current-password" />
          </label>
        </div>

        <div class="form-row">
          <button id="submitBtn" type="submit">Login</button>
        </div>

        <div class="hint" id="modeHint">Current site mode: <strong><?php echo h($mode); ?></strong>. Manage global mode at <a href="/app/mode.php">Mode Control</a>.</div>
      </form>
    </div>

    <div class="info-box">
      <h3 class="section-title">Example SQLi Payload</h3>
      <p>Try entering this into the <strong>Username</strong> and <strong>Password</strong> fields of the Vulnerable form:</p>
      <pre>' OR '1'='1</pre>
    </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($mode === 'vulnerable') {
    // VULNERABLE: raw concatenation WITHOUT escaping — intentionally insecure for demo
    // WARNING: this demonstrates how SQL injection works. Never use in production.
    $sql = "SELECT id, username FROM users WHERE username='" . $username . "' AND password='" . $password . "' LIMIT 1";
    
    echo "<div class='card'>";
    echo "<h3 class='card-title'>Vulnerable Mode</h3>";
    echo "<p><strong>Executed query:</strong></p>";
    echo "<pre>" . h($sql) . "</pre>";
    
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
      $row = mysqli_fetch_assoc($res);
      echo "<div class='result success'>";
      echo "<p>✓ Login successful — user: <strong>" . h($row['username']) . "</strong></p>";
      echo "</div>";
    } else {
      echo "<div class='result error'>";
      echo "<p>✗ Login failed</p>";
      echo "</div>";
    }
    echo "</div>";
    
  } elseif ($mode === 'secure') {
    // SECURE: use prepared statement (procedural mysqli)
    echo "<div class='card'>";
    echo "<h3 class='card-title'>Secure Mode (Prepared Statement)</h3>";
    
    $stmt = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1');
    if (!$stmt) {
      echo "<div class='result error'>";
      echo "<p><strong>Error:</strong> " . h(mysqli_error($conn)) . "</p>";
      echo "</div>";
    } else {
      mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      echo "<p><strong>Prepared statement executed with bound parameters.</strong></p>";
      
      if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        echo "<div class='result success'>";
        echo "<p>✓ Login successful — user: <strong>" . h($row['username']) . "</strong></p>";
        echo "</div>";
      } else {
        echo "<div class='result error'>";
        echo "<p>✗ Login failed</p>";
        echo "</div>";
      }
      mysqli_stmt_close($stmt);
    }
    echo "</div>";
  }
}

?>

  </body>
</html>