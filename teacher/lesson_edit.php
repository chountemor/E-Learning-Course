<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/teacher_auth.php';
require_teacher();
http_response_code(403);
exit('Teacher accounts can upload Video and PDF content only. Lesson editing and deletion are managed by the Administrator.');
