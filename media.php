<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$courseId = (int)($_GET['course_id'] ?? 0);
$type = $_GET['type'] ?? '';

$allowedTypes = ['pdf', 'video', 'course_pdf', 'course_video'];
if (!in_array($type, $allowedTypes, true)) {
    http_response_code(400);
    exit('Invalid media type.');
}

if ($courseId > 0 && in_array($type, ['course_pdf', 'course_video'], true)) {
    $s = $pdo->prepare("SELECT video_file, pdf_file FROM courses WHERE id = ? AND status = 'active' LIMIT 1");
    $s->execute([$courseId]);
    $course = $s->fetch();
    if (!$course) {
        http_response_code(404);
        exit('Course not found.');
    }
    $file = $type === 'course_pdf' ? ($course['pdf_file'] ?? '') : ($course['video_file'] ?? '');
} else {
    if ($id <= 0) {
        http_response_code(400);
        exit('Invalid lesson ID.');
    }
    $s = $pdo->prepare("SELECT video_file, document_file FROM lessons WHERE id = ? AND status = 'active' LIMIT 1");
    $s->execute([$id]);
    $l = $s->fetch();
    if (!$l) {
        http_response_code(404);
        exit('Lesson not found.');
    }
    $file = $type === 'pdf' ? ($l['document_file'] ?? '') : ($l['video_file'] ?? '');
}

if ($file === '') {
    http_response_code(404);
    exit('File not found.');
}

// Only allow a stored basename, never a path supplied by the browser.
$file = basename($file);
$base = realpath(__DIR__ . '/uploads');
$path = realpath(__DIR__ . '/uploads/' . $file);

if (!$path || !$base || !is_file($path) || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
    http_response_code(404);
    exit('File not found.');
}

$isPdf = in_array($type, ['pdf', 'course_pdf'], true);
$mime = $isPdf ? 'application/pdf' : (mime_content_type($path) ?: 'application/octet-stream');
$size = filesize($path);
$start = 0;
$end = $size - 1;

header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Disposition: inline; filename="' . basename($path) . '"');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    header('Content-Length: ' . $size);
    exit;
}

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    if ($m[1] === '' && $m[2] !== '') {
        $suffix = (int)$m[2];
        $start = max(0, $size - $suffix);
    } else {
        $start = (int)($m[1] ?? 0);
        if ($m[2] !== '') $end = (int)$m[2];
    }

    $end = min($end, $size - 1);

    if ($start > $end || $start >= $size) {
        http_response_code(416);
        header("Content-Range: bytes */$size");
        exit;
    }

    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
}

$length = $end - $start + 1;
header('Content-Length: ' . $length);

$fp = fopen($path, 'rb');
if (!$fp) {
    http_response_code(500);
    exit('Unable to open media file.');
}

fseek($fp, $start);
$remaining = $length;

while ($remaining > 0 && !feof($fp)) {
    $chunk = fread($fp, min(1024 * 1024, $remaining));
    if ($chunk === false) break;
    echo $chunk;
    $remaining -= strlen($chunk);
    flush();
    if (connection_aborted()) break;
}

fclose($fp);
