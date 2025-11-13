<?php
// 假设数据库连接配置信息存于 config.php 文件，后续使用时用户需自行正确配置
require_once 'config.php';
session_start();

// 验证请求方法
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // 创建数据库连接
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("数据库连接失败: " . $conn->connect_error);
        }

        // 准备 SQL 查询语句，防止 SQL 注入
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // 登录成功，设置会话变量
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            echo json_encode(['success' => true, 'message' => '登录成功']);
        } else {
            // 登录失败
            echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        }

        // 关闭语句和连接
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        // 发生异常，返回错误信息
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    // 非 POST 请求，返回错误信息
    echo json_encode(['success' => false, 'message' => '只允许 POST 请求']);
}