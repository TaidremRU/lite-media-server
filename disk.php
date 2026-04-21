<?php
header('Content-Type: application/json');

// Путь к корню файловой системы (для Linux/Unix) или к диску C: (для Windows)
// Для универсальности можно использовать текущую директорию: __DIR__
$path = '/';

$total = disk_total_space($path);
$free  = disk_free_space($path);

if ($total === false || $free === false) {
    echo json_encode(['error' => 'Unable to retrieve disk space information']);
    exit;
}

$used = $total - $free;

/**
 * Конвертирует байты в человеко-читаемый формат
 */
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

$response = [
    'total_bytes' => $total,
    'free_bytes'  => $free,
    'used_bytes'  => $used,
    'total_human' => formatSize($total),
    'free_human'  => formatSize($free),
    'used_human'  => formatSize($used),
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);