<?php
require_once __DIR__.'/../config/database.php';
require __DIR__.'/layout.php';

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM courses WHERE id=?');
$s->execute([$id]);
$c = $s->fetch();
if (!$c) exit('Course not found');

$error = '';
$categories = $pdo->query("SELECT id,name FROM categories WHERE status='active' ORDER BY name")->fetchAll();
$uploadDir = __DIR__.'/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

function replace_course_upload(array $file, string $type, string $uploadDir, string &$error): string {
    if (empty($file['name'])) return '';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please check the PHP upload limits.';
        return '';
    }
    $max = $type === 'video' ? 500*1024*1024 : 30*1024*1024;
    $allowed = $type === 'video' ? ['mp4','webm','ogg','mov'] : ['pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (($file['size'] ?? 0) > $max || !in_array($ext,$allowed,true)) {
        $error = ($type === 'video')
            ? 'Video must be MP4, WebM, OGG or MOV and max 500MB.'
            : 'PDF must be PDF and max 30MB.';
        return '';
    }
    $name = 'course_'.$type.'_'.bin2hex(random_bytes(10)).'.'.$ext;
    if (!move_uploaded_file($file['tmp_name'],$uploadDir.$name)) {
        $error = 'Could not save the uploaded file.';
        return '';
    }
    return $name;
}
function remove_course_file(string $file, string $uploadDir): void {
    if ($file !== '' && is_file($uploadDir.$file)) @unlink($uploadDir.$file);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf($_POST['csrf']??null) && isset($_POST['save_course'])) {
    $title = trim($_POST['title']??'');
    $title_kh = trim($_POST['title_kh']??'');
    $category_id = (int)($_POST['category_id']??0);
    $desc = trim($_POST['description']??'');
    $desc_kh = trim($_POST['description_kh']??'');
    $dur = trim($_POST['duration']??'');
    $level = $_POST['level']??'beginner';
    $status = $_POST['status']??'active';
    $featured = !empty($_POST['featured']) ? 1 : 0;
    $video_url = trim($_POST['video_url']??'');
    $pdf_url = trim($_POST['pdf_url']??'');
    $img = $c['image']??'';
    $video_file = $c['video_file']??'';
    $pdf_file = $c['pdf_file']??'';
    $cat = '';

    if($category_id>0){
        $cs=$pdo->prepare("SELECT name FROM categories WHERE id=? AND status='active'");
        $cs->execute([$category_id]);
        $cat=(string)$cs->fetchColumn();
    }
    if($title===''||$desc==='') $error='English course title and description are required.';
    if(!in_array($level,['beginner','intermediate','advanced'],true)) $level='beginner';
    if(!in_array($status,['draft','active','inactive'],true)) $status='draft';
    if($featured && $status==='active') {
        $featuredCount=(int)$pdo->query("SELECT COUNT(*) FROM courses WHERE featured=1 AND status='active' AND id<>$id")->fetchColumn();
        if($featuredCount>=3) $error='Only 3 Featured Courses are allowed. Unfeature another course first.';
    }

    if(!$error && !empty($_FILES['image']['name'])){
        $f=$_FILES['image'];
        $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
        if($f['error']||$f['size']>5*1024*1024||!in_array($ext,['jpg','jpeg','png','webp'],true)){
            $error='Cover image must be JPG, PNG or WebP and max 5MB.';
        } else {
            $new='course_'.bin2hex(random_bytes(10)).'.'.$ext;
            if(move_uploaded_file($f['tmp_name'],$uploadDir.$new)){
                remove_course_file($img,$uploadDir);
                $img=$new;
            } else $error='Could not save the cover image.';
        }
    }

    if(!$error && !empty($_FILES['video_file']['name'])){
        $newVideo=replace_course_upload($_FILES['video_file'],'video',$uploadDir,$error);
        if(!$error && $newVideo){
            remove_course_file($video_file,$uploadDir);
            $video_file=$newVideo;
        }
    }

    if(!$error && !empty($_FILES['pdf_file']['name'])){
        $newPdf=replace_course_upload($_FILES['pdf_file'],'pdf',$uploadDir,$error);
        if(!$error && $newPdf){
            remove_course_file($pdf_file,$uploadDir);
            $pdf_file=$newPdf;
        }
    }

    if(!$error && isset($_POST['remove_video']) && $_POST['remove_video']==='1'){
        remove_course_file($video_file,$uploadDir);
        $video_file='';
    }
    if(!$error && isset($_POST['remove_pdf']) && $_POST['remove_pdf']==='1'){
        remove_course_file($pdf_file,$uploadDir);
        $pdf_file='';
    }

    if(!$error){
        $pdo->prepare(
            'UPDATE courses SET category_id=?,featured=?,title=?,title_kh=?,category=?,description=?,description_kh=?,duration=?,image=?,video_url=?,video_file=?,pdf_url=?,pdf_file=?,level=?,status=? WHERE id=?'
        )->execute([
            $category_id?:null,$featured,$title,$title_kh,$cat,$desc,$desc_kh,$dur,$img,
            $video_url,$video_file,$pdf_url,$pdf_file,$level,$status,$id
        ]);
        header('Location: course_edit.php?id='.$id.'&saved=1');
        exit;
    }
}
?>
<div class="admincard form course-form-card">
    <div class="head">
        <div>
            <span class="eyebrow">EDIT COURSE</span>
            <h2><?=htmlspecialchars($c['title'])?></h2>
            <p class="page-description">Manage the course as one complete learning page — image, video and PDF. Lessons are not required.</p>
        </div>
        <a class="btn outline" href="courses.php">← Back</a>
    </div>

    <?php if(!empty($_GET['saved'])): ?><div class="alert ok">✓ Course saved successfully.</div><?php endif; ?>
    <?php if($error): ?><div class="alert err"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">
        <input type="hidden" name="save_course" value="1">

        <div class="language-form-grid">
            <div class="language-panel">
                <div class="language-panel-title">🇬🇧 English <small>Required</small></div>
                <label>Course Title<input name="title" value="<?=htmlspecialchars($c['title'])?>" required></label>
                <label>Description<textarea name="description" rows="6" required><?=htmlspecialchars($c['description'])?></textarea></label>
            </div>
        </div>

        <div class="two">
            <label>Category<select name="category_id">
                <option value="0">General</option>
                <?php foreach($categories as $catRow): ?>
                    <option value="<?=$catRow['id']?>" <?=$c['category_id']==$catRow['id']?'selected':''?>><?=htmlspecialchars($catRow['name'])?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Duration<input name="duration" value="<?=htmlspecialchars($c['duration']??'')?>" placeholder="e.g. 12 Weeks"></label>
        </div>

        <div class="three">
            <label>Level<select name="level">
                <?php foreach(['beginner','intermediate','advanced'] as $v): ?>
                    <option value="<?=$v?>" <?=$c['level']===$v?'selected':''?>><?=ucfirst($v)?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Status<select name="status">
                <?php foreach(['draft','active','inactive'] as $v): ?>
                    <option value="<?=$v?>" <?=$c['status']===$v?'selected':''?>><?=ucfirst($v)?><?=$v==='active'?' / Published':''?></option>
                <?php endforeach; ?>
            </select></label>
            <label class="featured-toggle"><span>Homepage Featured</span><input type="checkbox" name="featured" value="1" <?=$c['featured']?'checked':''?>><small class="field-help">Maximum 3 active featured courses.</small></label>
            <label>Replace Cover Image<input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/*"><small class="field-help">Leave empty to keep current image.</small></label>
        </div>

        <div class="course-media-admin">
            <div class="course-media-admin-head">
                <div>
                    <span class="eyebrow">DIRECT COURSE CONTENT</span>
                    <h3>Video & PDF</h3>
                    <p>These files belong directly to this course. Students do not need to open Lessons.</p>
                </div>
                <a class="btn small" href="../course.php?id=<?=$id?>" target="_blank"><i class="fa-solid fa-eye"></i> Preview Course</a>
            </div>

            <div class="media-admin-grid">
                <div class="media-admin-card">
                    <div class="media-admin-icon video"><i class="fa-solid fa-video"></i></div>
                    <div>
                        <h4>Course Video</h4>
                        <p>YouTube / Vimeo / direct video URL or upload</p>
                    </div>
                    <input name="video_url" type="url" value="<?=htmlspecialchars($c['video_url']??'')?>" placeholder="https://www.youtube.com/watch?v=...">
                    <?php if(!empty($c['video_file'])): ?>
                        <div class="current-file"><i class="fa-solid fa-film"></i><span><?=htmlspecialchars($c['video_file'])?></span></div>
                        <label class="remove-file"><input type="checkbox" name="remove_video" value="1"> Remove uploaded video</label>
                    <?php endif; ?>
                    <div class="or-line"><span>OR replace/upload video</span></div>
                    <input name="video_file" type="file" accept=".mp4,.webm,.ogg,.mov,video/*">
                    <small class="field-help">MP4/WebM/OGG/MOV • max 500MB. Long videos work better with YouTube/Vimeo URL.</small>
                </div>

                <div class="media-admin-card">
                    <div class="media-admin-icon pdf"><i class="fa-solid fa-file-pdf"></i></div>
                    <div>
                        <h4>Course PDF</h4>
                        <p>PDF URL or uploaded PDF</p>
                    </div>
                    <input name="pdf_url" type="url" value="<?=htmlspecialchars($c['pdf_url']??'')?>" placeholder="https://example.com/course.pdf">
                    <?php if(!empty($c['pdf_file'])): ?>
                        <div class="current-file"><i class="fa-solid fa-file-pdf"></i><span><?=htmlspecialchars($c['pdf_file'])?></span></div>
                        <label class="remove-file"><input type="checkbox" name="remove_pdf" value="1"> Remove uploaded PDF</label>
                    <?php endif; ?>
                    <div class="or-line"><span>OR replace/upload PDF</span></div>
                    <input name="pdf_file" type="file" accept=".pdf,application/pdf">
                    <small class="field-help">PDF • max 30MB</small>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">💾 Save Course</button>
            <a class="btn outline" href="../course.php?id=<?=$id?>" target="_blank">👁 View Course</a>
        </div>
    </form>
</div>
<?php require __DIR__.'/end.php';?>
