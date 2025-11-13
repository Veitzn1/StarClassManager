<?php
// 文件名称：install.php
// 作用：执行 SQL 脚本，创建名为 weekstar 的数据库和三个表（classes 和 deductions、users），
// 并在创建成功或失败时输出相应的信息
// config.php 包含数据库连接信息

include 'config.php';

// 确保连接成功
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// SQL 脚本用于创建数据库和表
$sql = "
CREATE DATABASE IF NOT EXISTS weekstar;

USE weekstar;

-- 创建 classes 表
CREATE TABLE IF NOT EXISTS classes (
    year VARCHAR(10) NOT NULL,
    grade INT NOT NULL,
    count INT NOT NULL,
    PRIMARY KEY (year, grade)
);

-- 创建 users 表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建 deductions 表
CREATE TABLE IF NOT EXISTS deductions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year VARCHAR(10) NOT NULL,
    grade INT NOT NULL,
    class INT NOT NULL,
    week INT NOT NULL,

    honglingjin DECIMAL(3,1) DEFAULT 10.0,
    indoor_exercise DECIMAL(3,1) DEFAULT 10.0,
    outdoor_exercise DECIMAL(3,1) DEFAULT 10.0,
    morning_exercise_teachers DECIMAL(3,1) DEFAULT 10.0,
    indoor_hygiene DECIMAL(3,1) DEFAULT 10.0,
    outdoor_hygiene DECIMAL(3,1) DEFAULT 10.0,
    hygiene_teachers DECIMAL(3,1) DEFAULT 10.0,
    civilized_behavior DECIMAL(3,1) DEFAULT 10.0,
    assembly_discipline DECIMAL(3,1) DEFAULT 10.0,
    other_discipline DECIMAL(3,1) DEFAULT 10.0,
    UNIQUE (year, grade, class, week)
);
";

// 执行 SQL 脚本
if ($conn->multi_query($sql) === TRUE) {
    echo "数据库和表创建成功";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>