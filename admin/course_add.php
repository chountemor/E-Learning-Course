<?php
require_once __DIR__.'/../config/database.php';
require __DIR__.'/layout.php';

$error = '';
$categories = $pdo->query("SELECT id,name FROM categories WHERE status='active' ORDER BY name")->fetchAll();
$uploadDir = __DIR__.'/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function upload_course_media(array $file, string $type, string $uploadDir, string &$error): string {
    if (empty($file['name'])) return '';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please check the file size and PHP upload limits.';
        return '';
    }

    $max = $type === 'video' ? 500 * 1024 * 1024 : 30 * 1024 * 1024;
    $allowed = $type === 'video'
        ? ['mp4','webm','ogg','mov']
        : ['pdf'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (($file['size'] ?? 0) > $max || !in_array($ext, $allowed, true)) {
        $label = $type === 'video' ? 'MP4, WebM, OGG or MOV (max 500MB)' : 'PDF (max 30MB)';
        $error = ($type === 'video' ? 'Video' : 'PDF') . " must be {$label}.";
        return '';
    }

    $name = 'course_' . $type . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
        $error = 'Could not save the uploaded file.';
        return '';
    }
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? null)) {
    $title = trim($_POST['title'] ?? '');
    $title_kh = trim($_POST['title_kh'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $desc_kh = trim($_POST['description_kh'] ?? '');
    $dur = trim($_POST['duration'] ?? '');
    $level = $_POST['level'] ?? 'beginner';
    $status = $_POST['status'] ?? 'draft';
    $featured = !empty($_POST['featured']) ? 1 : 0;
    $video_url = trim($_POST['video_url'] ?? '');
    $pdf_url = trim($_POST['pdf_url'] ?? '');
    $img = '';
    $video_file = '';
    $pdf_file = '';
    $cat = '';

    if ($category_id > 0) {
        $cs = $pdo->prepare("SELECT name FROM categories WHERE id=? AND status='active'");
        $cs->execute([$category_id]);
        $cat = (string)$cs->fetchColumn();
    }

    if ($title === '' || $desc === '') $error = 'English course title and description are required.';
    if (!in_array($level, ['beginner','intermediate','advanced'], true)) $level = 'beginner';
    if (!in_array($status, ['draft','active','inactive'], true)) $status = 'draft';

    if (!$error && !empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($f['error'] || $f['size'] > 5*1024*1024 || !in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            $error = 'Cover image must be JPG, PNG or WebP and max 5MB.';
        } else {
            $img = 'course_' . bin2hex(random_bytes(10)) . '.' . $ext;
            if (!move_uploaded_file($f['tmp_name'], $uploadDir.$img)) $error = 'Could not save the cover image.';
        }
    }

    if (!$error && !empty($_FILES['video_file']['name'])) {
        $video_file = upload_course_media($_FILES['video_file'], 'video', $uploadDir, $error);
    }

    if (!$error && !empty($_FILES['pdf_file']['name'])) {
        $pdf_file = upload_course_media($_FILES['pdf_file'], 'pdf', $uploadDir, $error);
    }

    if (!$error) {
        $s = $pdo->prepare(
            'INSERT INTO courses
            (category_id,featured,title,title_kh,category,description,description_kh,duration,price,image,video_url,video_file,pdf_url,pdf_file,level,status)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $s->execute([
            $category_id ?: null, $featured, $title, $title_kh, $cat, $desc, $desc_kh,
            $dur, 0, $img, $video_url, $video_file, $pdf_url, $pdf_file, $level, $status
        ]);
        header('Location: course_edit.php?id='.$pdo->lastInsertId().'&saved=1');
        exit;
    }
}
?>
<div class="admincard form course-form-card">
    <div class="head">
        <div>
            <span class="eyebrow">NEW COURSE</span>
            <h2>Create a New Course</h2>
            <p class="page-description">Create one complete course with cover image, video and PDF. No Lessons are required.</p>
        </div>
        <a class="btn outline" href="courses.php">← Back</a>
    </div>

    <?php if($error): ?><div class="alert err"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">

        <div class="language-form-grid">
            <div class="language-panel">
                <div class="language-panel-title">🇬🇧 English <small>Required</small></div>
                <label>Course Title
                    <input name="title" placeholder="e.g. Full Stack Web Development" required>
                </label>
                <label>Description
                    <textarea name="description" rows="6" placeholder="What will students learn?" required></textarea>
                </label>
            </div>
            

        <div class="two">
            <label>Category
                <select name="category_id">
                    <option value="0">General</option>
                    <?php foreach($categories as $catRow): ?>
                        <option value="<?=$catRow['id']?>"><?=htmlspecialchars($catRow['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Duration
                <input name="duration" placeholder="e.g. 12 Weeks">
            </label>
        </div>

        <div class="three">
            <label>Level
                <select name="level">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </label>
            <label>Status
                <select name="status">
                    <option value="draft">Draft</option>
                    <option value="active">Active / Published</option>
                    <option value="inactive">Inactive</option>
                </select>
            </label>
            <label class="featured-toggle"><span>Homepage Featured</span><input type="checkbox" name="featured" value="1"><small class="field-help">Select for the homepage. Maximum 3 active featured courses.</small></label>
            <label>Cover Image
                <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/*">
                <small class="field-help">JPG/PNG/WebP • max 5MB</small>
            </label>
        </div>

        <div class="course-media-admin">
            <div class="course-media-admin-head">
                <div>
                    <span class="eyebrow">DIRECT COURSE CONTENT</span>
                    <h3>Video & PDF</h3>
                    <p>No Lessons needed. Students click View Course and open the content immediately.</p>
                </div>
            </div>

            <div class="media-admin-grid">
                <div class="media-admin-card">
                    <div class="media-admin-icon video"><i class="fa-solid fa-video"></i></div>
                    <div>
                        <h4>Course Video</h4>
                        <p>YouTube / Vimeo / direct MP4 URL</p>
                    </div>
                    <input name="video_url" type="url" placeholder="https://www.youtube.com/watch?v=...">
                    <div class="or-line"><span>OR upload video</span></div>
                    <input name="video_file" type="file" accept=".mp4,.webm,.ogg,.mov,video/*">
                    <small class="field-help">MP4/WebM/OGG/MOV • max 500MB. For long videos, a YouTube/Vimeo URL is recommended.</small>
                </div>

                <div class="media-admin-card">
                    <div class="media-admin-icon pdf"><i class="fa-solid fa-file-pdf"></i></div>
                    <div>
                        <h4>Course PDF</h4>
                        <p>PDF URL or upload a PDF file</p>
                    </div>
                    <input name="pdf_url" type="url" placeholder="https://example.com/course.pdf">
                    <div class="or-line"><span>OR upload PDF</span></div>
                    <input name="pdf_file" type="file" accept=".pdf,application/pdf">
                    <small class="field-help">PDF • max 30MB</small>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">Create Course →</button>
            <a class="btn outline" href="courses.php">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__.'/end.php';?>
