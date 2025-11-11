<?php
require_once 'config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>取件码规则管理系统</title>
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
                <a class="nav-link" href="export.php">导出JSON</a>
                <a class="nav-link" href="api.php?action=get_rules" target="_blank">API接口</a>
                <a class="nav-link" href="api_keys.php">API密钥</a>
                <a class="nav-link" href="info.php">系统信息</a>
            </div>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="nav-link" href="change_password.php">修改密码</a>
                <?php if ($_SESSION['username'] === 'admin'): ?>
                    <a class="nav-link" href="admin_panel.php">管理员面板</a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php">登出</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>系统提示：</strong>欢迎使用取件码规则管理系统，请选择相应的功能模块进行操作。如需帮助，请查看<a href="info.php" class="alert-link">系统信息</a>页面。
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-list-check" style="font-size: 3rem; color: #0d6efd;"></i>
                        <h5 class="card-title mt-3">规则管理</h5>
                        <p class="card-text">管理取件码解析规则</p>
                        <a href="rules.php" class="btn btn-primary">进入管理</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-key" style="font-size: 3rem; color: #198754;"></i>
                        <h5 class="card-title mt-3">关键词管理</h5>
                        <p class="card-text">管理关键词过滤规则</p>
                        <a href="keywords.php" class="btn btn-success">进入管理</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-cloud-download" style="font-size: 3rem; color: #ffc107;"></i>
                        <h5 class="card-title mt-3">导出JSON</h5>
                        <p class="card-text">导出规则和关键词为JSON文件</p>
                        <a href="export.php" class="btn btn-warning">导出数据</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-info-circle" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="card-title mt-3">系统信息</h5>
                        <p class="card-text">查看系统状态和使用说明</p>
                        <a href="info.php" class="btn btn-secondary">查看信息</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-key-fill" style="font-size: 3rem; color: #6f42c1;"></i>
                        <h5 class="card-title mt-3">API密钥管理</h5>
                        <p class="card-text">管理API访问密钥</p>
                        <a href="api_keys.php" class="btn btn-info">管理密钥</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>