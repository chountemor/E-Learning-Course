# Teacher Admin Fix — 2026-09-28

Fixed the Admin > Teachers workflow:

1. Teacher photo upload is now saved to `teachers.photo`.
2. Teacher photo preview is shown immediately after selecting an image.
3. Teacher login account is created in `users` with role `teacher`.
4. Existing teacher accounts are updated safely instead of creating duplicate users.
5. Existing student/admin accounts cannot be silently converted to teacher accounts when their email is reused.
6. Teacher password is stored using `password_hash()`.
7. Locked teacher accounts are reset when Admin changes the teacher password.
8. Teacher status is synchronized between `teachers.status` and `users.status`.
9. Deleting a teacher also deletes the matching teacher login account.
10. Teacher photo is removed when the teacher is deleted.
11. `lessons.created_by` remains safe because the database schema uses `ON DELETE SET NULL`.
12. Status handling is defensive, so `Undefined array key "status"` is avoided.
13. Photo upload validates MIME type and maximum size of 5 MB.

Teacher login:
- Open `Login/login.php`
- Use the Teacher email created by Admin
- Use the password entered by Admin
- Successful teacher login redirects to `teacher/dashboard.php`
