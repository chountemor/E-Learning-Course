# Popular Categories / Skills

The website Popular Categories section is now database-driven.

## 1. Database
Run `database_category_upgrade.sql` once in phpMyAdmin using database `elearning_college`.

It adds:
- `categories.name_kh` — Khmer category name
- `categories.icon` — emoji/icon shown on the website

## 2. Admin
Open:
`http://localhost:8000/admin/categories.php`

Admin can:
- Add a new skill/category
- Enter English + Khmer names
- Choose an icon/emoji
- Set Active/Inactive
- Edit categories
- Delete categories safely (courses are kept)

## 3. Website
`index.php` now loads active categories directly from the database. New categories added by Admin automatically appear under **Popular Categories**.

## 4. Courses
`courses.php?cat=CategoryName` filters courses by the selected category. Course Add/Edit already loads active categories from the `categories` table.
