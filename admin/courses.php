<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/layout.php';

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$category = trim($_GET['category'] ?? '');

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(c.title LIKE ? OR c.title_kh LIKE ? OR c.category LIKE ?)';
    $term = "%{$q}%";
    array_push($params,$term,$term,$term);
}
if (in_array($status,['draft','active','inactive'],true)) { $where[]='c.status=?'; $params[]=$status; }
if ($category !== '') { $where[]='c.category=?'; $params[]=$category; }

$sql="SELECT c.* FROM courses c";
if($where) $sql .= ' WHERE '.implode(' AND ',$where);
$sql .= ' ORDER BY c.created_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $courses=$stmt->fetchAll(PDO::FETCH_ASSOC);

$categories=$pdo->query("SELECT DISTINCT category FROM courses WHERE category IS NOT NULL AND category<>'' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$total=(int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$active=(int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status='active'")->fetchColumn();
$draft=(int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status='draft'")->fetchColumn();
$featuredCourses=(int)$pdo->query("SELECT COUNT(*) FROM courses WHERE featured=1 AND status='active'")->fetchColumn();
$mediaCourses=(int)$pdo->query("SELECT COUNT(*) FROM courses WHERE
    (video_url IS NOT NULL AND video_url <> '') OR (video_file IS NOT NULL AND video_file <> '')
    OR (pdf_url IS NOT NULL AND pdf_url <> '') OR (pdf_file IS NOT NULL AND pdf_file <> '')
")->fetchColumn();

function admin_status_class($s){ return $s==='active'?'active':($s==='draft'?'draft':'inactive'); }
function admin_level_label($s){ return ucfirst($s ?: 'beginner'); }
?>
<div class="admincard course-manager-hero">
    <div>
        <span class="eyebrow">COURSE MANAGEMENT</span>
        <h2>Manage Courses</h2>
        <p class="page-description">Create, edit and publish complete courses with image, video and PDF from one simple workspace.</p>
    </div>
    <a href="course_add.php" class="btn">＋ Add New Course</a>
</div>

<div class="admin-course-stats">
    <div><span>📚</span><b><?= $total ?></b><small>Total Courses</small></div>
    <div><span>🟢</span><b><?= $active ?></b><small>Published</small></div>
    <div><span>📝</span><b><?= $draft ?></b><small>Drafts</small></div>
    <div><span>⭐</span><b><?= $featuredCourses ?>/3</b><small>Featured Slots</small></div><div><span>🎬</span><b><?= $mediaCourses ?></b><small>Courses With Media</small></div>
</div>

<div class="admincard">
    <form class="course-toolbar" method="get">
        <div class="course-search-admin">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="q" value="<?=htmlspecialchars($q)?>" placeholder="Search title, Khmer title or category...">
        </div>
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?><option value="<?=htmlspecialchars($cat)?>" <?=$category===$cat?'selected':''?>><?=htmlspecialchars($cat)?></option><?php endforeach;?>
        </select>
        <select name="status">
            <option value="">All Status</option>
            <option value="active" <?=$status==='active'?'selected':''?>>Active</option>
            <option value="draft" <?=$status==='draft'?'selected':''?>>Draft</option>
            <option value="inactive" <?=$status==='inactive'?'selected':''?>>Inactive</option>
        </select>
        <button class="btn small" type="submit">Filter</button>
        <?php if($q||$category||$status): ?><a class="btn outline small" href="courses.php">Reset</a><?php endif;?>
    </form>

    <div class="course-manager-help">
        <span>💡 <b>Quick workflow:</b> Add Course → Image → Video/PDF → Publish → Students click View Course.</span>
    </div>

    <div class="tablewrap course-manager-table">
        <table>
            <thead><tr><th>Course</th><th>Category</th><th>Level</th><th>Content</th><th>Featured</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(!$courses): ?>
                <tr><td colspan="8" class="empty-state">📚 No courses match your filters.</td></tr>
            <?php else: foreach($courses as $course): ?>
                <tr>
                    <td>
                        <div class="table-course">
                            <?php if(!empty($course['image'])):?><img class="thumb" src="../uploads/<?=htmlspecialchars($course['image'])?>" alt="">
                            <?php else:?><div class="thumb-placeholder">📚</div><?php endif;?>
                            <div><b><?=htmlspecialchars($course['title'])?></b>
                                <?php if(!empty($course['title_kh'])):?><small><?=htmlspecialchars($course['title_kh'])?></small><?php endif;?>
                            </div>
                        </div>
                    </td>
                    <td><span class="table-chip"><?=htmlspecialchars($course['category']?:'General')?></span></td>
                    <td><span class="level-chip"><?=htmlspecialchars(admin_level_label($course['level']))?></span></td>
                    <td>
    <div class="course-content-tags">
        <?php if(!empty($course['video_file']) || !empty($course['video_url'])): ?><span class="tag video">🎬 Video</span><?php endif; ?>
        <?php if(!empty($course['pdf_file']) || !empty($course['pdf_url'])): ?><span class="tag pdf">📄 PDF</span><?php endif; ?>
        <?php if(empty($course['video_file']) && empty($course['video_url']) && empty($course['pdf_file']) && empty($course['pdf_url'])): ?><span class="tag">—</span><?php endif; ?>
    </div>
</td>
                    <td><?=!empty($course['featured']) && $course['status']==='active'?'<span class="status-pill active">★ Featured</span>':'<span class="table-chip">Not featured</span>'?></td>
                    <td><span class="status-pill <?=admin_status_class($course['status'])?>"><i></i><?=ucfirst($course['status'])?></span></td>
                    <td class="date-cell"><?=htmlspecialchars(date('M d, Y',strtotime($course['updated_at']??$course['created_at'])))?></td>
                    <td><div class="manager-actions">
                        <a class="action-link" href="course_edit.php?id=<?=(int)$course['id']?>">✏️ Edit</a>
                        <a class="action-link media" href="course_media.php?id=<?=(int)$course['id']?>">🎬 Media</a>
                        <a class="action-link view" href="../course.php?id=<?=(int)$course['id']?>" target="_blank">👁 View</a>
                        <form method="post" action="course_delete.php" onsubmit="return confirm('Delete this course?');">
                            <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">
                            <input type="hidden" name="id" value="<?=(int)$course['id']?>">
                            <button class="action-link danger" type="submit">🗑 Delete</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; endif;?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/end.php'; ?>
