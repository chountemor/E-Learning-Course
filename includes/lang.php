<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$lang = 'en';
$_SESSION['site_lang'] = 'en';

function lang_url(string $language): string {
    $params = $_GET;
    $params['lang'] = $language;
    return '?' . http_build_query($params);
}

function t(string $en, string $kh): string {
    global $lang;
    return $en;
}

function course_title(array $course): string {
    global $lang;
    if ($lang === 'kh' && !empty($course['title_kh'])) return $course['title_kh'];
    return $course['title'] ?? '';
}
function course_description(array $course): string {
    global $lang;
    if ($lang === 'kh' && !empty($course['description_kh'])) return $course['description_kh'];
    return $course['description'] ?? '';
}
function lesson_title(array $lesson): string {
    global $lang;
    if ($lang === 'kh' && !empty($lesson['title_kh'])) return $lesson['title_kh'];
    return $lesson['title'] ?? '';
}
?>
