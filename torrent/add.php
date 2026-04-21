<?php
include 'settings.php';


header('Content-Type: application/json');

// Проверяем, какой метод добавления используется
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['magnet'])) {
    $magnet = trim($_GET['magnet']);
    if (empty($magnet)) {
        echo json_encode(['error' => 'Empty magnet link']);
        exit;
    }
    addTorrent(['filename' => $magnet]);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['torrent']) && $_FILES['torrent']['error'] === UPLOAD_ERR_OK) {
    $file_path = $_FILES['torrent']['tmp_name'];
    $file_content = file_get_contents($file_path);
    if ($file_content === false) {
        echo json_encode(['error' => 'Failed to read uploaded file']);
        exit;
    }
    $base64_content = base64_encode($file_content);
    addTorrent(['metainfo' => $base64_content]);
}
else {
    echo json_encode(['error' => 'Invalid request. Use ?magnet=... or POST with torrent']);
    exit;
}

/**
 * Отправляет запрос к Transmission RPC.
 */
function addTorrent($params) {
    global $transmission_host, $transmission_port, $transmission_auth, $transmission_user, $transmission_pass, $download_dir, $paused;

    // Добавляем опциональные параметры, если заданы
    if ($download_dir) $params['download-dir'] = $download_dir;
    if ($paused) $params['paused'] = true;

    $rpc_data = [
        'method'    => 'torrent-add',
        'arguments' => $params,
        'tag'       => time()
    ];
    $json_data = json_encode($rpc_data);

    $url = "http://{$transmission_host}:{$transmission_port}/transmission/rpc";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_data)
    ]);

    if ($transmission_auth) {
        curl_setopt($ch, CURLOPT_USERPWD, "$transmission_user:$transmission_pass");
    }

    // Первый запрос – возможно, получим 409 с X-Transmission-Session-Id
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code === 409) {
        // Извлекаем Session-ID из заголовков
        $headers = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
            if (stripos($header, 'X-Transmission-Session-Id:') === 0) {
                $headers['session_id'] = trim(substr($header, strlen('X-Transmission-Session-Id:')));
            }
            return strlen($header);
        });
        curl_exec($ch); // повторяем, чтобы заполнить $headers
        if (isset($headers['session_id'])) {
            // Повторяем запрос с правильным заголовком
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Transmission-Session-Id: ' . $headers['session_id'],
                'Content-Length: ' . strlen($json_data)
            ]);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
    }

    curl_close($ch);

    if ($http_code !== 200) {
        echo json_encode(['error' => "HTTP $http_code", 'response' => $response]);
        exit;
    }

    $result = json_decode($response, true);
    if (isset($result['result']) && $result['result'] === 'success') {
        echo json_encode(['success' => true, 'torrent' => $result['arguments']['torrent-added'] ?? $result['arguments']['torrent-duplicate'] ?? null]);
    } else {
        echo json_encode(['error' => $result['result'] ?? 'Unknown error', 'full_response' => $result]);
    }
}
?>