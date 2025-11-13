<?php
$servername = "localhost"; 
$username = "your_username";  //自行修改数据库用户名
$password = "your_password";  //自行修改数据库密码
$dbname = "your_database";  //自行修改数据库

// 创建数据库连接并指定数据库名
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 检查连接是否成功
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


?>
