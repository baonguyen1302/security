<?php
// Scenario 1 - Login bypass (SQLi) demo + prepared-statement defense
// Uses ../config.php for DB connection ($conn)
require_once __DIR__ . '/../config.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$mode = $_POST['mode'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <title>Scenario1 — Login SQLi demo</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;max-width:820px;margin:18px;} form{margin-bottom:14px;} code{background:#f6f6f6;padding:2px 6px;border-radius:4px;}</style>
  </head>
  <body>
    <h1>Scenario 1 — Login bypass (SQL Injection)</h1>
    <p>Use the <strong>Vulnerable</strong> form to demonstrate how concatenated SQL can be bypassed. Use the <strong>Secure</strong> form to see the prepared-statement defense.</p>

    <form method="post">
      <input type="hidden" name="mode" value="vulnerable">
      <label>Username: <input name="username" ></label><br>
      <label>Password: <input type="password" name="password" ></label><br>
      <button type="submit">Login (Vulnerable)</button>
    </form>

    <form method="post">
      <input type="hidden" name="mode" value="secure">
      <label>Username: <input name="username" ></label><br>
      <label>Password: <input type="password" name="password" ></label><br>
      <button type="submit">Login (Secure - Prepared Statement)</button>
    </form>

    <h3>Example SQLi payload</h3>
    <p>Try entering this into the <strong>Username</strong> and <strong>Password</strong> fields of the Vulnerable form:</p>
    <pre>' OR '1'='1</pre>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($mode === 'vulnerable') {
    // VULNERABLE: raw concatenation WITHOUT escaping — intentionally insecure for demo
    // WARNING: this demonstrates how SQL injection works. Never use in production.
    $sql = "SELECT id, username FROM users WHERE username='" . $username . "' AND password='" . $password . "' LIMIT 1";
        echo "<h3>Vulnerable mode</h3>";
        echo "<p>Executed query: <code>" . h($sql) . "</code></p>";
        $res = mysqli_query($conn, $sql);
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            echo "<p style='color:green'>Login successful — user: " . h($row['username']) . "</p>";
        } else {
            echo "<p style='color:red'>Login failed</p>";
        }
    } elseif ($mode === 'secure') {
        // SECURE: use prepared statement (procedural mysqli)
        echo "<h3>Secure mode (prepared statement)</h3>";
        $stmt = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1');
        if (!$stmt) {
            echo "<p style='color:red'>Prepare failed: " . h(mysqli_error($conn)) . "</p>";
        } else {
            mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            echo "<p>Prepared statement executed with bound parameters.</p>";
            if ($res && mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                echo "<p style='color:green'>Login successful — user: " . h($row['username']) . "</p>";
            } else {
                echo "<p style='color:red'>Login failed</p>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

?>

  </body>
</html>