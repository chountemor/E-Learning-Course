<?php
/* =========================================================
   eLearning College — Database Connection
   Safe compatibility migration for old installations.
   ========================================================= */
$host = 'localhost';
$dbname = 'elearning_college';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/database.php and make sure MariaDB/MySQL is running.');
}

/* ---------------------------------------------------------
   Compatibility helpers
   The project has evolved over several versions. Older DBs may
   not contain the newer columns. We add only missing columns.
   --------------------------------------------------------- */
function db_table_exists(PDO $pdo, string $table): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
    $q->execute([$table]);
    return (int)$q->fetchColumn() > 0;
}

function db_column_exists(PDO $pdo, string $table, string $column): bool {
    if (!db_table_exists($pdo, $table)) return false;
    $q = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    $q->execute([$table, $column]);
    return (int)$q->fetchColumn() > 0;
}

function db_add_column(PDO $pdo, string $table, string $column, string $definition): bool {
    if (!db_table_exists($pdo, $table) || db_column_exists($pdo, $table, $column)) return true;
    try {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        return true;
    } catch (Throwable $e) {
        error_log('eLearning schema migration failed for '.$table.'.'.$column.': '.$e->getMessage());
        return false;
    }
}

/* New database = already complete. Old database = upgrade missing fields. */
try {
    /* courses */
    db_add_column($pdo, 'courses', 'category_id', 'INT UNSIGNED NULL');
    db_add_column($pdo, 'courses', 'featured', 'TINYINT(1) NOT NULL DEFAULT 0');
    db_add_column($pdo, 'courses', 'title_kh', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'courses', 'category', 'VARCHAR(150) NULL');
    db_add_column($pdo, 'courses', 'description_kh', 'TEXT NULL');
    db_add_column($pdo, 'courses', 'duration', 'VARCHAR(100) NULL');
    db_add_column($pdo, 'courses', 'price', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    db_add_column($pdo, 'courses', 'image', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'courses', 'video_url', 'TEXT NULL');
    db_add_column($pdo, 'courses', 'video_file', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'courses', 'pdf_url', 'TEXT NULL');
    db_add_column($pdo, 'courses', 'pdf_file', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'courses', 'instructor', 'VARCHAR(150) NULL');
    db_add_column($pdo, 'courses', 'level', "VARCHAR(30) NOT NULL DEFAULT 'beginner'");
    db_add_column($pdo, 'courses', 'status', "VARCHAR(30) NOT NULL DEFAULT 'active'");
    db_add_column($pdo, 'courses', 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    db_add_column($pdo, 'courses', 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    /* users */
    db_add_column($pdo, 'users', 'phone', 'VARCHAR(30) NULL');
    db_add_column($pdo, 'users', 'role', "VARCHAR(20) NOT NULL DEFAULT 'student'");
    db_add_column($pdo, 'users', 'status', "VARCHAR(20) NOT NULL DEFAULT 'active'");
    db_add_column($pdo, 'users', 'failed_login_attempts', 'TINYINT UNSIGNED NOT NULL DEFAULT 0');
    db_add_column($pdo, 'users', 'locked_at', 'DATETIME NULL');
    db_add_column($pdo, 'users', 'last_login_at', 'DATETIME NULL');
    db_add_column($pdo, 'users', 'gender', 'VARCHAR(20) NULL');
    db_add_column($pdo, 'users', 'education_level', 'VARCHAR(100) NULL');
    db_add_column($pdo, 'users', 'profile_image', 'VARCHAR(255) NULL');

    /* teachers */
    db_add_column($pdo, 'teachers', 'category_id', 'INT UNSIGNED NULL');
    db_add_column($pdo, 'teachers', 'status', "VARCHAR(20) NOT NULL DEFAULT 'active'");

    /* lessons — required for Teacher My Lessons */
    db_add_column($pdo, 'lessons', 'title_kh', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'lessons', 'description', 'TEXT NULL');
    db_add_column($pdo, 'lessons', 'video_url', 'VARCHAR(500) NULL');
    db_add_column($pdo, 'lessons', 'video_file', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'lessons', 'document_url', 'VARCHAR(500) NULL');
    db_add_column($pdo, 'lessons', 'document_file', 'VARCHAR(255) NULL');
    db_add_column($pdo, 'lessons', 'duration', 'VARCHAR(50) NULL');
    db_add_column($pdo, 'lessons', 'lesson_order', 'INT NOT NULL DEFAULT 1');
    db_add_column($pdo, 'lessons', 'created_by', 'INT UNSIGNED NULL');
    db_add_column($pdo, 'lessons', 'status', "VARCHAR(20) NOT NULL DEFAULT 'active'");
    db_add_column($pdo, 'lessons', 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    db_add_column($pdo, 'lessons', 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
} catch (Throwable $e) {
    error_log('eLearning compatibility migration: '.$e->getMessage());
}


/* ---------------------------------------------------------
   More From eLearning College
   Create the content table automatically for older installs.
   --------------------------------------------------------- */
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS more_information (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $moreCount = (int)$pdo->query("SELECT COUNT(*) FROM more_information")->fetchColumn();
    if ($moreCount === 0) {
        $seed = $pdo->prepare("
            INSERT INTO more_information
            (slug, icon, eyebrow_en, eyebrow_kh, title_en, title_kh,
             description_en, description_kh, content_en, content_kh, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')
        ");
        $seedRows = [
            [
                'about','fa-solid fa-building-columns','ABOUT THE COLLEGE','អំពីមហាវិទ្យាល័យ',
                'About eLearning College','អំពី eLearning College',
                'Learn about our mission, learning community, and free online education.',
                'ស្វែងយល់អំពីបេសកកម្ម សហគមន៍សិក្សា និងការអប់រំតាមអនឡាញដោយឥតគិតថ្លៃ។',
                'eLearning College is a modern online learning platform designed to make useful learning resources easier to access. Explore the platform, learn at your own pace, and build knowledge from anywhere.',
                'eLearning College គឺជាវេទិកាសិក្សាតាមអនឡាញទំនើប ដែលធ្វើឱ្យធនធានសិក្សាងាយស្រួលក្នុងការចូលប្រើ។ អ្នកអាចរៀនតាមល្បឿនរបស់ខ្លួន និងបង្កើនចំណេះដឹងពីគ្រប់ទីកន្លែង។',1
            ],
            [
                'how-it-works','fa-solid fa-circle-play','HOW IT WORKS','របៀបសិក្សា',
                'How Online Learning Works','របៀបរៀនតាមអនឡាញ',
                'Create an account, discover learning materials, study online, and track your progress.',
                'បង្កើតគណនី ស្វែងរកមេរៀន រៀនតាមអនឡាញ និងតាមដានវឌ្ឍនភាព។',
                'Start with your account, discover the learning materials you need, study online, and return whenever you want to continue.',
                'ចាប់ផ្តើមពីគណនីរបស់អ្នក ស្វែងរកធនធានសិក្សាដែលត្រូវការ រៀនតាមអនឡាញ ហើយត្រឡប់មកបន្តរៀនបានគ្រប់ពេល។',2
            ],
            [
                'certificates','fa-solid fa-award','ACHIEVEMENTS','សមិទ្ធផល',
                'Certificates & Achievements','វិញ្ញាបនបត្រ និងសមិទ្ធផល',
                'Understand completion, certificates, achievements, and learning progress.',
                'ស្វែងយល់ពីការបញ្ចប់ វិញ្ញាបនបត្រ សមិទ្ធផល និងវឌ្ឍនភាពសិក្សា។',
                'Learn how course completion, certificates, and learning achievements can be part of your learning journey.',
                'ស្វែងយល់អំពីការបញ្ចប់ការសិក្សា វិញ្ញាបនបត្រ និងសមិទ្ធផលដែលអាចជាផ្នែកមួយនៃដំណើរសិក្សារបស់អ្នក។',3
            ],
            [
                'news','fa-solid fa-newspaper','LATEST INFORMATION','ព័ត៌មានថ្មីៗ',
                'News & Updates','ព័ត៌មាន និងការជូនដំណឹង',
                'Stay informed about announcements, platform updates, learning activities, and community news.',
                'ទទួលព័ត៌មានអំពីសេចក្តីជូនដំណឹង ការអាប់ដេត សកម្មភាពសិក្សា និងព័ត៌មានសហគមន៍។',
                'Stay up to date with announcements, platform changes, learning activities, and other information published by eLearning College.',
                'ទទួលព័ត៌មានថ្មីៗអំពីសេចក្តីជូនដំណឹង ការផ្លាស់ប្តូរវេទិកា សកម្មភាពសិក្សា និងព័ត៌មានផ្សេងៗពី eLearning College។',4
            ]
        ];
        foreach ($seedRows as $row) {
            $seed->execute($row);
        }
    }
} catch (Throwable $e) {
    error_log('More information compatibility migration: '.$e->getMessage());
}


/* Feature flag for defensive rendering if a hosting account does not allow ALTER TABLE. */
$FEATURED_SUPPORTED = db_column_exists($pdo, 'courses', 'featured');
$TEACHER_SKILL_SUPPORTED = db_column_exists($pdo, 'teachers', 'category_id');
$LESSON_OWNER_SUPPORTED = db_column_exists($pdo, 'lessons', 'created_by');
