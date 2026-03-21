<?php
require_once __DIR__ . '/../config.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$mode = $_GET['mode'] ?? 'vulnerable';
$id_raw = $_GET['product_id'] ?? '';

echo "<h1>Scenario 2 — Product (Union-based SQLi demo)</h1>";
echo "<p>Mode: <strong>" . h($mode) . "</strong></p>";
echo "<p>Example attack payload (GET): <code>?product_id=-1 UNION SELECT 1,username,password FROM users--</code></p>";

if ($mode === 'vulnerable') {
    // VULNERABLE: directly inject GET param into SQL (intentionally insecure)
    $sql = "SELECT id, name, description FROM products WHERE id = $id_raw";
    echo "<p>Executed query: <code>" . h($sql) . "</code></p>";
    $res = mysqli_query($conn, $sql);

    echo "<h2>Product Result</h2>";
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            echo "ID: " . h($row['id']) . "<br>";
            echo "Name: " . h($row['name']) . "<br>";
            echo "Description: " . h($row['description']) . "<br><hr>";
        }
    } else {
        echo "<p style='color:red'>Query error: " . h(mysqli_error($conn)) . "</p>";
    }

} else {
    // SECURE: validate and bind parameter as integer (or use prepared statement)
    echo "<p>Secure mode: parameterized query (casts and uses prepared statement)</p>";
    // ✅ VALIDATION: chỉ cho phép số
    if (!filter_var($id_raw, FILTER_VALIDATE_INT)) {
        echo "<p style='color:red'>Invalid input: product_id must be an integer.</p>";
        die();
    }
    $id = intval($id_raw);
    echo "<p>Using product_id = " . h($id) . " (cast to integer)</p>";
    $stmt = mysqli_prepare($conn, 'SELECT id, name, description FROM products WHERE id = ?');
    if (!$stmt) {
        echo "<p style='color:red'>Prepare failed: " . h(mysqli_error($conn)) . "</p>";
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        echo "<h2>Product Result</h2>";
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                echo "ID: " . h($row['id']) . "<br>";
                echo "Name: " . h($row['name']) . "<br>";
                echo "Description: " . h($row['description']) . "<br><hr>";
            }
        } else {
            echo "<p style='color:red'>Query error: " . h(mysqli_error($conn)) . "</p>";
        }
        mysqli_stmt_close($stmt);
    }
}

?>