<?php
include 'settings.php';

header('Content-Type: application/json');

// Проверяем передан ли ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Missing or invalid torrent ID']);
    exit;
}

$id = (int)$_GET['id'];

function httpPostWithSession($url, $json_data, $auth_user, $auth_pass) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    if ($auth_user) {
        curl_setopt($ch, CURLOPT_USERPWD, "$auth_user:$auth_pass");
    }
    
    $response_headers = [];
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$response_headers) {
        $response_headers[] = trim($header);
        return strlen($header);
    });
    
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Если 409 – извлекаем Session ID и повторяем
    if ($http_code == 409) {
        $session_id = null;
        foreach ($response_headers as $h) {
            if (stripos($h, 'X-Transmission-Session-Id:') === 0) {
                $session_id = trim(substr($h, strlen('X-Transmission-Session-Id:')));
                break;
            }
        }
        if ($session_id) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Transmission-Session-Id: ' . $session_id
            ]);
            if ($auth_user) {
                curl_setopt($ch, CURLOPT_USERPWD, "$auth_user:$auth_pass");
            }
            $response_body = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
    }

    if ($http_code !== 200) {
        return false;
    }
    return json_decode($response_body, true);
}

// Формируем запрос на удаление с удалением файлов
$rpc = [
    'method'    => 'torrent-remove',
    'arguments' => [
        'ids'             => [$id],
        'delete-local-data' => true   // удаляет сами файлы с диска
    ],
    'tag' => time()
];

$json_data = json_encode($rpc);
$url = "http://{$transmission_host}:{$transmission_port}/transmission/rpc";

$auth_user = $transmission_auth ? $transmission_user : null;
$auth_pass = $transmission_auth ? $transmission_pass : null;

$response = httpPostWithSession($url, $json_data, $auth_user, $auth_pass);

if (!$response || $response['result'] !== 'success') {
    echo json_encode(['error' => 'Failed to remove torrent', 'details' => $response]);
    exit;
}

echo json_encode(['success' => true, 'id' => $id]);
?>