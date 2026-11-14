<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_admin();

$admin_heading = 'More From eLearning College';
$admin_title = 'More Information';
$error = '';
$edit = null;

function more_upload_image(string $field, ?string $old = null): ?string {
    if (empty($_FILES[$field]['name'])) return $old;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new Exception('Image upload failed.');
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) throw new Exception('Image must be 5MB or smaller.');
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime])) throw new Exception('Only JPG, PNG, WEBP or GIF images are allowed.');
    $name = 'more_'.bin2hex(random_bytes(10)).'.'.$allowed[$mime];
    $dir = __DIR__.'/../uploads';
    if (!is_dir($dir)) mkdir($dir,0755,true);
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir.'/'.$name)) throw new Exception('Unable to save image.');
    if ($old && is_file($dir.'/'.$old)) @unlink($dir.'/'.$old);
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? null)) { http_response_code(419); exit('Invalid CSRF token.'); }
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($action === 'delete') {
            $q=$pdo->prepare("SELECT image FROM more_information WHERE id=?"); $q->execute([$id]); $old=$q->fetchColumn();
            $pdo->prepare("DELETE FROM more_information WHERE id=?")->execute([$id]);
            if ($old && is_file(__DIR__.'/../uploads/'.$old)) @unlink(__DIR__.'/../uploads/'.$old);
        } else {
            $slug = preg_replace('/[^a-z0-9-]+/i','-',strtolower(trim($_POST['slug'] ?? '')));
            $slug = trim($slug,'-');
            $title_en=trim($_POST['title_en']??'');
            if ($slug==='' || $title_en==='') throw new Exception('Slug and English title are required.');
            $icon=trim($_POST['icon']??'fa-solid fa-circle-info');
            $ey_en=trim($_POST['eyebrow_en']??''); $ey_kh=trim($_POST['eyebrow_kh']??'');
            $title_kh=trim($_POST['title_kh']??'');
            $desc_en=trim($_POST['description_en']??''); $desc_kh=trim($_POST['description_kh']??'');
            $content_en=trim($_POST['content_en']??''); $content_kh=trim($_POST['content_kh']??'');
            $sort=(int)($_POST['sort_order']??0);
            $status=in_array($_POST['status']??'published',['draft','published','inactive'],true)?$_POST['status']:'published';

            $old=null;
            if($action==='edit'){
                $q=$pdo->prepare("SELECT image FROM more_information WHERE id=?"); $q->execute([$id]); $old=$q->fetchColumn() ?: null;
            }
            $image=more_upload_image('image',$old);

            if($action==='edit'){
                $q=$pdo->prepare("UPDATE more_information SET slug=?,icon=?,eyebrow_en=?,eyebrow_kh=?,title_en=?,title_kh=?,description_en=?,description_kh=?,content_en=?,content_kh=?,image=?,sort_order=?,status=? WHERE id=?");
                $q->execute([$slug,$icon,$ey_en,$ey_kh,$title_en,$title_kh,$desc_en,$desc_kh,$content_en,$content_kh,$image,$sort,$status,$id]);
            } else {
                $q=$pdo->prepare("INSERT INTO more_information(slug,icon,eyebrow_en,eyebrow_kh,title_en,title_kh,description_en,description_kh,content_en,content_kh,image,sort_order,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $q->execute([$slug,$icon,$ey_en,$ey_kh,$title_en,$title_kh,$desc_en,$desc_kh,$content_en,$content_kh,$image,$sort,$status]);
            }
        }
        header('Location: more_information.php'); exit;
    } catch(Throwable $e) { $error=$e->getMessage(); }
}

if(isset($_GET['edit'])) {
    $q=$pdo->prepare("SELECT * FROM more_information WHERE id=?"); $q->execute([(int)$_GET['edit']]); $edit=$q->fetch();
}
$rows=$pdo->query("SELECT * FROM more_information ORDER BY sort_order ASC,id ASC")->fetchAll();
require __DIR__.'/layout.php';
?>
<div class="admincard more-admin-card">
    <div class="head">
        <div><span class="eyebrow"><?= $edit?'EDIT':'NEW' ?> CONTENT</span><h2><?= $edit?'Edit Information':'Add More Information' ?></h2><p class="page-description">Manage the cards shown in “More From eLearning College” and their detail pages.</p></div>
        <a class="btn outline" href="../more.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Preview</a>
    </div>
    <?php if($error): ?><div class="alert err"><?=e($error)?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif; ?>
        <div class="two">
            <label>Slug <input name="slug" value="<?=e($edit['slug']??'')?>" placeholder="about-the-college" required></label>
            <label>Font Awesome Icon <input name="icon" value="<?=e($edit['icon']??'fa-solid fa-circle-info')?>" placeholder="fa-solid fa-building-columns"></label>
        </div>
        <div class="two">
            <label>Eyebrow (English)<input name="eyebrow_en" value="<?=e($edit['eyebrow_en']??'')?>"></label>
            <label>Eyebrow (Khmer)<input name="eyebrow_kh" value="<?=e($edit['eyebrow_kh']??'')?>"></label>
        </div>
        <div class="two">
            <label>Title (English)<input name="title_en" value="<?=e($edit['title_en']??'')?>" required></label>
            <label>Title (Khmer)<input name="title_kh" value="<?=e($edit['title_kh']??'')?>"></label>
        </div>
        <div class="two">
            <label>Description (English)<textarea name="description_en" rows="4"><?=e($edit['description_en']??'')?></textarea></label>
            <label>Description (Khmer)<textarea name="description_kh" rows="4"><?=e($edit['description_kh']??'')?></textarea></label>
        </div>
        <div class="two">
            <label>Detail Content (English)<textarea name="content_en" rows="8"><?=e($edit['content_en']??'')?></textarea></label>
            <label>Detail Content (Khmer)<textarea name="content_kh" rows="8"><?=e($edit['content_kh']??'')?></textarea></label>
        </div>
        <div class="two">
            <label>Image (optional)<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"><?php if(!empty($edit['image'])): ?><small class="muted">Current: <?=e($edit['image'])?></small><?php endif; ?></label>
            <label>Sort Order<input type="number" name="sort_order" value="<?=e($edit['sort_order']??0)?>"></label>
        </div>
        <label>Status<select name="status"><option value="published" <?=($edit['status']??'published')==='published'?'selected':''?>>Published</option><option value="draft" <?=($edit['status']??'')==='draft'?'selected':''?>>Draft</option><option value="inactive" <?=($edit['status']??'')==='inactive'?'selected':''?>>Inactive</option></select></label>
        <button class="btn"><i class="fa-solid fa-floppy-disk"></i> <?=$edit?'Save Changes':'Add Information'?></button>
        <?php if($edit): ?><a class="btn outline" href="more_information.php">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="admincard">
    <div class="head"><div><span class="eyebrow">PUBLISHED CONTENT</span><h2>More Information Cards</h2></div></div>
    <div class="more-admin-grid">
        <?php foreach($rows as $r): ?>
        <article class="more-admin-item">
            <div class="more-admin-thumb">
                <?php if($r['image']): ?><img src="../uploads/<?=e($r['image'])?>" alt=""><?php else: ?><i class="<?=e($r['icon'])?>"></i><?php endif; ?>
            </div>
            <div class="more-admin-copy">
                <small><?=e($r['eyebrow_en'] ?: 'eLEARNING COLLEGE')?></small>
                <h3><?=e($r['title_en'])?></h3>
                <p><?=e(mb_strimwidth((string)$r['description_en'],0,115,'...'))?></p>
            </div>
            <div class="more-admin-actions">
                <a class="action-link" href="../more.php?slug=<?=rawurlencode($r['slug'])?>" target="_blank">View</a>
                <a class="action-link" href="more_information.php?edit=<?=$r['id']?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this information?')">
                    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>">
                    <button class="action-link danger" type="submit">Delete</button>
                </form>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__.'/end.php'; ?>
