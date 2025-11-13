<?php 
session_start();
include 'header.php'; 
?>
<!DOCTYPE html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>年度星星数量排行榜</title>
    <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Excel导入导出 -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <style>
        /* 分页标签样式 */
        .tab {
            padding: 0.6rem 1.5rem;
            background: #e0f2fe;
            color: #2563eb;
            border-radius: 8px;
            margin-right: 0.8rem;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .tab.active {
            background: #2563eb;
            color: white;
        }

        /* 分页内容样式 */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container">
        <!-- 年度选择 -->
        <div class="card">
            <div class="flex items-center space-x-2">
                <label for="yearSelector" class="block font-semibold">选择年度：</label>
                <select id="yearSelector" name="yearSelector" class="w-full md:w-1/4"></select>
            </div>
        </div>
        <!-- 分页标签 -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button class="tab active" data-target="schoolRanking">全校排行榜</button>
            <button class="tab" data-target="gradeRanking">年级排行榜</button>
        </div>
        <!-- 全校排行榜分页内容 -->
        <div id="schoolRanking" class="tab-content active">
            <div class="card">
                <h2 class="text-xl font-bold mb-4">全校排行榜</h2>
                <div class="overflow-x-auto">
                    <table id="schoolRankingTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th>班级编号</th>
                                <th>星星数量</th>
                                <th>获得一百分的周次</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <!-- 数据将通过 JavaScript 动态填充 -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card mt-6 mb-4">
                <button id="exportSchoolExcel" class="btn btn-green">导出全校排行榜到 Excel</button>
            </div>
        </div>
        <!-- 年级排行榜分页内容 -->
        <div id="gradeRanking" class="tab-content">
            <div class="flex flex-wrap gap-2 mb-4">
                <!-- 年级分页标签 -->
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <button class="tab <?= $i === 1 ? 'active' : '' ?>" data-target="grade<?= $i ?>Ranking">
                        <?= $i ?>年级
                    </button>
                <?php endfor; ?>
            </div>
            <!-- 各年级表格容器 -->
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div id="grade<?= $i ?>Ranking" class="tab-content <?= $i === 1 ? 'active' : '' ?>">
                    <div class="card">
                        <h2 class="text-xl font-bold mb-4"><?= $i ?>年级排行榜</h2>
                        <div class="overflow-x-auto">
                            <table id="grade<?= $i ?>RankingTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th>班级编号</th>
                                        <th>星星数量</th>
                                        <th>获得一百分的周次</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <!-- 数据将通过 JavaScript 动态填充 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card mt-6 mb-4">
                        <button id="exportGradeExcel" class="btn btn-green">导出所有年级排行榜到 Excel</button>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            // 初始化年度选择框
            initYearSelector();

            // 年度选择改变事件
            $('#yearSelector').on('change', function () {
                const selectedYear = $(this).val();
                loadRankingData(selectedYear);
            });

            // 大分页标签点击事件
            $('.tab[data-target^="schoolRanking"], .tab[data-target^="gradeRanking"]').on('click', function () {
                const target = $(this).data('target');
                $('.tab').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $(`#${target}`).addClass('active');

                // 若点击的是年级排行榜，显示第一个小分页
                if (target === 'gradeRanking') {
                    $('#gradeRanking .tab:first').addClass('active');
                    $('#gradeRanking .tab-content:first').addClass('active');
                }
            });

            // 年级内分页标签点击事件
            $('#gradeRanking .tab').on('click', function () {
                const target = $(this).data('target');
                $('#gradeRanking .tab').removeClass('active');
                $(this).addClass('active');
                $('#gradeRanking .tab-content').removeClass('active');
                $(`#${target}`).addClass('active');
            });

            // 导出全校排行榜到 Excel
            $('#exportSchoolExcel').on('click', function () {
                const selectedYear = $('#yearSelector').val();
                exportSchoolRankingToExcel(selectedYear);
            });

            // 导出所有年级排行榜到 Excel
            $('#exportGradeExcel').on('click', function () {
                const selectedYear = $('#yearSelector').val();
                exportAllGradeRankingToExcel(selectedYear);
            });
        });

        // 初始化年度选择框
        function initYearSelector() {
            $.getJSON('getinfo.php?action=get_years_list', function (years) {
                const $yearSelect = $('#yearSelector');
                $yearSelect.empty();
                if (years.length > 0) {
                    years.forEach(function (year) {
                        $yearSelect.append(`<option value="${year}">${year}</option>`);
                    });
                    const selectedYear = years[0];
                    $yearSelect.val(selectedYear);
                    loadRankingData(selectedYear);
                } else {
                    $yearSelect.append('<option value="">暂无可用年度</option>');
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.error("获取年份失败: " + textStatus + ", " + errorThrown);
            });
        }

        // 加载排行榜数据
        async function loadRankingData(year) {
            const allDeductions = [];
            for (let week = 1; week <= 24; week++) {
                try {
                    const deductions = await fetchDeductions(year, week);
                    allDeductions.push(...deductions.map(item => ({ ...item, week })));
                } catch (error) {
                    console.error(`获取第 ${week} 周扣分数据失败:`, error);
                }
            }

            const schoolRanking = calculateSchoolRanking(allDeductions);
            const gradeRanking = await calculateGradeRanking(allDeductions, year);

            renderSchoolRanking(schoolRanking);
            renderGradeRanking(gradeRanking);
        }

        // 计算全校排行榜
        function calculateSchoolRanking(allDeductions) {
            const schoolRanking = {};
            allDeductions.forEach(item => {
                const classKey = `${item.grade}-${item.class}`;
                const totalPoints = [
                    item.honglingjin, item.indoor_exercise, item.outdoor_exercise,
                    item.morning_exercise_teachers, item.indoor_hygiene,
                    item.outdoor_hygiene, item.hygiene_teachers, item.civilized_behavior,
                    item.assembly_discipline, item.other_discipline
                ].reduce((sum, val) => sum + parseFloat(val || 0), 0);

                if (!schoolRanking[classKey]) {
                    schoolRanking[classKey] = { starCount: 0, weeks: [] };
                }

                if (totalPoints === 100) {
                    schoolRanking[classKey].starCount++;
                    schoolRanking[classKey].weeks.push(item.week);
                }
            });

            return Object.entries(schoolRanking)
               .map(([classKey, { starCount, weeks }]) => ({
                    classKey,
                    starCount,
                    weeks: weeks.sort((a, b) => a - b).map(week => `第${week}周`).join('、')
                }))
               .sort((a, b) => b.starCount - a.starCount);
        }

        // 计算年级排行榜
        async function calculateGradeRanking(allDeductions, year) {
            const allClasses = await getClasses(year);
            allDeductions.forEach(item => {
                const classKey = `${item.grade}-${item.class}`;
                const totalPoints = [
                    item.honglingjin, item.indoor_exercise, item.outdoor_exercise,
                    item.morning_exercise_teachers, item.indoor_hygiene,
                    item.outdoor_hygiene, item.hygiene_teachers, item.civilized_behavior,
                    item.assembly_discipline, item.other_discipline
                ].reduce((sum, val) => sum + parseFloat(val || 0), 0);

                if (!allClasses[classKey]) {
                    allClasses[classKey] = { grade: parseInt(item.grade), class: parseInt(item.class), starCount: 0, weeks: [] };
                }

                if (totalPoints === 100) {
                    allClasses[classKey].starCount++;
                    allClasses[classKey].weeks.push(item.week);
                }
            });

            const result = {};
            for (const classKey in allClasses) {
                const grade = allClasses[classKey].grade;
                if (!result[grade]) {
                    result[grade] = [];
                }
                allClasses[classKey].weeks = allClasses[classKey].weeks.sort((a, b) => a - b).map(week => `第${week}周`).join('、');
                result[grade].push(allClasses[classKey]);
            }

            for (const grade in result) {
                result[grade].sort((a, b) => b.starCount - a.starCount);
            }

            return result;
        }

        // 获取某个年度下的所有班级
        function getClasses(year) {
            return new Promise((resolve, reject) => {
                $.getJSON('getinfo.php?action=get_current_year_and_classes', { year: year }, function (data) {
                    const allClasses = {};
                    const grades = data.grades;
                    for (const grade in grades) {
                        for (let i = 1; i <= grades[grade]; i++) {
                            const classKey = `${grade}-${i}`;
                            allClasses[classKey] = { grade: parseInt(grade), class: i, starCount: 0, weeks: [] };
                        }
                    }
                    resolve(allClasses);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    console.error("获取班级信息失败: " + textStatus + ", " + errorThrown);
                    reject(new Error("获取班级信息失败"));
                });
            });
        }

        // 渲染全校排行榜
        function renderSchoolRanking(ranking) {
            const $tableBody = $('#schoolRankingTable tbody');
            $tableBody.empty();
            ranking.forEach(item => {
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td>${item.classKey}</td>
                        <td>${item.starCount}</td>
                        <td>${item.weeks}</td>
                    </tr>`;
                $tableBody.append(row);
            });
        }

        // 渲染年级排行榜
        function renderGradeRanking(ranking) {
            for (let grade = 1; grade <= 6; grade++) {
                const $tableBody = $(`#grade${grade}RankingTable tbody`);
                $tableBody.empty();
                if (ranking[grade]) {
                    ranking[grade].forEach(item => {
                        const row = `
                            <tr class="hover:bg-gray-50">
                                <td>${item.grade}-${item.class}</td>
                                <td>${item.starCount}</td>
                                <td>${item.weeks}</td>
                            </tr>`;
                        $tableBody.append(row);
                    });
                }
            }
        }

        // 导出全校排行榜到 Excel
        function exportSchoolRankingToExcel(year) {
            const $table = $('#schoolRankingTable');
            const rows = [];
            const headers = [];
            // 添加标题行
            rows.push([`${year}年班级常规全校排行榜`]);
            $table.find('th').each(function () {
                headers.push($(this).text());
            });
            rows.push(headers);

            $table.find('tbody tr').each(function () {
                const rowData = [];
                $(this).find('td').each(function () {
                    rowData.push($(this).text());
                });
                rows.push(rowData);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(wb, ws, "全校排行榜");
            XLSX.writeFile(wb, `${year}年全校星星数量排行榜.xlsx`);
        }

        // 导出所有年级排行榜到 Excel
        function exportAllGradeRankingToExcel(year) {
            const wb = XLSX.utils.book_new();

            for (let grade = 1; grade <= 6; grade++) {
                const $table = $(`#grade${grade}RankingTable`);
                const rows = [];
                const headers = [];
                // 添加标题行
                rows.push([`${year}年班级常规${grade}年级排行榜`]);
                $table.find('th').each(function () {
                    headers.push($(this).text());
                });
                rows.push(headers);

                $table.find('tbody tr').each(function () {
                    const rowData = [];
                    $(this).find('td').each(function () {
                        rowData.push($(this).text());
                    });
                    rows.push(rowData);
                });

                const ws = XLSX.utils.aoa_to_sheet(rows);
                XLSX.utils.book_append_sheet(wb, ws, `${grade}年级排行榜`);
            }

            XLSX.writeFile(wb, `${year}年所有年级星星数量排行榜.xlsx`);
        }

        // 获取扣分数据
        async function fetchDeductions(year, week) {
            return new Promise((resolve, reject) => {
                $.getJSON('getinfo.php?action=get_deductions', { year: year, week: week }, function (data) {
                    resolve(data);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    console.error("获取扣分数据失败: " + textStatus + ", " + errorThrown);
                    reject(new Error("获取扣分数据失败"));
                });
            });
        }
    </script>
</body>

</html>    