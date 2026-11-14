<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_admin();

$admin_title='Categories';
$admin_heading='Category Management';
$error='';
$success='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!verify_csrf($_POST['csrf']??null)){ http_response_code(419); exit('Invalid CSRF token.'); }

    $action=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    $name=trim($_POST['name']??'');
    $name_kh=trim($_POST['name_kh']??'');
    $icon=trim($_POST['icon']??'📚');
    $desc=trim($_POST['description']??'');
    $status=$_POST['status']??'active';

    if($action!=='delete' && $name==='') $error='English category name is required.';
    elseif(!in_array($status,['active','inactive'],true)) $status='active';
    elseif($action==='add'){
        $check=$pdo->prepare('SELECT id FROM categories WHERE name=? LIMIT 1');
        $check->execute([$name]);
        if($check->fetch()) $error='This category already exists.';
        else{
            $s=$pdo->prepare('INSERT INTO categories(name,name_kh,icon,description,status) VALUES(?,?,?,?,?)');
            $s->execute([$name,$name_kh,$icon ?: '📚',$desc,$status]);
            header('Location: categories.php?saved=1'); exit;
        }
    }
    elseif($action==='edit'){
        if($id<=0) $error='Invalid category.';
        else{
            $check=$pdo->prepare('SELECT id FROM categories WHERE name=? AND id<>? LIMIT 1');
            $check->execute([$name,$id]);
            if($check->fetch()) $error='Another category already uses this English name.';
            else{
                $s=$pdo->prepare('UPDATE categories SET name=?,name_kh=?,icon=?,description=?,status=? WHERE id=?');
                $s->execute([$name,$name_kh,$icon ?: '📚',$desc,$status,$id]);
                header('Location: categories.php?saved=1'); exit;
            }
        }
    }
    elseif($action==='delete'){
        if($id>0){
            // Keep courses safe: unlink them instead of deleting courses.
            $pdo->prepare('UPDATE courses SET category_id=NULL WHERE category_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
        }
        header('Location: categories.php?deleted=1'); exit;
    }
}

if(isset($_GET['saved'])) $success='Category saved successfully.';
if(isset($_GET['deleted'])) $success='Category deleted successfully. Courses were kept.';

$edit=null;
if(isset($_GET['edit'])){
    $s=$pdo->prepare('SELECT * FROM categories WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit=$s->fetch();
}

$rows=$pdo->query('SELECT c.*,COUNT(co.id) course_count FROM categories c LEFT JOIN courses co ON co.category_id=c.id GROUP BY c.id ORDER BY c.status DESC,c.name')->fetchAll();
require __DIR__.'/layout.php';
?>
<div class="admincard form category-manager-card">
    <div class="head">
        <div>
            <span class="eyebrow"><?= $edit?'EDIT CATEGORY':'NEW CATEGORY' ?></span>
            <h2><?= $edit?'Edit Skill / Category':'Add New Skill / Category' ?></h2>
            <p class="page-description">Create a new skill area for the Website's <b>Popular Categories</b>. It will also be available when creating courses.</p>
        </div>
    </div>

    <?php if($error): ?><div class="alert err"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">
        <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif; ?>

        <div class="category-form-grid">
            <label>
                English Skill / Category Name
                <input name="name" value="<?=htmlspecialchars($edit['name']??'')?>" placeholder="e.g. Graphic Design" required>
            </label>
            
            <label>
                Icon / Emoji
                <input name="icon" value="<?=htmlspecialchars($edit['icon']??'📚')?>" maxlength="20" placeholder="🎨">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="active" <?=($edit['status']??'active')==='active'?'selected':''?>>Active / Show on Website</option>
                    <option value="inactive" <?=($edit['status']??'')==='inactive'?'selected':''?>>Inactive / Hide</option>
                </select>
            </label>
        </div>

        <label>
            Description
            <textarea name="description" rows="3" placeholder="Short description of this skill/category..."><?=htmlspecialchars($edit['description']??'')?></textarea>
        </label>

        <div class="form-actions">
            <button class="btn" type="submit"><?=$edit?'Save Changes':'Add Skill / Category'?></button>
            <?php if($edit): ?><a class="btn outline" href="categories.php">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="admincard category-list-card">
    <div class="head">
        <div>
            <span class="eyebrow">WEBSITE CATEGORIES</span>
            <h2>Popular Categories</h2>
        </div>
        <span class="category-count"><?=count($rows)?> categories</span>
    </div>

    <div class="category-admin-grid">
        <?php foreach($rows as $r): ?>
            <div class="category-admin-item <?=$r['status']==='inactive'?'is-inactive':''?>">
                <div class="category-admin-icon"><?=htmlspecialchars($r['icon']??'📚')?></div>
                <div class="category-admin-info">
                    <h3><?=htmlspecialchars($r['name'])?></h3>
                    <?php if(!empty($r['name_kh'])): ?><div class="category-kh"><?=htmlspecialchars($r['name_kh'])?></div><?php endif; ?>
                    <p><?=htmlspecialchars($r['description']??'')?></p>
                    <div class="category-meta">
                        <span><?= (int)$r['course_count'] ?> course<?=((int)$r['course_count']===1?'':'s')?></span>
                        <span class="status-pill <?=$r['status']?>"><?=htmlspecialchars(ucfirst($r['status']))?></span>
                    </div>
                </div>
                <div class="category-actions">
                    <a class="action-link" href="categories.php?edit=<?=$r['id']?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this category? Courses will remain, but their category will be cleared.')">
                        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?=$r['id']?>">
                        <button class="action-link danger" type="submit">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(!$rows): ?><div class="empty-state">No categories yet. Add your first skill above.</div><?php endif; ?>
    </div>
</div>
<?php require __DIR__.'/end.php'; ?>
