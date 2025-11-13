<?php
// 假设数据库连接配置信息存于 config.php 文件，后续使用时用户需自行正确配置
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '用户名和密码不能为空']);
        exit;
    }

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("数据库连接失败: " . $conn->connect_error);
        }

        // 检查用户名是否已存在
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => '该用户名已被注册']);
            $stmt->close();
            $conn->close();
            exit;
        }

        // 插入新用户
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => '注册成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '注册失败: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => '只允许 POST 请求']);
}