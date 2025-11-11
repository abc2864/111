<?php
require_once 'config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 处理表单提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = trim($_POST['keyword']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($keyword)) {
        $message = '关键词不能为空';
    } elseif (!in_array($type, ['sender', 'content'])) {
        $message = '无效的关键词类型';
    } else {
        try {
            $pdo = getDBConnection();
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // 更新关键词
                $stmt = $pdo->prepare("UPDATE keywords SET keyword = ?, type = ?, description = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$keyword, $type, $description, $is_active, $_POST['id']]);
                $message = '关键词更新成功';
            } else {
                // 添加新关键词
                $stmt = $pdo->prepare("INSERT INTO keywords (keyword, type, description, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$keyword, $type, $description, $is_active]);
                $message = '关键词添加成功';
            }
        } catch (PDOException $e) {
            $message = '操作失败: ' . $e->getMessage();
        }
    }
}

// 处理删除请求
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pdo = null;
    $message = '';
    try {
        $pdo = getDBConnection();
        
        // 开始事务
        $pdo->beginTransaction();
        
        // 获取要删除的关键词ID
        $delete_id = (int)$_GET['id'];
        
        // 删除指定的关键词
        $stmt = $pdo->prepare("DELETE FROM keywords WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        // 重新分配剩余关键词的ID，使其连续
        // 首先获取所有剩余关键词，按ID排序
        $stmt = $pdo->query("SELECT id FROM keywords ORDER BY id ASC");
        $remaining_keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 为每个关键词分配新的连续ID
        $new_id = 1;
        foreach ($remaining_keywords as $keyword) {
            if ($keyword['id'] != $new_id) {
                $stmt = $pdo->prepare("UPDATE keywords SET id = ? WHERE id = ?");
                $stmt->execute([$new_id, $keyword['id']]);
            }
            $new_id++;
        }
        
        // 重置自增计数器（注意：ALTER TABLE不支持预处理语句的参数绑定）
        // 使用exec语句避免prepare可能引起的问题
        $pdo->exec("ALTER TABLE keywords AUTO_INCREMENT = " . $new_id);
        
        // 提交事务
        $pdo->commit();
        
        $message = '关键词删除成功，ID已重新排列';
    } catch (PDOException $e) {
        // 只有在有活动事务时才回滚
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = '删除失败: ' . $e->getMessage();
    } catch (Exception $e) {
        // 处理其他可能的异常
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = '删除失败: ' . $e->getMessage();
    }
    
    // 删除后重定向到关键词列表页面，避免重复提交
    header('Location: keywords.php?message=' . urlencode($message));
    exit;
}

// 获取关键词列表，按ID升序排列
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM keywords ORDER BY id ASC");
    $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $keywords = [];
    $message = '获取关键词列表失败: ' . $e->getMessage();
}

// 获取要编辑的关键词
$editKeyword = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM keywords WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $editKeyword = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '获取关键词信息失败: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关键词管理 - 取件码规则管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="mobile.css" rel="stylesheet">
    <style>
        .table th {
            white-space: nowrap;
        }
        .badge {
            font-size: 0.75em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">取件码规则管理系统</a>
            <div class="navbar-nav">
                <a class="nav-link" href="rules.php">规则管理</a>
                <a class="nav-link active" href="keywords.php">关键词管理</a>
                <a class="nav-link" href="export.php">导出JSON</a>
                <a class="nav-link" href="api.php?action=get_rules" target="_blank">API接口</a>
                <a class="nav-link" href="api_keys.php">API密钥</a>
                <a class="nav-link" href="info.php">系统信息</a>
            </div>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="nav-link" href="change_password.php">修改密码</a>
                <a class="nav-link" href="logout.php">登出</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>关键词管理</h2>
                
                <?php if ($message || isset($_GET['message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message ?? $_GET['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo $editKeyword ? '编辑关键词' : '添加新关键词'; ?></h5>
                        <?php if ($editKeyword): ?>
                            <a href="keywords.php" class="btn btn-sm btn-outline-secondary">添加新关键词</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editKeyword): ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editKeyword['id']); ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="keyword" class="form-label">关键词</label>
                                <input type="text" class="form-control" id="keyword" name="keyword" 
                                       value="<?php echo $editKeyword ? htmlspecialchars($editKeyword['keyword']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type" class="form-label">类型</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="sender" <?php echo ($editKeyword && $editKeyword['type'] === 'sender') ? 'selected' : ''; ?>>发件人</option>
                                    <option value="content" <?php echo ($editKeyword && $editKeyword['type'] === 'content') ? 'selected' : ''; ?>>内容</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">描述</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $editKeyword ? htmlspecialchars($editKeyword['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       <?php echo (!$editKeyword || $editKeyword['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">启用关键词</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $editKeyword ? '更新关键词' : '添加关键词'; ?></button>
                            <?php if ($editKeyword): ?>
                                <a href="keywords.php" class="btn btn-secondary">取消</a>
                            <?php endif; ?>
                        </form>
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <strong>操作提示：</strong>
                                <?php if ($editKeyword): ?>
                                    点击"更新关键词"保存修改，点击"取消"返回关键词列表。
                                <?php else: ?>
                                    填写表单后点击"添加关键词"创建新关键词。
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">关键词列表</h5>
                        <span class="badge bg-secondary"><?php echo count($keywords); ?> 个关键词</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($keywords)): ?>
                            <p>暂无关键词数据</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>关键词</th>
                                            <th>类型</th>
                                            <th>描述</th>
                                            <th>状态</th>
                                            <th>创建时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($keywords as $keyword): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($keyword['id']); ?></td>
                                                <td><?php echo htmlspecialchars($keyword['keyword']); ?></td>
                                                <td>
                                                    <?php if ($keyword['type'] === 'sender'): ?>
                                                        <span class="badge bg-primary">发件人</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">内容</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (!empty($keyword['description'])) {
                                                        echo strlen($keyword['description']) > 50 ? 
                                                            htmlspecialchars(substr($keyword['description'], 0, 50)) . '...' : 
                                                            htmlspecialchars($keyword['description']);
                                                    } else {
                                                        echo '<span class="text-muted">无描述</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($keyword['is_active']): ?>
                                                        <span class="badge bg-success">启用</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">禁用</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo date('Y-m-d', strtotime($keyword['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="keywords.php?action=edit&id=<?php echo $keyword['id']; ?>" class="btn btn-outline-primary" title="编辑">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="keywords.php?action=delete&id=<?php echo $keyword['id']; ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="删除"
                                                           onclick="return confirm('确定要删除这个关键词吗？\n\n关键词ID: <?php echo htmlspecialchars($keyword['id']); ?>\n关键词: <?php echo htmlspecialchars($keyword['keyword']); ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>