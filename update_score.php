<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// 启用错误显示（用于调试）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 处理 GET 请求（数据获取）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $valid_actions = [
        'get_years_list',
        'get_weeks_by_year',
        'get_grades_by_year_and_week',
        'get_classes_by_year_week_and_grade',
        'get_projects_list',
        'get_project_score'
        
    ];

    if (!in_array($action, $valid_actions)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '无效的操作类型']);
        exit;
    }

    // 选择数据库
    $conn->select_db(DB_NAME);

    try {
        switch ($action) {
            case 'get_years_list':
                $result = $conn->query("SELECT DISTINCT year FROM classes ORDER BY year DESC");
                $years = [];
                while ($row = $result->fetch_assoc()) {
                    $years[] = $row['year'];
                }
                echo json_encode(['success' => true, 'data' => $years]);
                break;

            case 'get_weeks_by_year':
                $year = $conn->real_escape_string($_GET['year']);
                $result = $conn->query("SELECT DISTINCT week FROM deductions WHERE year = '$year'");
                $weeks = [];
                while ($row = $result->fetch_assoc()) {
                    $weeks[] = $row['week'];
                }
                echo json_encode(['success' => true, 'data' => $weeks]);
                break;

            case 'get_grades_by_year_and_week':
                $year = $conn->real_escape_string($_GET['year']);
                $week = intval($_GET['week']);
                $result = $conn->query("SELECT DISTINCT grade FROM deductions WHERE year = '$year' AND week = $week");
                $grades = [];
                while ($row = $result->fetch_assoc()) {
                    $grades[] = $row['grade'];
                }
                echo json_encode(['success' => true, 'data' => $grades]);
                break;

            case 'get_classes_by_year_week_and_grade':
                $year = $conn->real_escape_string($_GET['year']);
                $week = intval($_GET['week']);
                $grade = intval($_GET['grade']);
                $result = $conn->query("SELECT DISTINCT class FROM deductions WHERE year = '$year' AND week = $week AND grade = $grade");
                $classes = [];
                while ($row = $result->fetch_assoc()) {
                    $classes[] = $row['class'];
                }
                echo json_encode(['success' => true, 'data' => $classes]);
                break;

            case 'get_projects_list':
                // 获取 deductions 表中所有项目字段（排除 year, week, grade, class 等控制字段）
                $stmt = $conn->prepare("SHOW COLUMNS FROM deductions");
                $stmt->execute();
                $result = $stmt->get_result();
                $projectFields = [];

                $excludeFields = ['id', 'year', 'week', 'grade', 'class'];
                while ($row = $result->fetch_assoc()) {
                    $field = $row['Field'];
                    if (!in_array($field, $excludeFields)) {
                        $projectFields[] = $field;
                    }
                }

                echo json_encode(['success' => true, 'data' => $projectFields]);
                $stmt->close();
                break;

            case 'get_project_score':
                $year = $conn->real_escape_string($_GET['year']);
                $week = intval($_GET['week']);
                $grade = intval($_GET['grade']);
                $class = intval($_GET['class']);
                $project = $conn->real_escape_string($_GET['project']);

                // 验证项目字段是否存在
                $validProjects = ['honglingjin', 'indoor_exercise', 'outdoor_exercise', 'morning_exercise_teachers', 'indoor_hygiene', 'outdoor_hygiene', 'hygiene_teachers', 'civilized_behavior', 'assembly_discipline', 'other_discipline'];
                if (!in_array($project, $validProjects)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => '无效的项目类型']);
                    exit;
                }

                $stmt = $conn->prepare("SELECT $project FROM deductions WHERE year = ? AND week = ? AND grade = ? AND class = ?");
                $stmt->bind_param("siii", $year, $week, $grade, $class);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    echo json_encode(['success' => true, 'data' => $row[$project]]);
                } else {
                    echo json_encode(['success' => true, 'data' => null]);
                }
                $stmt->close();
                break;
    
    
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '服务器错误: ' . $e->getMessage()]);
    }
    $conn->close();
    exit;
}

// 处理 POST 请求（分值更新）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 校验
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => '非法请求：CSRF 验证失败']);
        exit;
    }

    // 验证必要字段
    $required_fields = ['year', 'week', 'grade', 'class', 'project', 'score'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "缺少必要参数: $field"]);
            exit;
        }
    }

    $year = $conn->real_escape_string($_POST['year']);
    $week = intval($_POST['week']);
    $grade = intval($_POST['grade']);
    $class = intval($_POST['class']);
    $project = $conn->real_escape_string($_POST['project']);
    $score = floatval($_POST['score']);

    // 验证分数范围
    if ($score < 0 || $score > 10) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '分值必须在 0-10 之间']);
        exit;
    }

    // 验证项目字段是否存在
    $validProjects = ['honglingjin', 'indoor_exercise', 'outdoor_exercise', 'morning_exercise_teachers', 'indoor_hygiene', 'outdoor_hygiene', 'hygiene_teachers', 'civilized_behavior', 'assembly_discipline', 'other_discipline'];
    if (!in_array($project, $validProjects)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '无效的项目类型']);
        exit;
    }

    // 更新分值
    $conn->select_db(DB_NAME);
    $stmt = $conn->prepare("UPDATE deductions SET $project = ? WHERE year = ? AND week = ? AND grade = ? AND class = ?");
    $stmt->bind_param("dssii", $score, $year, $week, $grade, $class);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '分值更新成功']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '数据库错误: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>