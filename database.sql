/* =========================================================
   eLEARNING COLLEGE DATABASE
   Database: elearning_college
   ========================================================= */

CREATE DATABASE IF NOT EXISTS elearning_college
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE elearning_college;


/* =========================================================
   DROP TABLES
   ========================================================= */

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS more_information;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;


/* =========================================================
   1. USERS
   Login / Register
   ========================================================= */

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(120) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM('admin','teacher','student')
        NOT NULL DEFAULT 'student',

    status ENUM('active','inactive','pending','blocked')
        NOT NULL DEFAULT 'active',

    phone VARCHAR(30) NULL,

    gender ENUM('male','female','other') NULL,

    date_of_birth DATE NULL,

    address VARCHAR(255) NULL,

    education_level VARCHAR(100) NULL,

    profile_image VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    failed_login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_at DATETIME NULL,
    last_login_at DATETIME NULL

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   2. CATEGORIES
   ========================================================= */

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    name_kh VARCHAR(150) NULL,

    description TEXT NULL,

    image VARCHAR(255) NULL,

    icon VARCHAR(50) NOT NULL DEFAULT '📚',

    status ENUM('active','inactive')
        NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   3. COURSES
   ========================================================= */

CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    category_id INT UNSIGNED NULL,

    featured TINYINT(1) NOT NULL DEFAULT 0,

    title VARCHAR(255) NOT NULL,

    title_kh VARCHAR(255) NULL,

    category VARCHAR(150) NULL,

    description TEXT NOT NULL,

    description_kh TEXT NULL,

    duration VARCHAR(100) NULL,

    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    image VARCHAR(255) NULL,

    video_url TEXT NULL,

    video_file VARCHAR(255) NULL,

    pdf_url TEXT NULL,

    pdf_file VARCHAR(255) NULL,

    instructor VARCHAR(150) NULL,

    level ENUM(
        'beginner',
        'intermediate',
        'advanced'
    ) DEFAULT 'beginner',

    status ENUM(
        'draft',
        'active',
        'inactive'
    ) DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_courses_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   4. LESSONS
   ========================================================= */

CREATE TABLE lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    course_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,

    title_kh VARCHAR(255) NULL,

    description TEXT NULL,

    video_url VARCHAR(500) NULL,
    video_file VARCHAR(255) NULL,
    document_url VARCHAR(500) NULL,
    document_file VARCHAR(255) NULL,

    duration VARCHAR(50) NULL,

    lesson_order INT NOT NULL DEFAULT 1,

    created_by INT UNSIGNED NULL,

    status ENUM('active','inactive')
        DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_lessons_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_lessons_created_by
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_lessons_course (course_id),
    INDEX idx_lessons_created_by (created_by)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   5. ENROLLMENTS
   Student enroll Course
   ========================================================= */

CREATE TABLE enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    course_id INT UNSIGNED NOT NULL,

    status ENUM(
        'pending',
        'approved',
        'completed',
        'cancelled'
    ) DEFAULT 'approved',

    progress DECIMAL(5,2) NOT NULL DEFAULT 0.00,

    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    completed_at TIMESTAMP NULL,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_enrollment (
        user_id,
        course_id
    ),

    CONSTRAINT fk_enrollments_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_enrollments_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_enrollments_user (user_id),

    INDEX idx_enrollments_course (course_id)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   6. TEACHERS
   ========================================================= */

CREATE TABLE teachers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    name_kh VARCHAR(150) NULL,

    email VARCHAR(150) NULL,

    phone VARCHAR(30) NULL,

    specialization VARCHAR(200) NULL,

    category_id INT UNSIGNED NULL,

    biography TEXT NULL,

    photo VARCHAR(255) NULL,

    status ENUM('active','inactive')
        DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_teachers_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_teachers_category (category_id)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   7. STAFF
   ========================================================= */

CREATE TABLE staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    name_kh VARCHAR(150) NULL,

    position VARCHAR(150) NULL,

    email VARCHAR(150) NULL,

    phone VARCHAR(30) NULL,

    photo VARCHAR(255) NULL,

    description TEXT NULL,

    status ENUM('active','inactive')
        DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   8. NEWS
   ========================================================= */

CREATE TABLE news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,

    title_kh VARCHAR(255) NULL,

    content TEXT NOT NULL,

    content_kh TEXT NULL,

    image VARCHAR(255) NULL,

    author VARCHAR(150) NULL,

    status ENUM(
        'draft',
        'published',
        'inactive'
    ) DEFAULT 'published',

    published_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   9. MORE INFORMATION
   Admin-managed content for "More From eLearning College".
   This is intentionally separate from courses.
   ========================================================= */

CREATE TABLE more_information (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icon VARCHAR(100) NOT NULL DEFAULT 'fa-solid fa-circle-info',
    eyebrow_en VARCHAR(150) NULL,
    eyebrow_kh VARCHAR(150) NULL,
    title_en VARCHAR(255) NOT NULL,
    title_kh VARCHAR(255) NULL,
    description_en TEXT NULL,
    description_kh TEXT NULL,
    content_en LONGTEXT NULL,
    content_kh LONGTEXT NULL,
    image VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft','published','inactive') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_more_information_status_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO more_information
(slug,icon,eyebrow_en,eyebrow_kh,title_en,title_kh,description_en,description_kh,content_en,content_kh,sort_order,status)
VALUES
('about','fa-solid fa-building-columns','ABOUT THE COLLEGE','អំពីមហាវិទ្យាល័យ','About eLearning College','អំពី eLearning College','Learn about our mission, learning community, and the idea behind free online education.','ស្វែងយល់អំពីបេសកកម្ម សហគមន៍សិក្សា និងគោលគំនិតនៃការអប់រំតាមអនឡាញដោយឥតគិតថ្លៃ។','eLearning College is a modern online learning platform designed to make useful learning resources easier to access. Explore the platform, learn at your own pace, and build knowledge from anywhere.','eLearning College គឺជាវេទិកាសិក្សាតាមអនឡាញទំនើប ដែលបង្កើតឡើងដើម្បីធ្វើឱ្យធនធានសិក្សាមានភាពងាយស្រួលក្នុងការចូលប្រើ។ អ្នកអាចរៀនតាមល្បឿនរបស់ខ្លួន និងបង្កើនចំណេះដឹងពីគ្រប់ទីកន្លែង។',1,'published'),
('how-it-works','fa-solid fa-circle-play','HOW IT WORKS','របៀបសិក្សា','How Online Learning Works','របៀបរៀនតាមអនឡាញ','See how to create an account, discover learning materials, study online, and track your progress.','មើលពីរបៀបបង្កើតគណនី ស្វែងរកឯកសារ រៀនតាមអនឡាញ និងតាមដានវឌ្ឍនភាព។','Start with your account, discover the learning materials you need, study online, and return whenever you want to continue.','ចាប់ផ្តើមពីគណនីរបស់អ្នក ស្វែងរកធនធានសិក្សាដែលត្រូវការ រៀនតាមអនឡាញ ហើយអាចត្រឡប់មកបន្តរៀនបានគ្រប់ពេល។',2,'published'),
('certificates','fa-solid fa-award','ACHIEVEMENTS','សមិទ្ធផល','Certificates & Achievements','វិញ្ញាបនបត្រ និងសមិទ្ធផល','Understand completion, certificates, achievements, and learning progress.','ស្វែងយល់ពីការបញ្ចប់ វិញ្ញាបនបត្រ សមិទ្ធផល និងវឌ្ឍនភាពសិក្សា។','Learn how course completion, certificates, and learning achievements can be part of your learning journey.','ស្វែងយល់អំពីការបញ្ចប់ការសិក្សា វិញ្ញាបនបត្រ និងសមិទ្ធផលដែលអាចជាផ្នែកមួយនៃដំណើរសិក្សារបស់អ្នក។',3,'published'),
('news','fa-solid fa-newspaper','LATEST INFORMATION','ព័ត៌មានថ្មីៗ','News & Updates','ព័ត៌មាន និងការជូនដំណឹង','Stay informed about announcements, platform updates, learning activities, and community news.','ទទួលព័ត៌មានអំពីសេចក្តីជូនដំណឹង ការអាប់ដេត សកម្មភាពសិក្សា និងព័ត៌មានសហគមន៍។','Stay up to date with announcements, platform changes, learning activities, and other information published by eLearning College.','ទទួលព័ត៌មានថ្មីៗអំពីសេចក្តីជូនដំណឹង ការផ្លាស់ប្តូរវេទិកា សកម្មភាពសិក្សា និងព័ត៌មានផ្សេងៗពី eLearning College។',4,'published');


/* =========================================================
   10. CATEGORIES DATA
   ========================================================= */

INSERT INTO categories
(name, description)
VALUES
(
    'Information Technology',
    'Technology and computer related courses'
),
(
    'Business',
    'Business and management courses'
),
(
    'Finance',
    'Accounting and financial courses'
),
(
    'Languages',
    'English and other language courses'
);


/* =========================================================
   10. COURSES DATA
   ========================================================= */

INSERT INTO courses
(
    category_id,
    title,
    title_kh,
    category,
    description,
    description_kh,
    duration,
    price,
    image,
    instructor,
    level,
    status
)
VALUES

(
    1,
    'Full Stack Web Development',
    'ការអភិវឌ្ឍន៍ Web Full Stack',
    'Information Technology',
    'Learn HTML, CSS, JavaScript, PHP and MySQL to build modern web applications.',
    'សិក្សា HTML, CSS, JavaScript, PHP និង MySQL ដើម្បីបង្កើត Web Application ទំនើប។',
    '16 Weeks',
    99.00,
    'web-development.jpg',
    'eLearning College',
    'beginner',
    'active'
),

(
    2,
    'Business Management',
    'ការគ្រប់គ្រងអាជីវកម្ម',
    'Business',
    'Learn leadership, planning and modern business management skills.',
    'សិក្សាពីការដឹកនាំ ការរៀបចំផែនការ និងជំនាញគ្រប់គ្រងអាជីវកម្មទំនើប។',
    '12 Weeks',
    79.00,
    'business.jpg',
    'eLearning College',
    'beginner',
    'active'
),

(
    3,
    'Accounting and Finance',
    'គណនេយ្យ និងហិរញ្ញវត្ថុ',
    'Finance',
    'Build practical accounting and financial management knowledge.',
    'បង្កើនចំណេះដឹងជាក់ស្តែងផ្នែកគណនេយ្យ និងការគ្រប់គ្រងហិរញ្ញវត្ថុ។',
    '14 Weeks',
    89.00,
    'accounting.jpg',
    'eLearning College',
    'intermediate',
    'active'
),

(
    4,
    'English for Beginners',
    'ភាសាអង់គ្លេសសម្រាប់អ្នកចាប់ផ្តើម',
    'Languages',
    'Learn basic English vocabulary, grammar and communication.',
    'សិក្សាវាក្យសព្ទ វេយ្យាករណ៍ និងការទំនាក់ទំនងភាសាអង់គ្លេសមូលដ្ឋាន។',
    '10 Weeks',
    59.00,
    'english.jpg',
    'eLearning College',
    'beginner',
    'active'
);


/* =========================================================
   11. LESSONS DATA
   ========================================================= */

INSERT INTO lessons
(
    course_id,
    title,
    title_kh,
    description,
    duration,
    lesson_order,
    status
)
VALUES

(
    1,
    'Introduction to Web Development',
    'សេចក្តីផ្តើមអំពី Web Development',
    'Introduction to modern web development.',
    '45 Minutes',
    1,
    'active'
),

(
    1,
    'HTML Fundamentals',
    'មូលដ្ឋាន HTML',
    'Learn the fundamentals of HTML.',
    '60 Minutes',
    2,
    'active'
),

(
    1,
    'CSS Fundamentals',
    'មូលដ្ឋាន CSS',
    'Learn how to design websites using CSS.',
    '60 Minutes',
    3,
    'active'
),

(
    1,
    'JavaScript Fundamentals',
    'មូលដ្ឋាន JavaScript',
    'Learn JavaScript programming basics.',
    '75 Minutes',
    4,
    'active'
),

(
    1,
    'PHP and MySQL',
    'PHP និង MySQL',
    'Build dynamic websites using PHP and MySQL.',
    '90 Minutes',
    5,
    'active'
);


/* =========================================================
   12. TEACHERS DATA
   ========================================================= */

INSERT INTO teachers
(
    name,
    name_kh,
    email,
    phone,
    specialization,
    biography,
    photo,
    status
)
VALUES

(
    'Dara Sok',
    'សុខ ដារ៉ា',
    'dara@elearning.com',
    '012345678',
    'Web Development',
    'Experienced instructor in web development and programming.',
    'teacher-1.jpg',
    'active'
),

(
    'Sokha Chan',
    'ចាន់ សុខា',
    'sokha@elearning.com',
    '012345679',
    'Business Management',
    'Business management and leadership instructor.',
    'teacher-2.jpg',
    'active'
),

(
    'Davy Kim',
    'គឹម ដាវី',
    'davy@elearning.com',
    '012345680',
    'Accounting and Finance',
    'Accounting and financial management instructor.',
    'teacher-3.jpg',
    'active'
);


/* =========================================================
   13. STAFF DATA
   ========================================================= */

INSERT INTO staff
(
    name,
    name_kh,
    position,
    email,
    phone,
    photo,
    description,
    status
)
VALUES

(
    'College Administrator',
    'អ្នកគ្រប់គ្រងមហាវិទ្យាល័យ',
    'Administrator',
    'admin@elearning.com',
    '012345678',
    'staff-1.jpg',
    'eLearning College administration.',
    'active'
),

(
    'Academic Officer',
    'មន្ត្រីសិក្សា',
    'Academic Officer',
    'academic@elearning.com',
    '012345679',
    'staff-2.jpg',
    'Academic administration and student support.',
    'active'
);


/* =========================================================
   14. NEWS DATA
   ========================================================= */

INSERT INTO news
(
    title,
    title_kh,
    content,
    content_kh,
    image,
    author,
    status,
    published_at
)
VALUES

(
    'Welcome to eLearning College',
    'សូមស្វាគមន៍មកកាន់ eLearning College',
    'Welcome to our online learning platform.',
    'សូមស្វាគមន៍មកកាន់វេទិកាសិក្សាអនឡាញរបស់យើង។',
    'welcome.jpg',
    'Administrator',
    'published',
    CURRENT_TIMESTAMP
),

(
    'New Courses Available',
    'វគ្គសិក្សាថ្មីមាននៅលើប្រព័ន្ធ',
    'New online courses are now available for students.',
    'វគ្គសិក្សាអនឡាញថ្មីៗបានដាក់ឱ្យសិស្សចុះឈ្មោះសិក្សា។',
    'new-course.jpg',
    'Administrator',
    'published',
    CURRENT_TIMESTAMP
);


/* =========================================================
   15. CREATE VIEW
   Course + Lesson Count
   ========================================================= */

CREATE OR REPLACE VIEW course_statistics AS
SELECT
    c.id,
    c.title,
    c.category,
    c.price,
    c.duration,
    c.status,
    COUNT(l.id) AS lesson_count
FROM courses c
LEFT JOIN lessons l
    ON l.course_id = c.id
GROUP BY
    c.id,
    c.title,
    c.category,
    c.price,
    c.duration,
    c.status;


/* =========================================================
   16. CREATE VIEW
   Enrollment Statistics
   ========================================================= */

CREATE OR REPLACE VIEW enrollment_statistics AS
SELECT
    c.id AS course_id,
    c.title AS course_title,
    COUNT(e.id) AS total_students
FROM courses c
LEFT JOIN enrollments e
    ON e.course_id = c.id
GROUP BY
    c.id,
    c.title;


/* =========================================================
   17. DEFAULT ADMIN ACCOUNT
   Change this password after first login.
   Email: admin@elearning.com
   Password: Admin@12345
   ========================================================= */
INSERT INTO users (name, email, password, role, status)
VALUES (
    'System Administrator',
    'admin@elearning.com',
    '$2y$12$m3Tec/J4Fh2AnG7QJNP9UegjRckctqk95W3sm9OL7ti/HY4WODfui',
    'admin',
    'active'
)
ON DUPLICATE KEY UPDATE
    role = 'admin',
    status = 'active';

/* =========================================================
   DATABASE READY
   ========================================================= */
-- Teacher ownership and permissions upgrade (run once on existing database)
ALTER TABLE lessons ADD COLUMN created_by INT NULL AFTER course_id;
ALTER TABLE lessons ADD INDEX idx_lessons_created_by (created_by);
