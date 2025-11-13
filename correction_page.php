<!-- 文件名称：correction - page.php
         作用：提供一个用于更正分值的页面，用户可选择年度、周次、年级、班级和项目，查看当前分数并输入新分值进行提交 -->
         <?php 
session_start();
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>更正分值</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e6f7ff 0%, #f2f9ff 100%);
            color: #333;
            line-height: 1.6;
            margin: 0;
            min-height: 100vh;
        }
        .bg-white.p-8.rounded-lg.shadow-md.w-full.max-w-md {
            background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
            background-color: rgba(255, 255, 255, 0.8);
            border: 1px solid #e0e7ff;
            border-radius: 12px;
            padding: 1.2rem;
            margin: 0 auto;
            transition: transform 0.2s;
            overflow-x: auto;
        }
        body.bg-gray-100.flex.justify-center.items-center.h-screen {
            display: grid;
        }

    </style>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4">更正分值</h1>
        <form id="correctionForm">
            <!-- CSRF Token 隐藏字段 -->
            <input type="hidden" name="csrf_token" value="">
            <!-- 年度 -->
            <div class="mb-4">
                <label for="year" class="block text-gray-700 text-sm font-bold mb-2">年度</label>
                <select id="year" name="year" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" onchange="loadWeeks()">
                    <option value="">请选择年度</option>
                </select>
            </div>
            <!-- 周次 -->
            <div class="mb-4">
                <label for="week" class="block text-gray-700 text-sm font-bold mb-2">周次</label>
                <select id="week" name="week" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" onchange="loadGrades()">
                    <option value="">请先选择年度</option>
                </select>
            </div>
            <!-- 年级 -->
            <div class="mb-4">
                <label for="grade" class="block text-gray-700 text-sm font-bold mb-2">年级</label>
                <select id="grade" name="grade" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" onchange="loadClasses()">
                    <option value="">请先选择周次</option>
                </select>
            </div>
            <!-- 班级 -->
            <div class="mb-4">
                <label for="class" class="block text-gray-700 text-sm font-bold mb-2">班级</label>
                <select id="class" name="class" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
                    <option value="">请先选择年级</option>
                </select>
            </div>
            <!-- 项目 -->
            <div class="mb-4">
                <label for="project" class="block text-gray-700 text-sm font-bold mb-2">项目</label>
                <select id="project" name="project" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" onchange="loadProjectScore()">
                    <option value="">加载中...</option>
                </select>
            </div>
            <!-- 当前分数 -->
            <div class="mb-4">
                <label for="currentScore" class="block text-gray-700 text-sm font-bold mb-2">当前分数</label>
                <input type="text" id="currentScore" name="currentScore" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" readonly>
            </div>
            <!-- 新分值 -->
            <div class="mb-4">
                <label for="score" class="block text-gray-700 text-sm font-bold mb-2">新分值</label>
                <input type="number" id="score" name="score" step="0.1" min="0" max="10" class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline" placeholder="请输入新分值">
            </div>
            <!-- 提交按钮 -->
            <div class="flex justify-center">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">提交更正</button>
            </div>
        </form>
    </div>
    <!-- JavaScript 逻辑 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 加载年度
        function loadYears() {
            $.getJSON('update_score.php?action=get_years_list', function(data) {
                if (data.success) {
                    const yearSelect = document.getElementById('year');
                    data.data.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        yearSelect.appendChild(option);
                    });
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('获取年度列表失败:', textStatus, errorThrown);
                document.getElementById('year').innerHTML = '<option value="">加载失败</option>';
            });
        }

        // 加载周次
        function loadWeeks() {
            const selectedYear = document.getElementById('year').value;
            const weekSelect = document.getElementById('week');
            weekSelect.innerHTML = '<option value="">请选择周次</option>';
            if (selectedYear) {
                $.getJSON(`update_score.php?action=get_weeks_by_year&year=${selectedYear}`, function(data) {
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(week => {
                            const option = document.createElement('option');
                            option.value = week;
                            option.textContent = week;
                            weekSelect.appendChild(option);
                        });
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('获取周次列表失败:', textStatus, errorThrown);
                    weekSelect.innerHTML = '<option value="">加载失败</option>';
                });
            }
        }

        // 加载年级
        function loadGrades() {
            const selectedYear = document.getElementById('year').value;
            const selectedWeek = document.getElementById('week').value;
            const gradeSelect = document.getElementById('grade');
            gradeSelect.innerHTML = '<option value="">请选择年级</option>';
            if (selectedYear && selectedWeek) {
                $.getJSON(`update_score.php?action=get_grades_by_year_and_week&year=${selectedYear}&week=${selectedWeek}`, function(data) {
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(grade => {
                            const option = document.createElement('option');
                            option.value = grade;
                            option.textContent = grade + '年级';
                            gradeSelect.appendChild(option);
                        });
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('获取年级列表失败:', textStatus, errorThrown);
                    gradeSelect.innerHTML = '<option value="">加载失败</option>';
                });
            }
        }

        // 加载班级
        function loadClasses() {
            const selectedYear = document.getElementById('year').value;
            const selectedWeek = document.getElementById('week').value;
            const selectedGrade = document.getElementById('grade').value;
            const classSelect = document.getElementById('class');
            classSelect.innerHTML = '<option value="">请选择班级</option>';
            if (selectedYear && selectedWeek && selectedGrade) {
                $.getJSON(`update_score.php?action=get_classes_by_year_week_and_grade&year=${selectedYear}&week=${selectedWeek}&grade=${selectedGrade}`, function(data) {
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(classNum => {
                            const option = document.createElement('option');
                            option.value = classNum;
                            option.textContent = selectedGrade + '年级' + classNum + '班';
                            classSelect.appendChild(option);
                        });
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('获取班级列表失败:', textStatus, errorThrown);
                    classSelect.innerHTML = '<option value="">加载失败</option>';
                });
            }
        }

        // 加载项目选项（从数据库获取）
        function loadProjects() {
            const projectSelect = document.getElementById('project');
            projectSelect.innerHTML = '<option value="">加载中...</option>';

            $.getJSON('update_score.php?action=get_projects_list', function(data) {
                if (data.success && data.data.length > 0) {
                    projectSelect.innerHTML = '<option value="">请选择项目</option>';
                    const projectLabels = {
                        'honglingjin': '红领巾监督岗',
                        'indoor_exercise': '室内操',
                        'outdoor_exercise': '室外操',
                        'morning_exercise_teachers': '两操(师)',
                        'indoor_hygiene': '室内卫生',
                        'outdoor_hygiene': '室外卫生',
                        'hygiene_teachers': '卫生（师）',
                        'civilized_behavior': '文明行为',
                        'assembly_discipline': '集会纪律',
                        'other_discipline': '其他纪律'
                    };

                    data.data.forEach(field => {
                        const option = document.createElement('option');
                        option.value = field;
                        option.textContent = projectLabels[field] || field;
                        projectSelect.appendChild(option);
                    });
                } else {
                    projectSelect.innerHTML = '<option value="">无项目数据</option>';
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('获取项目列表失败:', textStatus, errorThrown);
                projectSelect.innerHTML = '<option value="">加载失败</option>';
            });
        }

        // 加载当前分数
        function loadProjectScore() {
            const selectedYear = document.getElementById('year').value;
            const selectedWeek = document.getElementById('week').value;
            const selectedGrade = document.getElementById('grade').value;
            const selectedClass = document.getElementById('class').value;
            const selectedProject = document.getElementById('project').value;
            const currentScoreInput = document.getElementById('currentScore');
            
            if (selectedYear && selectedWeek && selectedGrade && selectedClass && selectedProject) {
                $.getJSON(`update_score.php?action=get_project_score&year=${selectedYear}&week=${selectedWeek}&grade=${selectedGrade}&class=${selectedClass}&project=${selectedProject}`, function(data) {
                    if (data.success) {
                        currentScoreInput.value = data.data ?? '无记录';
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('获取项目分数失败:', textStatus, errorThrown);
                    currentScoreInput.value = '获取失败';
                });
            }
        }

        // 表单提交处理
        document.getElementById('correctionForm').addEventListener('submit', function(e) {
            e.preventDefault(); // 阻止默认提交

            const formData = new FormData(this);
            fetch('update_score.php', {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
           .then(response => response.json())
           .then(data => {
                if (data.success) {
                    alert('更新成功！');
                    document.getElementById('currentScore').value = formData.get('score');
                } else {
                    alert('更新失败: ' + data.message);
                }
            })
           .catch(error => {
                console.error('请求失败:', error);
                alert('网络错误，请重试');
            });
        });

        // 页面初始化
        $(document).ready(function() {
            loadYears();
            loadProjects();
        });
    </script>
</body>
</html>