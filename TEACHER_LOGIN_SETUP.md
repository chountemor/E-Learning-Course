# Teacher Login Setup

1. Import database.sql or run the ALTER statement if database already exists:

```sql
ALTER TABLE users MODIFY role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student';
ALTER TABLE lessons ADD COLUMN created_by INT NULL AFTER course_id;
ALTER TABLE lessons ADD INDEX idx_lessons_created_by (created_by);
```

2. Login to Admin > Teachers. Add teacher with email and password (minimum 8 characters).
3. Teacher logs in from Login/login.php using email and password.
4. Teacher is redirected to /teacher/dashboard.php and cannot access Admin.
