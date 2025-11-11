<?php
require_once 'config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '只允许POST请求']);
    exit;
}

try {
    $ruleId = $_POST['rule_id'] ?? '';
    $inputText = $_POST['input_text'] ?? '';
    
    if (empty($ruleId) || empty($inputText)) {
        throw new Exception('缺少必要参数');
    }
    
    $pdo = getDBConnection();
    
    // 获取规则信息
    $stmt = $pdo->prepare("SELECT * FROM rules WHERE id = ? AND is_active = 1");
    $stmt->execute([$ruleId]);
    $rule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rule) {
        throw new Exception('规则不存在或未启用');
    }
    
    $result = [
        'rule_name' => $rule['name'],
        'rule_type' => $rule['rule_type'],
        'input_text' => $inputText
    ];
    
    if ($rule['rule_type'] === 'regex') {
        // 正则规则测试
        $pattern = $rule['pattern'];
        $result['pattern'] = $pattern;
        
        // 执行正则匹配
        if (preg_match($pattern, $inputText, $matches)) {
            $result['matched'] = true;
            $result['matches'] = $matches;
        } else {
            $result['matched'] = false;
        }
    } else {
        // 自定义前后缀规则测试（简化测试逻辑）
        $result['tests'] = [];
        
        // 测试取件码
        $code_prefix = $rule['code_prefix'] ?? '';
        $code_suffix = $rule['code_suffix'] ?? '';
        $code_result = testPrefixSuffix($inputText, $code_prefix, $code_suffix);
        $result['tests']['code'] = [
            'prefix' => $code_prefix,
            'suffix' => $code_suffix,
            'matched' => $code_result['matched'],
            'extracted' => $code_result['matched'] ? $code_result['extracted'] : null
        ];
        
        // 测试驿站
        $station_prefix = $rule['station_prefix'] ?? '';
        $station_suffix = $rule['station_suffix'] ?? '';
        $station_result = testPrefixSuffix($inputText, $station_prefix, $station_suffix);
        $result['tests']['station'] = [
            'prefix' => $station_prefix,
            'suffix' => $station_suffix,
            'matched' => $station_result['matched'],
            'extracted' => $station_result['matched'] ? $station_result['extracted'] : null
        ];
        
        // 测试地址
        $address_prefix = $rule['address_prefix'] ?? '';
        $address_suffix = $rule['address_suffix'] ?? '';
        $address_result = testPrefixSuffix($inputText, $address_prefix, $address_suffix);
        $result['tests']['address'] = [
            'prefix' => $address_prefix,
            'suffix' => $address_suffix,
            'matched' => $address_result['matched'],
            'extracted' => $address_result['matched'] ? $address_result['extracted'] : null
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'result' => $result]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// 测试前后缀匹配的辅助函数
function testPrefixSuffix($text, $prefix, $suffix) {
    // 如果前后缀都为空，则认为匹配成功（不需要提取任何内容）
    if (empty($prefix) && empty($suffix)) {
        return [
            'matched' => true,
            'extracted' => $text  // 返回原文本
        ];
    }
    
    $matched = true;
    $extracted = $text;
    
    // 处理前缀
    if (!empty($prefix)) {
        $prefixPos = strpos($text, $prefix);
        if ($prefixPos !== false) {
            // 文本包含指定前缀，移除前缀前的部分
            $extracted = substr($extracted, $prefixPos + strlen($prefix));
        } else {
            // 文本不包含指定前缀，匹配失败
            $matched = false;
        }
    }
    
    // 处理后缀（仅在前缀匹配成功或没有前缀时处理）
    if ($matched && !empty($suffix)) {
        $suffixPos = strpos($extracted, $suffix);
        if ($suffixPos !== false) {
            // 文本包含指定后缀，移除后缀后的部分
            $extracted = substr($extracted, 0, $suffixPos);
        } else {
            // 文本不包含指定后缀，匹配失败
            $matched = false;
        }
    }
    
    // 清理提取结果（去除前后空格）
    if ($matched && is_string($extracted)) {
        $extracted = trim($extracted);
    }
    
    return [
        'matched' => $matched,
        'extracted' => $matched ? $extracted : null
    ];
}
?>