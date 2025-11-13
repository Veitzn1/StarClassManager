<?php
session_start();

include 'config.php';

// 创建数据库连接
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 检查连接
if ($conn->connect_error) {
    die(json_encode(['error' => "数据库连接失败: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// 日志函数（可选）
function logMessage($message) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

logMessage("请求开始");

// 获取请求动作
$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'import_deductions':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("非法请求方法");
            }

            $dataJson = file_get_contents('php://input');
            $data = json_decode($dataJson, true);

            if (!is_array($data) || !isset($data['data']) || !is_array($data['data'])) {
                throw new Exception("无效的导入数据");
            }

            $items = $data['data'];
            if (empty($items)) {
                throw new Exception("数据为空");
            }

            // 获取年份和周次（统一取第一条）
            $year = $items[0]['year'];
            $week = intval($items[0]['week']);

            if ($week < 1 || $week > 24) {
                throw new Exception("周次无效");
            }

            // 开始事务
            $conn->begin_transaction();

            // 清除当前年份+周次的旧数据
            $stmt = $conn->prepare("DELETE FROM deductions WHERE year = ? AND week = ?");
            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $conn->error);
            }
            $stmt->bind_param("si", $year, $week);
            $stmt->execute();
            $stmt->close();

            // 插入新数据（不包含 total 和 is_star）
            $stmt = $conn->prepare("
                INSERT INTO deductions 
                (year, week, grade, class, honglingjin, indoor_exercise, outdoor_exercise, 
                 morning_exercise_teachers, indoor_hygiene, outdoor_hygiene, hygiene_teachers, 
                 civilized_behavior, assembly_discipline, other_discipline)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    honglingjin = VALUES(honglingjin),
                    indoor_exercise = VALUES(indoor_exercise),
                    outdoor_exercise = VALUES(outdoor_exercise),
                    morning_exercise_teachers = VALUES(morning_exercise_teachers),
                    indoor_hygiene = VALUES(indoor_hygiene),
                    outdoor_hygiene = VALUES(outdoor_hygiene),
                    hygiene_teachers = VALUES(hygiene_teachers),
                    civilized_behavior = VALUES(civilized_behavior),
                    assembly_discipline = VALUES(assembly_discipline),
                    other_discipline = VALUES(other_discipline)
            ");

            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $conn->error);
            }

            foreach ($items as $item) {
                $grade = intval($item['grade']);
                $class = intval($item['class']);
                $honglingjin = floatval($item['honglingjin']);
                $indoor_exercise = floatval($item['indoor_exercise']);
                $outdoor_exercise = floatval($item['outdoor_exercise']);
                $morning_exercise_teachers = floatval($item['morning_exercise_teachers']);
                $indoor_hygiene = floatval($item['indoor_hygiene']);
                $outdoor_hygiene = floatval($item['outdoor_hygiene']);
                $hygiene_teachers = floatval($item['hygiene_teachers']);
                $civilized_behavior = floatval($item['civilized_behavior']);
                $assembly_discipline = floatval($item['assembly_discipline']);
                $other_discipline = floatval($item['other_discipline']);

                $stmt->bind_param(
                    'siiddddddddddd',
                    $year, $week, $grade, $class,
                    $honglingjin, $indoor_exercise, $outdoor_exercise,
                    $morning_exercise_teachers, $indoor_hygiene, $outdoor_hygiene,
                    $hygiene_teachers, $civilized_behavior, $assembly_discipline,
                    $other_discipline
                );

                if (!$stmt->execute()) {
                    throw new Exception("执行 SQL 错误：" . $stmt->error);
                }
            }

            $stmt->close();
            $conn->commit();
            echo json_encode(['success' => true, 'message' => '数据已成功导入！']);
            break;

        case 'save_classes':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("非法请求方法");
            }

            $year = $_POST['year'];
            $grades = $_POST['grades'] ?? [];

            if (empty($year) || !is_array($grades) || count($grades) === 0) {
                throw new Exception("年度和班级数量不能为空");
            }

            // 开始事务
            $conn->begin_transaction();

            foreach ($grades as $grade => $count) {
                $grade = intval($grade);
                $count = intval($count);

                if (!is_numeric($grade) || !is_numeric($count)) {
                    throw new Exception("年级或班级数量不是数字");
                }

                $stmt = $conn->prepare("INSERT INTO classes (year, grade, count) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE count = ?");
                if (!$stmt) {
                    throw new Exception("SQL准备失败: " . $conn->error);
                }
                $stmt->bind_param("siii", $year, $grade, $count, $count);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            header('Location: index.php');
            exit;

        case 'save_deductions':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("非法请求方法");
            }

            $year = $_POST['year'];
            $week = trim($_POST['week'] ?? '');

            if (empty($year) || empty($week)) {
                throw new Exception("年份或周次无效");
            }

            $deductionsJson = $_POST['deductions'] ?? '';
            if (!is_string($deductionsJson)) {
                throw new Exception("扣分数据格式错误");
            }

            $deductions = json_decode($deductionsJson, true);
            if (!is_array($deductions)) {
                throw new Exception("无法解析扣分数据 JSON");
            }

            // 支持的字段（更新为新的 10 个扣分项）
            $validFields = [
                'honglingjin',
                'indoor_exercise',
                'outdoor_exercise',
                'morning_exercise_teachers',
                'indoor_hygiene',
                'outdoor_hygiene',
                'hygiene_teachers',
                'civilized_behavior',
                'assembly_discipline',
                'other_discipline'
            ];

            // 统计每个班级在各项目中出现的次数
            $classCounts = [];
            foreach ($deductions as $item => $classes) {
                if (!in_array($item, $validFields)) {
                    continue;
                }

                foreach ($classes as $class) {
                    $parts = explode('-', $class);
                    if (count($parts) !== 2) {
                        logMessage("跳过非法班级格式: $class");
                        continue;
                    }

                    list($grade, $classNum) = $parts;
                    $grade = intval($grade);
                    $classNum = intval($classNum);

                    if (!is_numeric($grade) || !is_numeric($classNum)) {
                        logMessage("跳过非法年级/班级编号: $grade-$classNum");
                        continue;
                    }

                    $key = "$item|$grade|$classNum";
                    $classCounts[$key] = ($classCounts[$key] ?? 0) + 1;
                }
            }

            // 获取该年度下所有班级
            $allClasses = [];
            $result = $conn->query("SELECT grade, count FROM classes WHERE year = '$year'");
            while ($row = $result->fetch_assoc()) {
                $grade = $row['grade'];
                $count = $row['count'];
                for ($i = 1; $i <= $count; $i++) {
                    $allClasses["$grade|$i"] = ['grade' => $grade, 'class' => $i];
                }
            }

            // 开始事务
            $conn->begin_transaction();

            foreach ($allClasses as $classKey => $classInfo) {
                $grade = $classInfo['grade'];
                $classNum = $classInfo['class'];

                $scores = [];

                foreach ($validFields as $field) {
                    $key = "$field|$grade|$classNum";
                    $count = $classCounts[$key] ?? 0;
                    $score = round(10.0 - $count * 0.1, 1);
                    $scores[$field] = $score;
                }

                // 构建参数
                $params = [
                    $year,
                    $grade,
                    $classNum,
                    $week,
                    $scores['honglingjin'] ?? 10.0,
                    $scores['indoor_exercise'] ?? 10.0,
                    $scores['outdoor_exercise'] ?? 10.0,
                    $scores['morning_exercise_teachers'] ?? 10.0,
                    $scores['indoor_hygiene'] ?? 10.0,
                    $scores['outdoor_hygiene'] ?? 10.0,
                    $scores['hygiene_teachers'] ?? 10.0,
                    $scores['civilized_behavior'] ?? 10.0,
                    $scores['assembly_discipline'] ?? 10.0,
                    $scores['other_discipline'] ?? 10.0
                ];

                // SQL 插入语句
                $sql = "
                    INSERT INTO deductions 
                        (year, grade, class, week, honglingjin, indoor_exercise, outdoor_exercise, morning_exercise_teachers,
                         indoor_hygiene, outdoor_hygiene, hygiene_teachers, civilized_behavior, assembly_discipline, other_discipline)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      honglingjin = VALUES(honglingjin),
                      indoor_exercise = VALUES(indoor_exercise),
                      outdoor_exercise = VALUES(outdoor_exercise),
                      morning_exercise_teachers = VALUES(morning_exercise_teachers),
                      indoor_hygiene = VALUES(indoor_hygiene),
                      outdoor_hygiene = VALUES(outdoor_hygiene),
                      hygiene_teachers = VALUES(hygiene_teachers),
                      civilized_behavior = VALUES(civilized_behavior),
                      assembly_discipline = VALUES(assembly_discipline),
                      other_discipline = VALUES(other_discipline)";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    logMessage("SQL准备失败: " . $conn->error);
                    throw new Exception("SQL准备失败: " . $conn->error);
                }

                // 参数类型：year(string), grade(int), class(int), week(int), 后面是 10 个 decimal
                $types = 'siiidddddddddd';
                $stmt->bind_param($types, ...$params);
                $stmt->execute();

                if ($stmt->error) {
                    logMessage("执行 SQL 错误：" . $stmt->error);
                    throw new Exception("执行 SQL 错误：" . $stmt->error);
                }

                $stmt->close();
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => '扣分已成功保存！']);
            break;


        default:
            throw new Exception("无效的请求类型");
    }

    $conn->close();
} catch (Exception $e) {
    if ($conn && $conn->ping()) {
        $conn->rollback();
        logMessage('事务回滚: ' . $e->getMessage());
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}