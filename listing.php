<?php
$dir = '/var/www/html/video/';

// Нормализуем путь: убираем лишние слеши, добавляем завершающий слеш
$baseDir = rtrim($dir, '/') . '/';

$files = [];

if (is_dir($baseDir)) {
    // Рекурсивный обход всех элементов директории
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isFile()) {
            // Получаем путь относительно $baseDir
            $relativePath = substr($item->getPathname(), strlen($baseDir));
            $files[] = 'video/'.$relativePath;
        }
    }
}

// Отдаём результат в формате JSON
header('Content-Type: application/json');
echo json_encode(['video' => $files]);