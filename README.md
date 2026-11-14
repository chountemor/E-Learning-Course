# eLearning College — Enhanced PHP + MySQL

## Included
- Modern responsive student website.
- Admin dashboard with course/student/enrollment/lesson statistics.
- Admin can create/edit/delete courses.
- Admin can upload course cover images.
- Admin can add/edit/delete lessons.
- Lesson media: protected PDF upload and video upload, plus optional external URLs.
- Students learn for free.
- Login is required before a lesson can be viewed.
- Register requires valid email + phone + password.
- Login accepts email OR phone + password.
- Password failures are counted. On the 5th wrong password the account becomes `blocked`.
- A blocked email remains in the database and cannot be registered again; the student must use a different email/phone.
- Passwords use `password_hash()` / `password_verify()`.
- Uploaded lesson files are served through `media.php` after login, rather than exposing direct file URLs.

## Install on XAMPP
1. Copy the project to `C:\xampp\htdocs\eLearning-College-Full`.
2. Import `database.sql` in phpMyAdmin.
3. Check `config/database.php` for MySQL username/password.
4. Start Apache + MySQL.
5. Open `http://localhost/eLearning-College-Full/`.
6. Register a student account.
7. Default administrator is included:
   - Email: `admin@elearning.com`
   - Password: `Admin@12345`
   Change the password after first login.
8. Sign in as admin and open Dashboard. The admin can manage users, categories, courses, lessons, teachers, staff and news.
9. Students can sign in and view published lessons. Students do not get access to the admin panel.

### Important for uploads
For large video uploads, PHP/Apache limits must be increased. Example `php.ini` values:
- `upload_max_filesize = 512M`
- `post_max_size = 520M`
- `max_execution_time = 300`
- `max_input_time = 300`

Then restart Apache.

## Authentication behavior
A valid email format is required at registration. Login does not accept a non-existent email/phone. Five incorrect passwords permanently block that account until an administrator manually changes its status; the same email cannot be registered again because `users.email` is unique.

> Note: A normal successful login can be used again later. Blocking after 5 failed attempts is the behavior that prevents the old account from being used after repeated incorrect passwords.
\n\n## Updated Features\n- Free online learning structure for courses, lessons, PDF and video content.\n- Khmer/English translation files in `lang/` and `assets/js/translation.js`.\n- Dark/Light mode removed; website uses a consistent light theme.\n- Teacher, Admin and Student areas preserved.\n
## IMPORTANT — Existing Database Compatibility

If you are upgrading an existing eLearning College database, the new package uses the
`courses.featured` column for Featured Courses. The updated `config/database.php` now
checks for this column and adds it automatically when the website connects.

You can also run `database_teacher_featured_upgrade.sql` manually in phpMyAdmin if you
prefer database migrations to be explicit.

The homepage Featured Courses section is limited to 3 active courses.
