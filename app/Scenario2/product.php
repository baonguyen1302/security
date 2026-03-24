<?php
require_once __DIR__ . '/../config.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$mode = $_GET['mode'] ?? 'vulnerable';
$id_raw = $_GET['product_id'] ?? '';

?>
  </body>
</html><!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <title>Scenario2 — Union-based SQLi demo</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="style.css">
  </head>
  <body class="page">
    <header class="site-header">
      <h1>Scenario 2 — Union-based SQL Injection</h1>
      <p class="lead">Demonstrates how UNION-based SQL injection can extract data from the database. Use the <strong>Vulnerable</strong> mode to test payloads, and <strong>Secure</strong> mode to see the defense.</p>
    </header>

    <div class="card">
      <div class="mode-badge <?php echo $mode; ?>"><?php echo strtoupper($mode); ?> MODE</div>
      
      <form method="get" id="searchForm">
        <div class="form-row">
          <label for="product_id">Product ID
            <input id="product_id" name="product_id" type="text" placeholder="Enter product ID" value="<?php echo h($id_raw); ?>" />
          </label>
        </div>

        <div class="form-row">
          <div class="mode-toggle">
            <label><input type="radio" name="mode" value="vulnerable" <?php echo $mode === 'vulnerable' ? 'checked' : ''; ?>> Vulnerable</label>
            <label><input type="radio" name="mode" value="secure" <?php echo $mode === 'secure' ? 'checked' : ''; ?>> Secure (Prepared)</label>
          </div>
        </div>

        <div class="form-row">
          <button id="searchBtn" type="submit">🔍 Search Product</button>
        </div>

        <div class="hint" id="modeHint">
          Currently in <strong>Vulnerable</strong> mode. Try payload: <code>-1 UNION SELECT 1,username,password FROM users--</code>
        </div>
      </form>
    </div>

    <div class="info-box">
      <h3>📚 Union-based SQLi Explanation</h3>
      <p>This attack uses the <code>UNION</code> SQL operator to combine results from different tables. An attacker can extract sensitive data by injecting a UNION query.</p>
      <p><strong>Example payload:</strong></p>
      <pre>-1 UNION SELECT 1,username,password FROM users--</pre>
    </div>

    <script>
      (function(){
        const radios = document.querySelectorAll('input[name="mode"]');
        const hint = document.getElementById('modeHint');
        function update(){
          const mode = document.querySelector('input[name="mode"]:checked').value;
          if(mode === 'vulnerable'){
            hint.innerHTML = 'Currently in <strong>Vulnerable</strong> mode. Try payload: <code>-1 UNION SELECT 1,username,password FROM users--</code>';
          } else {
            hint.innerHTML = 'Secure mode uses prepared statements with integer validation. SQL injection is prevented.';
          }
        }
        radios.forEach(r=>r.addEventListener('change', update));
        update();
      })();
    </script>

<?php

if ($mode === 'vulnerable') {
    // VULNERABLE: directly inject GET param into SQL (intentionally insecure)
    $sql = "SELECT id, name, description FROM products WHERE id = $id_raw";
    
    echo "<div class='card'>";
    echo "<h3 class='card-title'>Query Executed</h3>";
    echo "<pre>" . h($sql) . "</pre>";
    
    $res = mysqli_query($conn, $sql);

    echo "<h3 class='section-title'>Product Results</h3>";
    if ($res && mysqli_num_rows($res) > 0) {
      echo "<div class='product-grid'>";
      while ($row = mysqli_fetch_assoc($res)) {
        echo "<div class='product-card'>";
        echo "<div class='product-id'>ID: " . h($row['id']) . "</div>";
        echo "<div class='product-name'>" . h($row['name']) . "</div>";
        echo "<div class='product-desc'>" . h($row['description']) . "</div>";
        echo "</div>";
      }
      echo "</div>";
      echo "<div class='result success'>";
      echo "<p>✓ Query executed successfully</p>";
      echo "</div>";
    } else {
      echo "<div class='result warning'>";
      echo "<p>⚠ No results found</p>";
      if (!$res) {
        echo "<p><strong>Error:</strong> " . h(mysqli_error($conn)) . "</p>";
      }
      echo "</div>";
    }
    echo "</div>";

} else {
    // SECURE: validate and bind parameter as integer (or use prepared statement)
    echo "<div class='card'>";
    echo "<h3 class='card-title'>Secure Mode (Validation & Prepared Statement)</h3>";
    
    // ✅ VALIDATION: only allow integer
    if (!filter_var($id_raw, FILTER_VALIDATE_INT)) {
      echo "<div class='result error'>";
      echo "<p>✗ Invalid input: product_id must be an integer.</p>";
      echo "</div>";
      echo "</div>";
    } else {
      $id = intval($id_raw);
      echo "<p><strong>Parameter validated:</strong> <code>product_id = " . h($id) . "</code></p>";
      
      $stmt = mysqli_prepare($conn, 'SELECT id, name, description FROM products WHERE id = ?');
      if (!$stmt) {
        echo "<div class='result error'>";
        echo "<p>✗ Prepare failed: " . h(mysqli_error($conn)) . "</p>";
        echo "</div>";
      } else {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        echo "<h3 class='section-title'>Product Results</h3>";
        if ($res && mysqli_num_rows($res) > 0) {
          echo "<div class='product-grid'>";
          while ($row = mysqli_fetch_assoc($res)) {
            echo "<div class='product-card'>";
            echo "<div class='product-id'>ID: " . h($row['id']) . "</div>";
            echo "<div class='product-name'>" . h($row['name']) . "</div>";
            echo "<div class='product-desc'>" . h($row['description']) . "</div>";
            echo "</div>";
          }
          echo "</div>";
          echo "<div class='result success'>";
          echo "<p>✓ Query executed with prepared statement</p>";
          echo "</div>";
        } else {
          echo "<div class='result warning'>";
          echo "<p>⚠ No results found</p>";
          echo "</div>";
        }
        mysqli_stmt_close($stmt);
      }
      echo "</div>";
    }
}

?>