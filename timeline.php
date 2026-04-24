<?php
/**
 * timeline.php
 * 
 * API для работы с временными метками по именам.
 * Данные хранятся в файле timeline.json в формате:
 * [{"name":"...","time":123}, ...]
 * 
 * Поддерживаемые действия:
 * ?add&name=...&time=...   - создать/обновить запись
 * ?get&name=...            - получить время по имени
 * ?getall                  - получить все записи
 * ?del&name=...            - удалить запись по имени
 */

// Устанавливаем заголовок JSON
header('Content-Type: application/json');

// Имя файла для хранения данных
define('DATA_FILE', 'timeline.json');

/**
 * Загружает данные из JSON-файла с блокировкой на чтение
 * @return array Массив записей
 */
function loadData() {
    if (!file_exists(DATA_FILE)) {
        return [];
    }

    $fp = fopen(DATA_FILE, 'r');
    if (!$fp) {
        return [];
    }

    if (flock($fp, LOCK_SH)) {
        $content = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        $data = json_decode($content, true);
        if (is_array($data)) {
            return $data;
        }
    } else {
        fclose($fp);
    }
    return [];
}

/**
 * Сохраняет данные в JSON-файл с эксклюзивной блокировкой
 * @param array $data Массив записей
 * @return bool true в случае успеха
 */
function saveData($data) {
    $fp = fopen(DATA_FILE, 'c');
    if (!$fp) {
        return false;
    }

    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    fclose($fp);
    return false;
}

/**
 * Ищет запись по имени
 * @param array $data Массив записей
 * @param string $name Имя для поиска
 * @return array|null Найденная запись или null
 */
function findByName($data, $name) {
    foreach ($data as $item) {
        if ($item['name'] === $name) {
            return $item;
        }
    }
    return null;
}

// Определяем действие по наличию параметров в $_GET
$action = null;
if (isset($_GET['add'])) {
    $action = 'add';
} elseif (isset($_GET['get'])) {
    $action = 'get';
} elseif (isset($_GET['getall'])) {
    $action = 'getall';
} elseif (isset($_GET['del'])) {
    $action = 'del';
} else {
    echo json_encode(['error' => 'No action specified. Use add, get, getall or del.']);
    exit;
}

// Обработка действий
switch ($action) {
    case 'add':
        // Проверяем наличие обязательных параметров
        if (!isset($_GET['name']) || $_GET['name'] === '') {
            echo json_encode(['error' => 'Parameter "name" is required for add action.']);
            break;
        }
        if (!isset($_GET['time'])) {
            echo json_encode(['error' => 'Parameter "time" is required for add action.']);
            break;
        }

        $name = $_GET['name'];
        $time = is_numeric($_GET['time']) ? (float)$_GET['time'] : null;
        if ($time === null) {
            echo json_encode(['error' => 'Parameter "time" must be a number.']);
            break;
        }

        $data = loadData();
        $found = false;
        foreach ($data as &$item) {
            if ($item['name'] === $name) {
                $item['time'] = $time;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $data[] = ['name' => $name, 'time' => $time];
        }

        if (saveData($data)) {
            echo json_encode(['status' => $found ? 'updated' : 'created', 'name' => $name, 'time' => $time]);
        } else {
            echo json_encode(['error' => 'Failed to write data file.']);
        }
        break;

    case 'get':
        if (!isset($_GET['name']) || $_GET['name'] === '') {
            echo json_encode(['error' => 'Parameter "name" is required for get action.']);
            break;
        }

        $name = $_GET['name'];
        $data = loadData();
        $entry = findByName($data, $name);
        if ($entry !== null) {
            echo json_encode(['time' => $entry['time']]);
        } else {
            echo json_encode(['time' => null]);
        }
        break;

    case 'getall':
        $data = loadData();
        echo json_encode($data);
        break;

    case 'del':
        if (!isset($_GET['name']) || $_GET['name'] === '') {
            echo json_encode(['error' => 'Parameter "name" is required for del action.']);
            break;
        }

        $name = $_GET['name'];
        $data = loadData();
        $newData = array_filter($data, function($item) use ($name) {
            return $item['name'] !== $name;
        });
        // переиндексируем массив
        $newData = array_values($newData);

        if (saveData($newData)) {
            $deleted = (count($data) !== count($newData));
            echo json_encode(['status' => $deleted ? 'deleted' : 'not found', 'name' => $name]);
        } else {
            echo json_encode(['error' => 'Failed to write data file.']);
        }
        break;
}