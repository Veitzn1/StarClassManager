<!-- 文件名称：getinfo.php
         作用：根据不同的请求动作，从数据库中获取相应的数据并以 JSON 格式返回，如获取年度列表、某年度下各年级班级数量、某年度某周次的扣分数据等 -->
<?php
// 假设数据库连接配置信息存于 config.php 文件，后续使用时用户需自行正确配置
require_once 'config.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("数据库连接失败: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'get_years_list':
                // 获取所有存在的年度
                $years = [];
                $result = $conn->query("SELECT DISTINCT year FROM classes ORDER BY year DESC");
                while ($row = $result->fetch_assoc()) {
                    $years[] = $row['year'];
                }
                echo json_encode($years);
                break;

            case 'get_classes_count_by_year':
                $year = $_GET['year'] ?? '';
                if ($year) {
                    $classesCount = [];
                    $result = $conn->query("SELECT grade, count FROM classes WHERE year = '$year'");
                    while ($row = $result->fetch_assoc()) {
                        $classesCount[$row['grade']] = $row['count'];
                    }
                    echo json_encode($classesCount);
                } else {
                    echo json_encode([]);
                }
                break;

            case 'get_deductions_by_year_and_week':
                $year = $_GET['year'] ?? '';
                $week = $_GET['week'] ?? '';
                if ($year && $week) {
                    $deductions = [];
                    $result = $conn->query("SELECT * FROM deductions WHERE year = '$year' AND week = '$week'");
                    while ($row = $result->fetch_assoc()) {
                        $deductions[] = $row;
                    }
                    echo json_encode($deductions);
                } else {
                    echo json_encode([]);
                }
                break;

            case 'get_project_score':
                $year = $_GET['year'] ?? '';
                $week = $_GET['week'] ?? '';
                $grade = $_GET['grade'] ?? '';
                $class = $_GET['class'] ?? '';
                $project = $_GET['project'] ?? '';
                if ($year && $week && $grade && $class && $project) {
                    $result = $conn->query("SELECT $project FROM deductions WHERE year = '$year' AND week = '$week' AND grade = '$grade' AND class = '$class'");
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo json_encode(['success' => true, 'data' => $row[$project]]);
                    } else {
                        echo json_encode(['success' => false, 'message' => '未找到相关记录']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => '缺少必要参数']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => '未知的请求动作']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '只允许 GET 请求']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>