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

    <style>
      .card{background:#fff;border:1px solid #e6e6e6;padding:18px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.04);}
      .form-row{margin:8px 0}
      label{display:block;margin-bottom:6px}
      input[type="text"], input[type="password"]{width:100%;padding:8px;border:1px solid #ccc;border-radius:6px}
      .mode-toggle{display:flex;gap:8px;margin:6px 0 12px 0}
      .mode-toggle label{display:inline-flex;align-items:center;gap:6px;background:#f4f6f8;padding:6px 10px;border-radius:6px;cursor:pointer}
      .mode-toggle input{margin:0}
      #submitBtn{background:#0078d4;color:#fff;border:none;padding:10px 14px;border-radius:6px;cursor:pointer}
      #submitBtn.secure{background:#2d7a46}
      .hint{font-size:13px;color:#555;margin-top:8px}
    </style>

    <div class="card">
      <form method="post" id="loginForm">
        <div class="mode-toggle">
          <label><input type="radio" name="mode" value="vulnerable" checked> Vulnerable</label>
          <label><input type="radio" name="mode" value="secure"> Secure (Prepared)</label>
        </div>

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
          <button id="submitBtn" type="submit">Login (Vulnerable)</button>
        </div>

        <div class="hint" id="modeHint">Currently showing the <strong>Vulnerable</strong> login. Use this to demo SQL injection (do not use in production).</div>
      </form>
    </div>

    <script>
      (function(){
        const radios = document.querySelectorAll('input[name="mode"]');
        const btn = document.getElementById('submitBtn');
        const hint = document.getElementById('modeHint');
        function update(){
          const mode = document.querySelector('input[name="mode"]:checked').value;
          if(mode === 'vulnerable'){
            btn.textContent = 'Login (Vulnerable)';
            btn.classList.remove('secure');
            hint.innerHTML = 'Currently showing the <strong>Vulnerable</strong> login. Try payload <code>\' OR \'1\'=\'1</code> in Username.';
          } else {
            btn.textContent = 'Login (Secure - Prepared Statement)';
            btn.classList.add('secure');
            hint.innerHTML = 'Secure mode uses prepared statements. This prevents SQL injection.';
          }
        }
        radios.forEach(r=>r.addEventListener('change', update));
        update();
      })();
    </script>

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