<?php
require_once 'config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 获取规则和关键词数据
try {
    $pdo = getDBConnection();
    
    // 获取启用的规则
    $stmt = $pdo->query("SELECT id, name, pattern, description FROM rules WHERE is_active = 1 ORDER BY id");
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取启用的关键词
    $stmt = $pdo->query("SELECT id, keyword, type, description FROM keywords WHERE is_active = 1 ORDER BY id");
    $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 构造JSON数据
    $data = [
        'version' => date('Y-m-d H:i:s'),
        'rules' => $rules,
        'keywords' => $keywords
    ];
    
    // 将数据保存到JSON文件
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents('rules_data.json', $json);
    
    $message = '数据导出成功';
    $json_data = $json;
} catch (PDOException $e) {
    $message = '导出失败: ' . $e->getMessage();
    $json_data = '';
} catch (Exception $e) {
    $message = '导出失败: ' . $e->getMessage();
    $json_data = '';
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>导出JSON - 取件码规则管理系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="mobile.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">取件码规则管理系统</a>
            <div class="navbar-nav">
                <a class="nav-link" href="rules.php">规则管理</a>
                <a class="nav-link" href="keywords.php">关键词管理</a>
                <a class="nav-link active" href="export.php">导出JSON</a>
                <a class="nav-link" href="api.php?action=get_rules" target="_blank">API接口</a>
                <a class="nav-link" href="api_keys.php">API密钥</a>
                <a class="nav-link" href="info.php">系统信息</a>
            </div>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="nav-link" href="logout.php">登出</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>导出JSON数据</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>导出信息</h5>
                    </div>
                    <div class="card-body">
                        <p>点击下面的按钮可以将当前的规则和关键词数据导出为JSON格式，供App端下载使用。</p>
                        <div class="alert alert-info">
                            <strong>导出说明：</strong>导出的数据仅包含启用状态的规则和关键词，导出后会自动保存为rules_data.json文件。
                        </div>
                        <a href="rules_data.json" class="btn btn-primary" download>
                            <i class="bi bi-download"></i> 下载JSON文件
                        </a>
                        <a href="api.php?action=get_rules" class="btn btn-success" target="_blank">
                            <i class="bi bi-link"></i> API接口地址
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>JSON数据预览</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($json_data): ?>
                            <pre><code class="language-json"><?php echo htmlspecialchars($json_data); ?></code></pre>
                        <?php else: ?>
                            <p>暂无数据可显示</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>