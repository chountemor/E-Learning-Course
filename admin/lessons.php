<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$admin_title   = 'Lessons';
$admin_heading = 'Lesson Management';


/*
|--------------------------------------------------------------------------
| Search / Filter
|--------------------------------------------------------------------------
*/

$q         = trim($_GET['q'] ?? '');
$course_id = (int)($_GET['course_id'] ?? 0);
$status    = $_GET['status'] ?? '';

$where  = [];
$params = [];


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($q !== '') {

    $where[] = '(
        l.title LIKE ?
        OR l.title_kh LIKE ?
        OR l.description LIKE ?
    )';

    $search = '%' . $q . '%';

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}


/*
|--------------------------------------------------------------------------
| Course Filter
|--------------------------------------------------------------------------
*/

if ($course_id > 0) {

    $where[] = 'l.course_id = ?';

    $params[] = $course_id;
}


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if (in_array($status, ['active', 'inactive'], true)) {

    $where[] = 'l.status = ?';

    $params[] = $status;
}


/*
|--------------------------------------------------------------------------
| Load Lessons
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        l.*,
        c.title AS course_title,
        c.title_kh AS course_title_kh,
        c.category AS course_category,
        u.name AS teacher_name,
        u.email AS teacher_email

    FROM lessons l

    INNER JOIN courses c
        ON c.id = l.course_id

    LEFT JOIN users u
        ON u.id = l.created_by
";


if ($where) {

    $sql .= ' WHERE ' . implode(' AND ', $where);
}


$sql .= "
    ORDER BY
        c.title ASC,
        l.lesson_order ASC,
        l.id ASC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Courses for Filter
|--------------------------------------------------------------------------
*/

$courseStmt = $pdo->query("
    SELECT
        id,
        title,
        title_kh

    FROM courses

    ORDER BY title ASC
");

$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalLessons = (int)$pdo->query("
    SELECT COUNT(*)
    FROM lessons
")->fetchColumn();


$activeLessons = (int)$pdo->query("
    SELECT COUNT(*)
    FROM lessons
    WHERE status = 'active'
")->fetchColumn();


$inactiveLessons = (int)$pdo->query("
    SELECT COUNT(*)
    FROM lessons
    WHERE status = 'inactive'
")->fetchColumn();


$videoLessons = (int)$pdo->query("
    SELECT COUNT(*)
    FROM lessons
    WHERE
        video_url IS NOT NULL
        AND video_url <> ''

        OR

        video_file IS NOT NULL
        AND video_file <> ''
")->fetchColumn();


$pdfLessons = (int)$pdo->query("
    SELECT COUNT(*)
    FROM lessons
    WHERE
        document_url IS NOT NULL
        AND document_url <> ''

        OR

        document_file IS NOT NULL
        AND document_file <> ''
")->fetchColumn();


/*
|--------------------------------------------------------------------------
| Status Helper
|--------------------------------------------------------------------------
*/

function lesson_status_class(string $status): string
{
    return $status === 'active'
        ? 'active'
        : 'inactive';
}


/*
|--------------------------------------------------------------------------
| Start Admin Layout
|--------------------------------------------------------------------------
*/

require __DIR__ . '/layout.php';

?>

<div class="lessons-page">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="admincard lesson-hero">

        <div class="lesson-hero-content">

            <div class="eyebrow">
                LESSON MANAGEMENT
            </div>

            <h2>
                Manage Lessons
            </h2>

            <p class="page-description">
                Create, organize, edit and manage lessons for every course.
            </p>

        </div>


        <div class="lesson-hero-actions">

            <?php if ($courses): ?>

                <a
                    href="lesson_add.php?course_id=<?= (int)$courses[0]['id'] ?>"
                    class="btn"
                >
                    <i class="fa-solid fa-plus"></i>

                    Add New Lesson
                </a>

            <?php else: ?>

                <a
                    href="course_add.php"
                    class="btn"
                >
                    <i class="fa-solid fa-plus"></i>

                    Create Course First
                </a>

            <?php endif; ?>

        </div>

    </section>



    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <section class="lesson-stats">


        <div class="lesson-stat-card">

            <div class="lesson-stat-icon orange">
                <i class="fa-solid fa-clapperboard"></i>
            </div>

            <div>

                <span>
                    Total Lessons
                </span>

                <strong>
                    <?= $totalLessons ?>
                </strong>

            </div>

        </div>



        <div class="lesson-stat-card">

            <div class="lesson-stat-icon green">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div>

                <span>
                    Active
                </span>

                <strong>
                    <?= $activeLessons ?>
                </strong>

            </div>

        </div>



        <div class="lesson-stat-card">

            <div class="lesson-stat-icon gray">
                <i class="fa-solid fa-circle-pause"></i>
            </div>

            <div>

                <span>
                    Inactive
                </span>

                <strong>
                    <?= $inactiveLessons ?>
                </strong>

            </div>

        </div>



        <div class="lesson-stat-card">

            <div class="lesson-stat-icon purple">
                <i class="fa-solid fa-video"></i>
            </div>

            <div>

                <span>
                    Video Lessons
                </span>

                <strong>
                    <?= $videoLessons ?>
                </strong>

            </div>

        </div>



        <div class="lesson-stat-card">

            <div class="lesson-stat-icon blue">
                <i class="fa-solid fa-file-pdf"></i>
            </div>

            <div>

                <span>
                    PDF Lessons
                </span>

                <strong>
                    <?= $pdfLessons ?>
                </strong>

            </div>

        </div>


    </section>



    <!-- =====================================================
         MAIN LESSON TABLE
    ====================================================== -->

    <section class="admincard lesson-manager">


        <!-- HEADER -->

        <div class="lesson-manager-header">

            <div>

                <span class="eyebrow">
                    ALL LESSONS
                </span>

                <h2>
                    Lesson Library
                </h2>

                <p>
                    Manage all lessons from one workspace.
                </p>

            </div>


            <div class="lesson-total">

                <strong>
                    <?= count($lessons) ?>
                </strong>

                <span>
                    Results
                </span>

            </div>

        </div>



        <!-- =================================================
             FILTER
        ================================================== -->

        <form
            method="get"
            class="lesson-filter"
        >


            <!-- SEARCH -->

            <div class="lesson-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="q"
                    value="<?= htmlspecialchars($q) ?>"
                    placeholder="Search lesson title..."
                >

            </div>



            <!-- COURSE -->

            <select name="course_id">

                <option value="">
                    All Courses
                </option>

                <?php foreach ($courses as $course): ?>

                    <option
                        value="<?= (int)$course['id'] ?>"
                        <?= $course_id === (int)$course['id']
                            ? 'selected'
                            : '' ?>
                    >

                        <?= htmlspecialchars($course['title']) ?>

                    </option>

                <?php endforeach; ?>

            </select>



            <!-- STATUS -->

            <select name="status">

                <option value="">
                    All Status
                </option>

                <option
                    value="active"
                    <?= $status === 'active' ? 'selected' : '' ?>
                >
                    Active
                </option>

                <option
                    value="inactive"
                    <?= $status === 'inactive' ? 'selected' : '' ?>
                >
                    Inactive
                </option>

            </select>



            <!-- FILTER -->

            <button
                type="submit"
                class="btn small"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>



            <!-- RESET -->

            <?php if ($q || $course_id || $status): ?>

                <a
                    href="lessons.php"
                    class="btn outline small"
                >

                    Reset

                </a>

            <?php endif; ?>


        </form>



        <!-- =================================================
             QUICK WORKFLOW
        ================================================== -->

        <div class="lesson-workflow">

            <span class="workflow-icon">
                💡
            </span>

            <div>

                <strong>
                    Quick workflow
                </strong>

                <span>
                    Select Course →
                    Add Lesson →
                    Upload Video/PDF →
                    Set Order →
                    Publish
                </span>

            </div>

        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="tablewrap lesson-table">

            <table>

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            LESSON
                        </th>

                        <th>
                            COURSE
                        </th>

                        <th>
                            CONTENT
                        </th>

                        <th>
                            DURATION
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            UPDATED
                        </th>

                        <th>
                            ACTIONS
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!$lessons): ?>


                    <tr>

                        <td
                            colspan="8"
                            class="lesson-empty"
                        >

                            <div class="lesson-empty-icon">
                                🎬
                            </div>

                            <h3>
                                No lessons found
                            </h3>

                            <p>
                                Try another search or create a new lesson.
                            </p>

                            <?php if ($courses): ?>

                                <a
                                    href="lesson_add.php?course_id=<?= (int)$courses[0]['id'] ?>"
                                    class="btn small"
                                >
                                    + Add Lesson
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($lessons as $lesson): ?>


                        <tr>


                            <!-- ORDER -->

                            <td>

                                <div class="lesson-order">

                                    <?= (int)$lesson['lesson_order'] ?>

                                </div>

                            </td>



                            <!-- LESSON -->

                            <td>

                                <div class="lesson-info">


                                    <div class="lesson-icon">

                                        <?php if (
                                            !empty($lesson['video_file']) ||
                                            !empty($lesson['video_url'])
                                        ): ?>

                                            🎬

                                        <?php elseif (
                                            !empty($lesson['document_file']) ||
                                            !empty($lesson['document_url'])
                                        ): ?>

                                            📄

                                        <?php else: ?>

                                            📚

                                        <?php endif; ?>

                                    </div>


                                    <div>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $lesson['title']
                                            ) ?>

                                        </strong>


                                        <?php if (!empty($lesson['title_kh'])): ?>

                                            <small>

                                                <?= htmlspecialchars(
                                                    $lesson['title_kh']
                                                ) ?>

                                            </small>

                                        <?php endif; ?>


                                        <small class="lesson-id">

                                            Lesson #<?= (int)$lesson['id'] ?>

                                        </small>

                                    </div>


                                </div>

                            </td>



                            <!-- COURSE -->

                            <td>

                                <div class="lesson-course">

                                    <strong>

                                        <?= htmlspecialchars(
                                            $lesson['course_title']
                                        ) ?>

                                    </strong>


                                    <?php if (!empty($lesson['course_category'])): ?>

                                        <span>

                                            <?= htmlspecialchars(
                                                $lesson['course_category']
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </div>

                            </td>



                            <!-- UPLOADED BY -->

                            <td>

                                <div class="lesson-course">

                                    <?php if (!empty($lesson['teacher_name'])): ?>
                                        <strong><?= htmlspecialchars($lesson['teacher_name']) ?></strong>
                                        <?php if (!empty($lesson['teacher_email'])): ?>
                                            <span><?= htmlspecialchars($lesson['teacher_email']) ?></span>
                                        <?php endif; ?>
                                    <?php elseif (!empty($lesson['created_by'])): ?>
                                        <span>User #<?= (int)$lesson['created_by'] ?></span>
                                    <?php else: ?>
                                        <span>Administrator</span>
                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- CONTENT -->

                            <td>

                                <div class="lesson-content-icons">


                                    <?php if (!empty($lesson['video_file'])): ?>
                                        <a class="content-badge video" href="../lesson.php?id=<?= (int)$lesson['id'] ?>" target="_blank" title="Open uploaded Video">
                                            <i class="fa-solid fa-video"></i> Video File
                                        </a>
                                    <?php elseif (!empty($lesson['video_url'])): ?>
                                        <a class="content-badge video" href="<?= htmlspecialchars($lesson['video_url']) ?>" target="_blank" rel="noopener noreferrer" title="Open Video URL">
                                            <i class="fa-solid fa-link"></i> Video URL
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($lesson['document_file'])): ?>
                                        <a class="content-badge pdf" href="../lesson.php?id=<?= (int)$lesson['id'] ?>" target="_blank" title="Open uploaded PDF">
                                            <i class="fa-solid fa-file-pdf"></i> PDF File
                                        </a>
                                    <?php elseif (!empty($lesson['document_url'])): ?>
                                        <a class="content-badge pdf" href="<?= htmlspecialchars($lesson['document_url']) ?>" target="_blank" rel="noopener noreferrer" title="Open PDF URL">
                                            <i class="fa-solid fa-link"></i> PDF URL
                                        </a>
                                    <?php endif; ?>


                                    <?php if (
                                        empty($lesson['video_file']) &&
                                        empty($lesson['video_url']) &&
                                        empty($lesson['document_file']) &&
                                        empty($lesson['document_url'])
                                    ): ?>

                                        <span class="no-content">
                                            No Content
                                        </span>

                                    <?php endif; ?>


                                </div>

                            </td>



                            <!-- DURATION -->

                            <td>

                                <span class="lesson-duration">

                                    <?php if (!empty($lesson['duration'])): ?>

                                        <i class="fa-regular fa-clock"></i>

                                        <?= htmlspecialchars(
                                            $lesson['duration']
                                        ) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>

                                </span>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <span
                                    class="lesson-status <?= lesson_status_class($lesson['status']) ?>"
                                >

                                    <i></i>

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $lesson['status']
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- UPDATED -->

                            <td>

                                <span class="lesson-date">

                                    <?= htmlspecialchars(
                                        date(
                                            'M d, Y',
                                            strtotime(
                                                $lesson['updated_at']
                                                ?? $lesson['created_at']
                                            )
                                        )
                                    ) ?>

                                </span>

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div class="lesson-actions">


                                    <!-- VIEW -->

                                    <a
                                        href="../lesson.php?id=<?= (int)$lesson['id'] ?>"
                                        class="lesson-action view"
                                        target="_blank"
                                        title="View Lesson"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                    </a>



                                    <!-- EDIT -->

                                    <a
                                        href="lesson_edit.php?id=<?= (int)$lesson['id'] ?>"
                                        class="lesson-action edit"
                                        title="Edit Lesson"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                    </a>



                                    <!-- DELETE -->

                                    <form
                                        method="post"
                                        action="lesson_delete.php"
                                        onsubmit="return confirm('Are you sure you want to delete this lesson?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= htmlspecialchars(
                                                csrf_token()
                                            ) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$lesson['id'] ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="lesson-action delete"
                                            title="Delete Lesson"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    </form>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </section>


</div>


<?php require __DIR__ . '/end.php'; ?>