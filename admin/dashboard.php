<?php

require_once __DIR__ . '/../config/database.php';

$admin_title   = 'Dashboard';
$admin_heading = 'Dashboard';

require __DIR__ . '/layout.php';

/*
|--------------------------------------------------------------------------
| DASHBOARD COUNTS
|--------------------------------------------------------------------------
*/

$courses = (int) $pdo
    ->query("SELECT COUNT(*) FROM courses")
    ->fetchColumn();

$lessons = (int) $pdo
    ->query("SELECT COUNT(*) FROM lessons")
    ->fetchColumn();

$students = (int) $pdo
    ->query("SELECT COUNT(*) FROM users WHERE role = 'student'")
    ->fetchColumn();

$admins = (int) $pdo
    ->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")
    ->fetchColumn();

$categories = (int) $pdo
    ->query("SELECT COUNT(*) FROM categories")
    ->fetchColumn();

$enrollments = (int) $pdo
    ->query("SELECT COUNT(*) FROM enrollments")
    ->fetchColumn();


/*
|--------------------------------------------------------------------------
| RECENT COURSES
|--------------------------------------------------------------------------
*/

$recent = $pdo->query("
    SELECT
        c.id,
        c.title,
        c.image,
        c.created_at,
        COUNT(l.id) AS lesson_count
    FROM courses c
    LEFT JOIN lessons l
        ON l.course_id = c.id
    GROUP BY
        c.id,
        c.title,
        c.image,
        c.created_at
    ORDER BY c.created_at DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- =====================================================
     WELCOME
====================================================== -->

<section class="dashboard-welcome">

    <div>
        <span class="eyebrow">OVERVIEW</span>

        <h2>
            Welcome back,
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> 👋
        </h2>

        <p>
            Manage your eLearning College website, courses,
            lessons, students and content from one place.
        </p>
    </div>

    <a href="course_add.php" class="btn btn-primary">
        ＋ Add New Course
    </a>

</section>


<!-- =====================================================
     STATISTICS
====================================================== -->

<section class="stats-grid">

    <!-- Courses -->
    <div class="stat-card">

        <div class="stat-top">
            <div class="stat-icon">📚</div>

            <span class="stat-label">COURSES</span>
        </div>

        <h3><?= $courses ?></h3>

        <p>Total courses</p>

        <a href="courses.php" class="stat-link">
            Manage courses →
        </a>

    </div>


    <!-- Lessons -->
    <div class="stat-card">

        <div class="stat-top">
            <div class="stat-icon">🎬</div>

            <span class="stat-label">LESSONS</span>
        </div>

        <h3><?= $lessons ?></h3>

        <p>Video & PDF lessons</p>

        <a href="lessons.php" class="stat-link">
            Manage lessons →
        </a>

    </div>


    <!-- Students -->
    <div class="stat-card">

        <div class="stat-top">
            <div class="stat-icon">👨‍🎓</div>

            <span class="stat-label">STUDENTS</span>
        </div>

        <h3><?= $students ?></h3>

        <p>Student accounts</p>

        <a href="users.php" class="stat-link">
            Manage students →
        </a>

    </div>


    <!-- Enrollments -->
    <div class="stat-card">

        <div class="stat-top">
            <div class="stat-icon">📈</div>

            <span class="stat-label">ENROLLMENTS</span>
        </div>

        <h3><?= $enrollments ?></h3>

        <p>Recorded enrollments</p>

        <a href="enrollments.php" class="stat-link">
            View enrollments →
        </a>

    </div>


    <!-- Categories -->
    <div class="stat-card">

        <div class="stat-top">
            <div class="stat-icon">🗂️</div>

            <span class="stat-label">CATEGORIES</span>
        </div>

        <h3><?= $categories ?></h3>

        <p>Course categories</p>

        <a href="categories.php" class="stat-link">
            Manage categories →
        </a>

    </div>


    <!-- Admins -->
    <div class="stat-card">

        <div class="stat-top">
            <div class="stat-icon">🛡️</div>

            <span class="stat-label">ADMINS</span>
        </div>

        <h3><?= $admins ?></h3>

        <p>Administrator accounts</p>

        <a href="users.php" class="stat-link">
            Manage admins →
        </a>

    </div>

</section>


<!-- =====================================================
     QUICK ACTIONS
====================================================== -->

<section class="admincard quick-card">

    <div class="section-head">

        <div>
            <span class="eyebrow">QUICK ACTIONS</span>

            <h2>Add New Data</h2>

            <p>
                Quickly add information to your website.
            </p>
        </div>

    </div>


    <div class="quick-grid">

        <!-- Add Course -->
        <a href="course_add.php" class="quick-action">

            <div class="quick-icon">📚</div>

            <div>
                <strong>Add Course</strong>

                <span>
                    Create a new course
                </span>
            </div>

            <b>→</b>

        </a>


        <!-- Add Lesson -->
        <a href="lesson_add.php" class="quick-action">

            <div class="quick-icon">🎬</div>

            <div>
                <strong>Add Lesson</strong>

                <span>
                    Add video or PDF lesson
                </span>
            </div>

            <b>→</b>

        </a>


        <!-- Category -->
        <a href="categories.php" class="quick-action">

            <div class="quick-icon">🗂️</div>

            <div>
                <strong>Add Category</strong>

                <span>
                    Create course category
                </span>
            </div>

            <b>→</b>

        </a>


        <!-- User -->
        <a href="users.php" class="quick-action">

            <div class="quick-icon">👥</div>

            <div>
                <strong>Manage Users</strong>

                <span>
                    Students & administrators
                </span>
            </div>

            <b>→</b>

        </a>


        <!-- Teacher -->
        <a href="people.php?type=teachers" class="quick-action">

            <div class="quick-icon">👨‍🏫</div>

            <div>
                <strong>Teachers</strong>

                <span>
                    Manage teachers
                </span>
            </div>

            <b>→</b>

        </a>


        <!-- News -->
        <a href="news.php" class="quick-action">

            <div class="quick-icon">📰</div>

            <div>
                <strong>Add News</strong>

                <span>
                    Publish website news
                </span>
            </div>

            <b>→</b>

        </a>

    </div>

</section>


<!-- =====================================================
     RECENT COURSES
====================================================== -->

<section class="admincard">

    <div class="section-head">

        <div>
            <span class="eyebrow">COURSE MANAGEMENT</span>

            <h2>Recent Courses</h2>

            <p>
                Recently created courses on your website.
            </p>
        </div>

        <a href="courses.php" class="btn btn-outline">
            View All Courses →
        </a>

    </div>


    <?php if ($recent): ?>

        <div class="course-table">

            <!-- Table Header -->
            <div class="course-table-head">

                <span>COURSE</span>
                <span>LESSONS</span>
                <span>CREATED</span>
                <span>ACTION</span>

            </div>


            <?php foreach ($recent as $course): ?>

                <div class="course-table-row">

                    <!-- Course -->
                    <div class="course-info">

                        <?php if (!empty($course['image'])): ?>

                            <img
                                src="../uploads/<?= htmlspecialchars($course['image']) ?>"
                                alt="<?= htmlspecialchars($course['title']) ?>"
                                class="course-thumb"
                            >

                        <?php else: ?>

                            <div class="course-thumb placeholder">
                                📚
                            </div>

                        <?php endif; ?>


                        <div>

                            <strong>
                                <?= htmlspecialchars($course['title']) ?>
                            </strong>

                            <small>
                                Course ID #<?= (int) $course['id'] ?>
                            </small>

                        </div>

                    </div>


                    <!-- Lessons -->
                    <div>
                        <span class="table-badge">
                            🎬 <?= (int) $course['lesson_count'] ?> lessons
                        </span>
                    </div>


                    <!-- Created -->
                    <div class="created-date">

                        <?= !empty($course['created_at'])
                            ? date('d M Y', strtotime($course['created_at']))
                            : '-'
                        ?>

                    </div>


                    <!-- Actions -->
                    <div class="row-actions">

                        <a
                            href="course_edit.php?id=<?= (int) $course['id'] ?>"
                            class="action-edit"
                        >
                            Edit
                        </a>

                        <a
                            href="course_edit.php?id=<?= (int) $course['id'] ?>"
                            class="action-view"
                        >
                            View
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <div class="empty-icon">📚</div>

            <h3>No Courses Yet</h3>

            <p>
                Start by creating your first course.
            </p>

            <a href="course_add.php" class="btn btn-primary">
                ＋ Create First Course
            </a>

        </div>

    <?php endif; ?>

</section>


<!-- =====================================================
     MANAGEMENT SHORTCUTS
====================================================== -->

<section class="admincard management-card">

    <div class="section-head">

        <div>
            <span class="eyebrow">MANAGEMENT</span>

            <h2>Website Management</h2>
        </div>

    </div>


    <div class="management-grid">

        <a href="courses.php">
            <span>📚</span>
            <div>
                <strong>Courses</strong>
                <small><?= $courses ?> total</small>
            </div>
        </a>


        <a href="lessons.php">
            <span>🎬</span>
            <div>
                <strong>Lessons</strong>
                <small><?= $lessons ?> total</small>
            </div>
        </a>


        <a href="categories.php">
            <span>🗂️</span>
            <div>
                <strong>Categories</strong>
                <small><?= $categories ?> total</small>
            </div>
        </a>


        <a href="users.php">
            <span>👥</span>
            <div>
                <strong>Users</strong>
                <small><?= $students + $admins ?> accounts</small>
            </div>
        </a>


        <a href="news.php">
            <span>📰</span>
            <div>
                <strong>News</strong>
                <small>Website content</small>
            </div>
        </a>


        <a href="/" title="View Website">
            <span>🌐</span>
            <div>
                <strong>Website</strong>
                <small>Open public website</small>
            </div>
        </a>

    </div>

</section>
<?php require __DIR__ . '/end.php'; ?>