<?php
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// API requests go to /api/
if (strpos($path, '/api/') === 0) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        include $file;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(["error" => "API not found"]);
        exit;
    }
}

// Serve static files from the public folder
$file = __DIR__ . '/public' . $path;
if (file_exists($file) && !is_dir($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext === 'html') header('Content-Type: text/html');
    elseif ($ext === 'css') header('Content-Type: text/css');
    elseif ($ext === 'js') header('Content-Type: application/javascript');
    readfile($file);
    exit;
}

// Default to public/index.html (homepage)
$index = __DIR__ . '/public/index.html';
if (file_exists($index)) {
    readfile($index);
    exit;
}

http_response_code(404);
echo "404 Not Found";
?>
