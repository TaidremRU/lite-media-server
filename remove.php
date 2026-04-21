<?php
// Base directory - all operations are restricted to this path
$dir = '/var/www/html/';

// Set JSON response header
header('Content-Type: application/json');

// Helper function to recursively delete a directory
function deleteDirectory($path) {
    if (!is_dir($path)) {
        return false;
    }

    $items = scandir($path);
    if ($items === false) {
        return false;
    }

    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $itemPath = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($itemPath)) {
            if (!deleteDirectory($itemPath)) {
                return false;
            }
        } else {
            if (!unlink($itemPath)) {
                return false;
            }
        }
    }

    return rmdir($path);
}

// Get the 'link' parameter from GET request
$link = isset($_GET['link']) ? $_GET['link'] : '';

// Validate that 'link' parameter is provided
if (empty($link)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing "link" parameter'
    ]);
    exit;
}

// Build the full path by concatenating base directory and the link
$fullPath = $dir . $link;



// Resolve the real path to prevent directory traversal attacks
$realBase = realpath($dir);
$realPath = realpath($fullPath);

// Security check: ensure the resolved path is within the base directory
if ($realPath === false || strpos($realPath, $realBase) !== 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid path: file or directory does not exist or is outside the allowed directory'
    ]);
    exit;
}

// Check if the file or directory exists
if (!file_exists($realPath)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'File or directory does not exist'
    ]);
    exit;
}

// Perform deletion based on type
try {
    if (is_file($realPath)) {
        // Delete a single file
        if (unlink($realPath)) {
            echo json_encode([
                'status'  => 'success',
                'message' => 'File deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to delete file'
            ]);
        }
    } elseif (is_dir($realPath)) {
        // Delete directory recursively
        if (deleteDirectory($realPath)) {
            echo json_encode([
                'status'  => 'success',
                'message' => 'Directory and all its contents deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to delete directory'
            ]);
        }
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Path is neither a file nor a directory'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}