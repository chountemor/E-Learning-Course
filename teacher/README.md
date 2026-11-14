# Teacher Portal

## Folder structure

- `teacher/layout.php` — shared teacher sidebar + top bar
- `teacher/dashboard.php` — teacher dashboard
- `teacher/lessons.php` — lessons created by the logged-in teacher
- `teacher/lesson_add.php` — add lesson, video URL and PDF URL
- `teacher/lesson_edit.php` — blocked for teachers; Admin manages editing/deletion
- `assets/css/teacher/style.css` — all Teacher Portal styles

## Database

Run `database_teacher_upgrade.sql` once in phpMyAdmin for database `elearning_college`.

It adds `lessons.created_by` and its index/foreign key only when they are missing.

## Teacher account

Admin → User Management can set a user's role to **Teacher**. The login page already redirects Teacher accounts to `teacher/dashboard.php`.
