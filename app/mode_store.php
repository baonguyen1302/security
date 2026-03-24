<?php
// Simple server-side mode storage. Stores the current mode in a file under app/.
// This avoids relying on client-side cookies and keeps the mode controlled on the server.

// Primary file location (inside repo). Many PHP setups cannot write here when code is mounted
// inside a container. We'll try this first, but fall back to a temp-file path if writing fails.
define('MODE_STORE_PRIMARY', __DIR__ . '/.mode');
define('MODE_STORE_FALLBACK', sys_get_temp_dir() . '/sqli_mode');

function read_file_silent(string $path): ?string {
    $data = @file_get_contents($path);
    if ($data === false) return null;
    return $data;
}

function get_mode(): string {
    // Try primary file first
    $v = read_file_silent(MODE_STORE_PRIMARY);
    if ($v !== null) {
        $v = trim($v);
        return $v === 'secure' ? 'secure' : 'vulnerable';
    }

    // Fallback
    $v = read_file_silent(MODE_STORE_FALLBACK);
    if ($v !== null) {
        $v = trim($v);
        return $v === 'secure' ? 'secure' : 'vulnerable';
    }

    return 'vulnerable';
}

function set_mode(string $m): bool {
    $m = $m === 'secure' ? 'secure' : 'vulnerable';

    // First, try to write to primary location silently
    $w = @file_put_contents(MODE_STORE_PRIMARY, $m, LOCK_EX);
    if ($w !== false) return true;

    // If that fails, try fallback (temp dir) silently
    $w = @file_put_contents(MODE_STORE_FALLBACK, $m, LOCK_EX);
    if ($w !== false) return true;

    // If both fail, return false (caller should handle redirect without assuming success)
    return false;
}
