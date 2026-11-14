<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/teacher_auth.php';

require_teacher();

$title   = 'Upload Lesson';
$heading = 'Upload Lesson';

$uid = (int)($_SESSION['user_id'] ?? 0);

$error   = '';
$success = '';

$uploadDir = __DIR__ . '/../uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/*
|--------------------------------------------------------------------------
| Get Teacher
|--------------------------------------------------------------------------
*/
$teacher = null;

$ts = $pdo->prepare("
    SELECT 
        t.*,
        c.name AS skill_name
    FROM teachers t
    LEFT JOIN categories c 
        ON c.id = t.category_id
    WHERE t.email = (
        SELECT email 
        FROM users 
        WHERE id = ? 
        LIMIT 1
    )
    LIMIT 1
");

$ts->execute([$uid]);

$teacher = $ts->fetch(PDO::FETCH_ASSOC);

$categoryId = (int)($teacher['category_id'] ?? 0);

$skillName = trim(
    (string)(
        $teacher['skill_name']
        ?? $teacher['specialization']
        ?? ''
    )
);


/*
|--------------------------------------------------------------------------
| Upload Function
|--------------------------------------------------------------------------
*/
function teacher_upload(
    array $file,
    string $type,
    string $dir
): string {

    if (
        !isset($file['error']) ||
        $file['error'] !== UPLOAD_ERR_OK
    ) {
        throw new RuntimeException(
            'Upload failed. Please check your PHP upload settings.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | File Size
    |--------------------------------------------------------------------------
    */
    $maxSize = ($type === 'video')
        ? 500 * 1024 * 1024
        : 30 * 1024 * 1024;


    /*
    |--------------------------------------------------------------------------
    | Allowed Extensions
    |--------------------------------------------------------------------------
    */
    $allowed = ($type === 'video')
        ? ['mp4', 'webm', 'ogg', 'mov']
        : ['pdf'];


    $originalName = $file['name'] ?? '';

    $extension = strtolower(
        pathinfo($originalName, PATHINFO_EXTENSION)
    );


    /*
    |--------------------------------------------------------------------------
    | Validate Size
    |--------------------------------------------------------------------------
    */
    if (($file['size'] ?? 0) <= 0) {

        throw new RuntimeException(
            'The uploaded file is empty.'
        );
    }


    if (($file['size'] ?? 0) > $maxSize) {

        if ($type === 'video') {

            throw new RuntimeException(
                'Video file is too large. Maximum size is 500MB.'
            );

        } else {

            throw new RuntimeException(
                'PDF file is too large. Maximum size is 30MB.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Extension
    |--------------------------------------------------------------------------
    */
    if (!in_array($extension, $allowed, true)) {

        if ($type === 'video') {

            throw new RuntimeException(
                'Video must be MP4, WebM, OGG or MOV.'
            );

        } else {

            throw new RuntimeException(
                'Only PDF files are allowed.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Uploaded File
    |--------------------------------------------------------------------------
    */
    if (
        empty($file['tmp_name']) ||
        !is_uploaded_file($file['tmp_name'])
    ) {
        throw new RuntimeException(
            'Invalid uploaded file.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Safe Filename
    |--------------------------------------------------------------------------
    */
    $prefix = ($type === 'video')
        ? 'teacher_video_'
        : 'teacher_pdf_';

    $newName =
        $prefix .
        bin2hex(random_bytes(12)) .
        '.' .
        $extension;


    /*
    |--------------------------------------------------------------------------
    | Move File
    |--------------------------------------------------------------------------
    */
    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $dir . $newName
        )
    ) {

        throw new RuntimeException(
            'Could not save the uploaded file. Please check the uploads folder permission.'
        );
    }

    return $newName;
}


/*
|--------------------------------------------------------------------------
| Load Courses
|
| IMPORTANT:
| If teacher category has courses -> show those courses.
| If there are no courses under category -> show active courses
| instead of leaving the SELECT empty.
|--------------------------------------------------------------------------
*/

$courses = [];


/*
|--------------------------------------------------------------------------
| First: Courses from Teacher Category
|--------------------------------------------------------------------------
*/
if ($categoryId > 0) {

    $coursesSt = $pdo->prepare("
        SELECT
            id,
            title,
            title_kh,
            category_id
        FROM courses
        WHERE status = 'active'
          AND category_id = ?
        ORDER BY title ASC
    ");

    $coursesSt->execute([$categoryId]);

    $courses = $coursesSt->fetchAll(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| FALLBACK
|
| Prevent:
| "Please select an item in the list."
|
| If teacher category has no courses, show all active courses.
|--------------------------------------------------------------------------
*/
if (empty($courses)) {

    $coursesSt = $pdo->query("
        SELECT
            id,
            title,
            title_kh,
            category_id
        FROM courses
        WHERE status = 'active'
        ORDER BY title ASC
    ");

    $courses = $coursesSt->fetchAll(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */
    if (!verify_csrf($_POST['csrf'] ?? null)) {

        http_response_code(419);

        exit('Invalid CSRF token.');
    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */
    $courseId = (int)($_POST['course_id'] ?? 0);

    $lessonTitle = trim(
        $_POST['title'] ?? ''
    );

    $description = trim(
        $_POST['description'] ?? ''
    );

    $duration = trim(
        $_POST['duration'] ?? ''
    );

    $videoUrl = trim(
        $_POST['video_url'] ?? ''
    );

    $pdfUrl = trim(
        $_POST['document_url'] ?? ''
    );


    $videoFile = '';

    $pdfFile = '';


    /*
    |--------------------------------------------------------------------------
    | Validate Course
    |--------------------------------------------------------------------------
    */
    if ($courseId <= 0) {

        $error = 'Please select a course.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Lesson Title
    |--------------------------------------------------------------------------
    */
    elseif ($lessonTitle === '') {

        $error = 'Please enter a lesson title.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Selected Course
    |--------------------------------------------------------------------------
    |
    | Course must be active.
    |
    |--------------------------------------------------------------------------
    */
    else {

        $check = $pdo->prepare("
            SELECT
                id,
                category_id
            FROM courses
            WHERE id = ?
              AND status = 'active'
            LIMIT 1
        ");

        $check->execute([$courseId]);

        $selectedCourse = $check->fetch(PDO::FETCH_ASSOC);


        if (!$selectedCourse) {

            $error = 'The selected course is not available.';

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Video URL
    |--------------------------------------------------------------------------
    */
    if (
        !$error &&
        $videoUrl !== '' &&
        !filter_var($videoUrl, FILTER_VALIDATE_URL)
    ) {

        $error = 'Video URL is not valid.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate PDF URL
    |--------------------------------------------------------------------------
    */
    if (
        !$error &&
        $pdfUrl !== '' &&
        !filter_var($pdfUrl, FILTER_VALIDATE_URL)
    ) {

        $error = 'PDF URL is not valid.';
    }


    /*
    |--------------------------------------------------------------------------
    | Require Video or PDF
    |--------------------------------------------------------------------------
    */
    $hasVideoFile =
        isset($_FILES['video']) &&
        !empty($_FILES['video']['name']);

    $hasPdfFile =
        isset($_FILES['document']) &&
        !empty($_FILES['document']['name']);


    if (
        !$error &&
        !$hasVideoFile &&
        !$hasPdfFile &&
        $videoUrl === '' &&
        $pdfUrl === ''
    ) {

        $error =
            'Please upload at least one Video or PDF file.';
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Video
    |--------------------------------------------------------------------------
    */
    try {

        if (
            !$error &&
            $hasVideoFile
        ) {

            $videoFile = teacher_upload(
                $_FILES['video'],
                'video',
                $uploadDir
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Upload PDF
        |--------------------------------------------------------------------------
        */
        if (
            !$error &&
            $hasPdfFile
        ) {

            $pdfFile = teacher_upload(
                $_FILES['document'],
                'pdf',
                $uploadDir
            );
        }


    } catch (RuntimeException $ex) {

        $error = $ex->getMessage();


        /*
        |--------------------------------------------------------------------------
        | Delete uploaded video if PDF failed
        |--------------------------------------------------------------------------
        */
        if (
            $videoFile &&
            is_file($uploadDir . $videoFile)
        ) {

            @unlink(
                $uploadDir . $videoFile
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Delete uploaded PDF if something failed
        |--------------------------------------------------------------------------
        */
        if (
            $pdfFile &&
            is_file($uploadDir . $pdfFile)
        ) {

            @unlink(
                $uploadDir . $pdfFile
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save Lesson
    |--------------------------------------------------------------------------
    */
    if (!$error) {

        /*
        |--------------------------------------------------------------------------
        | Lesson Order
        |--------------------------------------------------------------------------
        */
        $orderSt = $pdo->prepare("
            SELECT
                COALESCE(MAX(lesson_order), 0) + 1
            FROM lessons
            WHERE course_id = ?
        ");

        $orderSt->execute([
            $courseId
        ]);

        $order = max(
            1,
            (int)$orderSt->fetchColumn()
        );


        /*
        |--------------------------------------------------------------------------
        | Insert Lesson
        |--------------------------------------------------------------------------
        */
        $s = $pdo->prepare("
            INSERT INTO lessons
            (
                course_id,
                title,
                title_kh,
                description,
                video_url,
                video_file,
                document_url,
                document_file,
                duration,
                lesson_order,
                created_by,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


        $s->execute([
            $courseId,
            $lessonTitle,
            null,
            $description ?: null,
            $videoUrl ?: null,
            $videoFile ?: null,
            $pdfUrl ?: null,
            $pdfFile ?: null,
            $duration ?: null,
            $order,
            $uid,
            'active'
        ]);


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */
        header(
            'Location: lessons.php?saved=1'
        );

        exit;
    }
}


require 'layout.php';
?>


<section class="teacher-welcome compact-welcome">

    <div>

        <span class="teacher-eyebrow">
            ASSIGNED SKILL
        </span>

        <h2>
            <?= e($skillName ?: 'Teacher Learning Content') ?>
        </h2>

        <p>
            Teachers can upload Video and PDF learning materials.
            Course management remains with the Administrator.
        </p>

    </div>

    <i class="fa-solid fa-cloud-arrow-up"></i>

</section>


<section class="teacher-card">

    <div class="teacher-card-head">

        <div>

            <h2>
                Upload Learning Content
            </h2>

            <p>
                Select a course and upload a Video, PDF, or both.
            </p>

        </div>


        <a
            class="btn secondary"
            href="lessons.php"
        >
            <i class="fa-solid fa-arrow-left"></i>
            My Lessons
        </a>

    </div>


    <?php if ($error): ?>

        <div class="notice">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <form
        method="post"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>"
        >


        <div class="teacher-form-grid">


            <!-- COURSE -->

            <div class="teacher-field full">

                <label>
                    Course *
                </label>


                <select
                    name="course_id"
                    required
                >

                    <option value="">
                        -- Select Course --
                    </option>


                    <?php foreach ($courses as $c): ?>

                        <option
                            value="<?= (int)$c['id'] ?>"
                            <?= (
                                (int)($_POST['course_id'] ?? 0)
                                ===
                                (int)$c['id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($c['title']) ?>

                        </option>

                    <?php endforeach; ?>


                </select>


                <?php if (empty($courses)): ?>

                    <small class="field-help">
                        No active courses are available.
                        Please ask the Administrator to add a course.
                    </small>

                <?php elseif ($categoryId > 0): ?>

                    <small class="field-help">
                        Courses are loaded from your assigned teaching skill.
                    </small>

                <?php else: ?>

                    <small class="field-help">
                        Showing active courses because no teaching category
                        is currently assigned to this Teacher account.
                    </small>

                <?php endif; ?>


            </div>


            <!-- LESSON TITLE -->

            <div class="teacher-field">

                <label>
                    Lesson Title *
                </label>

                <input
                    type="text"
                    name="title"
                    required
                    value="<?= e($_POST['title'] ?? '') ?>"
                    placeholder="e.g. HTML Fundamentals"
                >

            </div>


            <!-- DURATION -->

            <div class="teacher-field">

                <label>
                    Duration
                </label>

                <input
                    type="text"
                    name="duration"
                    value="<?= e($_POST['duration'] ?? '') ?>"
                    placeholder="e.g. 45 Minutes"
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="teacher-field full">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    placeholder="What will students learn?"
                ><?= e($_POST['description'] ?? '') ?></textarea>

            </div>

        </div>


        <!-- MEDIA -->

        <div class="teacher-media-grid">


            <!-- VIDEO -->

            <div class="teacher-media-card video">

                <h3>

                    <i class="fa-solid fa-video"></i>

                    Video Lesson

                </h3>


                <p>
                    Upload MP4, WebM, OGG or MOV.
                    Maximum 500MB.
                </p>


                <input
                    type="file"
                    name="video"
                    accept=".mp4,.webm,.ogg,.mov,video/*"
                >


                <div class="teacher-upload-zone">

                    <label>

                        Video URL

                        <input
                            type="url"
                            name="video_url"
                            value="<?= e($_POST['video_url'] ?? '') ?>"
                            placeholder="https://youtube.com/..."
                            style="width:100%;margin-top:7px"
                        >

                    </label>

                </div>

            </div>


            <!-- PDF -->

            <div class="teacher-media-card pdf">

                <h3>

                    <i class="fa-solid fa-file-pdf"></i>

                    PDF Lesson

                </h3>


                <p>
                    Upload PDF file.
                    Maximum 30MB.
                </p>


                <input
                    type="file"
                    name="document"
                    accept=".pdf,application/pdf"
                >


                <div class="teacher-upload-zone">

                    <label>

                        PDF URL

                        <input
                            type="url"
                            name="document_url"
                            value="<?= e($_POST['document_url'] ?? '') ?>"
                            placeholder="https://example.com/lesson.pdf"
                            style="width:100%;margin-top:7px"
                        >

                    </label>

                </div>

            </div>


        </div>


        <!-- BUTTONS -->

        <div class="teacher-form-actions">

            <a
                class="btn secondary"
                href="lessons.php"
            >
                Cancel
            </a>


            <button
                class="btn"
                type="submit"
            >

                <i class="fa-solid fa-cloud-arrow-up"></i>

                Upload Lesson

            </button>

        </div>


    </form>

</section>


</main>
</div>

</body>
</html>