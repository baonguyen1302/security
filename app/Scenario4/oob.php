<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../mode_store.php';

$id = $_GET['id'] ?? 1;
$mode = get_mode(); // get global mode from cookie (set via /app/mode.php)
echo "Mode: $mode<br>";

// Two modes: default "vulnerable" (demonstration) and "secure" (prepared statement + guidance)
if ($mode !== 'secure') {
    echo "<h1>Scenario 4 - Vulnerable SQL Injection</h1>";

    // Vulnerable: directly interpolates user input into SQL
    $sql = "SELECT secret_value FROM secrets WHERE id = $id";

    echo "Query: $sql <br><br>";

    $res = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        echo "Secret: " . $row['secret_value'] . "<br>";
    }

} else {
    // secure mode: parameterized query to prevent SQL injection
    echo "<h1>Scenario 4 - secure mode (prepared statement)</h1>";

    // Sử dụng prepared statement để tránh SQL injection:
    // - mysqli_prepare tạo một prepared statement với placeholder (?) cho tham số.
    // - mysqli_stmt_bind_param gán biến PHP vào placeholder. Giá trị "i" chỉ kiểu integer;
    //   nếu tham số là chuỗi dùng "s". Việc bind tách cấu trúc truy vấn khỏi dữ liệu đầu vào,
    //   nên dữ liệu không thể phá vỡ cấu trúc SQL.
    // - mysqli_stmt_execute thực thi statement đã bind.
    // - mysqli_stmt_bind_result liên kết cột kết quả (secret_value) với biến PHP $secret.
    // Lưu ý: trong môi trường thực tế nên kiểm tra trả về của mysqli_prepare/mysqli_stmt_execute
    // để xử lý lỗi an toàn (logging, không lộ thông tin chi tiết cho người dùng).
    $stmt = mysqli_prepare($conn, "SELECT secret_value FROM secrets WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $secret);

    while (mysqli_stmt_fetch($stmt)) {
        // Escape output for HTML context
        echo "Secret: " . htmlspecialchars($secret, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "<br>";
    }

    mysqli_stmt_close($stmt);

    // Hướng dẫn phòng thủ: các prepared statements ngăn SQLi truyền thống,
    // nhưng rò rỉ dữ liệu theo kiểu out-of-band (OOB) qua DNS cần thêm biện pháp.
    // (Các bình luận tiếng Việt này giải thích mục đích của phần hiển thị bên dưới.)
    }
?>