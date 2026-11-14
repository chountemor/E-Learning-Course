<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_admin();

if($_SERVER['REQUEST_METHOD']!=='POST' || !verify_csrf($_POST['csrf']??null)){
    http_response_code(419);
    exit('Invalid request.');
}

$id=(int)($_POST['id']??0);

$s=$pdo->prepare('SELECT image,video_file,pdf_file FROM courses WHERE id=?');
$s->execute([$id]);
$c=$s->fetch();

if($c){
    $uploadDir=__DIR__.'/../uploads/';

    foreach(['image','video_file','pdf_file'] as $field){
        if(!empty($c[$field]) && is_file($uploadDir.$c[$field])){
            @unlink($uploadDir.$c[$field]);
        }
    }

    /* Keep old Lesson cleanup for existing databases/projects. */
    $q=$pdo->prepare('SELECT video_file,document_file FROM lessons WHERE course_id=?');
    $q->execute([$id]);
    foreach($q as $lesson){
        foreach(['video_file','document_file'] as $field){
            if(!empty($lesson[$field]) && is_file($uploadDir.$lesson[$field])){
                @unlink($uploadDir.$lesson[$field]);
            }
        }
    }

    $pdo->prepare('DELETE FROM courses WHERE id=?')->execute([$id]);
}

header('Location: courses.php');
exit;
