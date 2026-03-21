<?php
// Scenario 3 - Blind SQLi demo (time-based) and WAF demonstration
// Vulnerable endpoint: profile.php?user_id=1
// WARNING: intentionally vulnerable for learning. Do NOT expose publicly.

require_once __DIR__ . '/../config.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$id_raw = $_GET['user_id'] ?? '';
$mode = $_GET['mode'] ?? 'vulnerable';

echo "<h1>Scenario 3 — Blind SQLi (time-based) demo</h1>";
echo "<p>Mode: <strong>" . h($mode) . "</strong></p>";

if ($mode === 'vulnerable') {
    // VULNERABLE: directly use GET param in SQL — allows time-based blind SQLi
    $sql = "SELECT id, username FROM users WHERE id = " . $id_raw;
    echo "<p>Executed query: <code>" . h($sql) . "</code></p>";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        echo "<h2>Profile Result</h2>";
        while ($row = mysqli_fetch_assoc($res)) {
            echo "ID: " . h($row['id']) . "<br>";
            echo "Username: " . h($row['username']) . "<br><hr>";
        }
    } else {
        echo "<p style='color:red'>Query error: " . h(mysqli_error($conn)) . "</p>";
    }
} else {
    // SECURE: prepared statement + integer cast to prevent injection
    $id = intval($id_raw);
    $stmt = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE id = ?');
    if (!$stmt) {
        echo "<p style='color:red'>Prepare failed: " . h(mysqli_error($conn)) . "</p>";
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo "<h2>Profile Result</h2>";
        while ($row = mysqli_fetch_assoc($res)) {
            echo "ID: " . h($row['id']) . "<br>";
            echo "Username: " . h($row['username']) . "<br><hr>";
        }
        mysqli_stmt_close($stmt);
    }
}

?>
