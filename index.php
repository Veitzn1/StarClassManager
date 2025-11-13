<?php
session_start();
include 'header.php';
?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班级管理系统首页</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Excel导入导出 -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <!-- PDF导出 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    
</head>

<body class="bg-gray-100">
<div class="container">
    <!-- 年度选择 -->
    <div class="card">
        <div class="flex items-center space-x-2">
            <label for="yearSelector" class="block font-semibold">选择年度：</label>
            <select id="yearSelector" name="yearSelector" class="w-full md:w-1/4"></select>
        </div>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <h2 id="classNumbersDisplay" class="mt-2 text-gray-600"></h2>
            <button id="editButton" onclick="toggleEdit()" class="btn mt-2">编辑年度和班级数量</button>
        <?php endif; ?>
    </div>
    <!-- 设置班级数量表单 -->
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <form id="classSetupForm" class="hidden card">
        <h2 class="text-xl font-bold mb-4">设置班级数量</h2>
        <label for="yearInput" class="block font-semibold mb-1">输入年度：</label>
        <select id="yearInput" name="year" required class="mb-4">
            <?php for ($year = 2023; $year <= 2026; $year++): ?>
                <option value="<?= $year ?>上"><?= $year ?>上</option>
                <option value="<?= $year ?>下"><?= $year ?>下</option>
            <?php endfor; ?>
        </select>
        
        <div class="grade-inputs">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="form-group">
                    <label for="grade<?= $i ?>" class="block font-semibold mb-1"> <?= $i ?> 年级班级数：</label>
                    <input type="number" name="grades[<?= $i ?>]" id="grade<?= $i ?>" required class="mb-4">
                </div>
                
            <?php endfor; ?>
        </div>
        <button type="submit" class="btn">提交班级数量</button>
    </form>
     <?php endif; ?>
    <!-- 周次选择独立组件 -->
    <div class="card week-selector-card">
        <h2 class="text-xl font-bold mb-4">选择周次</h2>
        <form id="weekSelectorForm">
            <div class="form-group mb-4">
                <select id="week" name="week" required class="w-full">
                    <option value="">请选择周次</option>
                    <?php for ($i = 1; $i <= 24; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?>周</option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- 周次扣分登记 -->
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <div class="card">
        <h2 class="text-xl font-bold mb-4">周次扣分设置</h2>
        <form id="deductionForm">
            <!-- 所有新增的 10 个扣分项 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="form-group">
                    <label for="honglingjinClasses">红领巾监督岗扣分班级：</label>
                    <input type="text" id="honglingjinClasses" name="honglingjinClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="indoorExerciseClasses">室内操扣分班级：</label>
                    <input type="text" id="indoorExerciseClasses" name="indoorExerciseClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="outdoorExerciseClasses">室外操扣分班级：</label>
                    <input type="text" id="outdoorExerciseClasses" name="outdoorExerciseClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="morningExerciseTeachersClasses">两操(师)扣分班级：</label>
                    <input type="text" id="morningExerciseTeachersClasses" name="morningExerciseTeachersClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="indoorHygieneClasses">室内卫生扣分班级：</label>
                    <input type="text" id="indoorHygieneClasses" name="indoorHygieneClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="outdoorHygieneClasses">室外卫生扣分班级：</label>
                    <input type="text" id="outdoorHygieneClasses" name="outdoorHygieneClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="hygieneTeacherClasses">卫生（师）扣分班级：</label>
                    <input type="text" id="hygieneTeacherClasses" name="hygieneTeacherClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="civilizedBehaviorClasses">文明行为扣分班级：</label>
                    <input type="text" id="civilizedBehaviorClasses" name="civilizedBehaviorClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="assemblyDisciplineClasses">集会纪律扣分班级：</label>
                    <input type="text" id="assemblyDisciplineClasses" name="assemblyDisciplineClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
                <div class="form-group">
                    <label for="otherDisciplineClasses">其他纪律扣分班级：</label>
                    <input type="text" id="otherDisciplineClasses" name="otherDisciplineClasses" placeholder="例如：1-1,1-2,2-1">
                </div>
            </div>
            <button type="submit" class="btn">提交扣分</button>
        </form>
    </div>
<?php endif; ?>



    <!-- 本周星星班级 -->
    <div id="starClassCard" class="card hidden">
        <h2 class="text-xl font-bold mb-4">星星班级</h2>
        <div class="overflow-x-auto">
            <table id="starClassTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th>年级</th>
                        <th>达标类型</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <!-- 数据将通过 JavaScript 动态填充 -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- 查看扣分结果 -->
    <div class="card">
        <h2 class="text-xl font-bold mb-4">查看扣分详情</h2>
        <div id="deductionWarning" class="p-3 bg-red-100 text-red-700 border-l-4 border-red-500 mb-4 hidden">
            请先选择年度和周次信息
        </div>
        <div id="gradeTabs" class="flex flex-wrap gap-2 mb-4">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <button class="grade-tab <?= $i === 1 ? 'active' : '' ?>" data-grade="<?= $i ?>">
                    <?= $i ?>年级
                </button>
            <?php endfor; ?>
        </div>
        <div class="overflow-x-auto">
            <table id="deductionTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th>班级编号</th>
                    <th>监督岗</th>
                    <th>室内操</th>
                    <th>室外操</th>
                    <th>两操(师)</th>
                    <th>室内卫生</th>
                    <th>室外卫生</th>
                    <th>卫生(师)</th>
                    <th>文明行为</th>
                    <th>集会纪律</th>
                    <th>其他纪律</th>
                    <th>总分</th>
                    <th>达标</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <!-- 数据将通过 JavaScript 动态填充 -->
                </tbody>
            </table>
        </div>
    </div>

<!-- 导入导出功能 -->
<div class="card mt-6 mb-4">
    <!-- 修改 flex 容器的样式，使其在移动端换行 -->
    <div class="flex flex-wrap justify-end space-x-4 space-y-2">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <button id="importExcel" class="btn btn-blue">导入Excel</button>
        <?php endif; ?>
        <button id="exportExcel" class="btn btn-green">导出Excel</button>
        <button id="exportPDF" class="btn btn-red">导出PDF</button>
        <button id="exportStarPDF" class="btn btn-purple">导出星星班级PDF</button>
    </div>
    <input type="file" id="excelFileInput" accept=".xlsx, .xls" style="display:none;">
</div>

    <script>
       function toggleEdit() {
        $('#classSetupForm').toggleClass('hidden');
    }

    // 初始化年份下拉框
    function initYearSelector() {
        reloadYearSelector();
    }

    function reloadYearSelector() {
        $.getJSON('getinfo.php?action=get_years_list', function(years) {
            const $yearSelect = $('#yearSelector');
            $yearSelect.empty();
            if (years.length > 0) {
                years.forEach(function(year) {
                    $yearSelect.append(`<option value="${year}">${year}</option>`);
                });
                const selectedYear = years[0];
                $yearSelect.val(selectedYear);
                fetchClassNumbers(selectedYear);
            } else {
                $yearSelect.append('<option value="">暂无可用年度</option>');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("获取年份失败: " + textStatus + ", " + errorThrown);
        });
    }

    function fetchClassNumbers(year) {
        $.getJSON('getinfo.php?action=get_current_year_and_classes', { year: year }, function(data) {
            $('#classNumbersDisplay').text('当前班级数: ' +
                Object.entries(data.grades)
                    .map(([grade, count]) => `${grade}年级: ${count}个班`)
                    .join('、 ')
            );
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("获取班级数量失败: " + textStatus + ", " + errorThrown);
        });
    }

   $(document).ready(function () {
        initYearSelector();
        $('#yearSelector').on('change', function () {
            const selectedYear = $(this).val(); // ✅ 移除 parseInt
            fetchClassNumbers(selectedYear);
        });
        $('#classSetupForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('save_data.php?action=save_classes', formData, function (response) {
                alert('班级数量已保存');
                reloadYearSelector();
            }).fail(function (xhr, status, error) {
                alert("保存失败：" + error);
            });
        });
        $('#deductionForm').on('submit', function(event) {
            event.preventDefault();
            const week = $('#week').val();
            const deductions = {
                honglingjin: $('#honglingjinClasses').val().split(',').map(c => c.trim()).filter(c => c),
                indoor_exercise: $('#indoorExerciseClasses').val().split(',').map(c => c.trim()).filter(c => c),
                outdoor_exercise: $('#outdoorExerciseClasses').val().split(',').map(c => c.trim()).filter(c => c),
                morning_exercise_teachers: $('#morningExerciseTeachersClasses').val().split(',').map(c => c.trim()).filter(c => c),
                indoor_hygiene: $('#indoorHygieneClasses').val().split(',').map(c => c.trim()).filter(c => c),
                outdoor_hygiene: $('#outdoorHygieneClasses').val().split(',').map(c => c.trim()).filter(c => c),
                hygiene_teachers: $('#hygieneTeacherClasses').val().split(',').map(c => c.trim()).filter(c => c),
                civilized_behavior: $('#civilizedBehaviorClasses').val().split(',').map(c => c.trim()).filter(c => c),
                assembly_discipline: $('#assemblyDisciplineClasses').val().split(',').map(c => c.trim()).filter(c => c),
                other_discipline: $('#otherDisciplineClasses').val().split(',').map(c => c.trim()).filter(c => c)
            };
            $.ajax({
                url: 'save_data.php?action=save_deductions',
                type: 'POST',
                data: {
                    year: $('#yearSelector').val(),
                    week: week,
                    deductions: JSON.stringify(deductions)
                },
                success: function(response) {
                    try {
                        const json = JSON.parse(response);
                        alert(json.message || '保存成功');
                    } catch (e) {
                        alert("保存失败");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("保存扣分失败: " + status + ", " + error);
                }
            });
        });

        displayDeductions(1);
        $('.grade-tab[data-grade="1"]').addClass('active');

        $('.grade-tab').on('click', function () {
            const grade = $(this).data('grade');
            displayDeductions(grade);
        });

        $('#week').on('change', function () {
            const grade = $('.grade-tab.active').data('grade');
            displayDeductions(grade);
        });

        $('#yearSelector').on('change', function () {
            const grade = $('.grade-tab.active').data('grade');
            displayDeductions(grade);
        });
    });

    async function fetchDeductions(year, week) {
        return new Promise((resolve, reject) => {
            $.getJSON('getinfo.php?action=get_deductions', { year: year, week: week }, function(data) {
                resolve(data);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("获取扣分数据失败: " + textStatus + ", " + errorThrown);
                reject(new Error("获取扣分数据失败"));
            });
        });
    }

    function getStarIcon(totalPoints) {
        return totalPoints === 100 ? '⭐' : '';
    }

    function updateStarClassTable(data) {
        const starClassTable = $('#starClassTable tbody');
        starClassTable.empty();
        let hasData = false;

        // 按年级分组
        const groupedData = {};
        data.forEach(item => {
            const grade = item.grade;
            if (!groupedData[grade]) groupedData[grade] = [];

            if (parseFloat(item.total) === 100) {
                groupedData[grade].push(item);
                hasData = true;
            }
        });

        // 填充数据
        if (hasData) {
            for (let grade = 1; grade <= 6; grade++) {
                if (groupedData[grade] && groupedData[grade].length > 0) {
                    const gradeName = `${grade}年级`;
                    const classNumbers = groupedData[grade].map(item => `${item.grade}-${item.class}`).join('、 ');
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td>${gradeName}</td>
                            <td>
                                ${classNumbers}
                                ${groupedData[grade].every(item => parseFloat(item.total) === 100) ? '⭐' : ''}
                            </td>
                        </tr>`;
                    starClassTable.append(row);
                }
            }
            $('#starClassCard').removeClass('hidden');
        } else {
            $('#starClassCard').addClass('hidden');
        }
    }

    async function displayDeductions(grade) {
                const selectedYear = $('#yearSelector').val(); // ✅ 移除 parseInt
                const selectedWeek = parseInt($('#week').val());
                if (!selectedYear || !selectedWeek) { // ✅ 修改判断条件
                    $('#deductionWarning').removeClass('hidden');
                    $('#deductionTable tbody').empty();
                    $('#starClassCard').addClass('hidden');
                    return;
                } else {
                    $('#deductionWarning').addClass('hidden');
                }
    
                try {
                const deductionsData = await fetchDeductions(selectedYear, selectedWeek);

            // 为每个数据对象添加 total 字段
            const dataWithTotal = deductionsData.map(item => {
                const totalPoints = [
                    item.honglingjin, item.indoor_exercise, item.outdoor_exercise,
                    item.morning_exercise_teachers, item.indoor_hygiene,
                    item.outdoor_hygiene, item.hygiene_teachers, item.civilized_behavior,
                    item.assembly_discipline, item.other_discipline
                ].reduce((sum, val) => sum + parseFloat(val || 0), 0);

                return {
                    ...item,
                    total: totalPoints
                };
            });

            const filteredData = dataWithTotal.filter(d => d.grade == grade);

            // 更新星星班级表格
            updateStarClassTable(dataWithTotal);

            // 渲染扣分表格
            $('#deductionTable tbody').empty();
            if (filteredData.length === 0) {
                $('#deductionTable tbody').append('<tr><td colspan="13" class="text-center py-4">暂无该年级的扣分记录</td></tr>');
            } else {
                filteredData.forEach(d => {
                    const starIcon = getStarIcon(d.total);
                    const fullClass = `${d.grade}.${d.class}`;
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td>${fullClass}</td>
                            <td>${d.honglingjin}</td>
                            <td>${d.indoor_exercise}</td>
                            <td>${d.outdoor_exercise}</td>
                            <td>${d.morning_exercise_teachers}</td>
                            <td>${d.indoor_hygiene}</td>
                            <td>${d.outdoor_hygiene}</td>
                            <td>${d.hygiene_teachers}</td>
                            <td>${d.civilized_behavior}</td>
                            <td>${d.assembly_discipline}</td>
                            <td>${d.other_discipline}</td>
                            <td>${d.total.toFixed(1)}</td>
                            <td>${starIcon}</td>
                        </tr>`;
                    $('#deductionTable tbody').append(row);
                });
            }

            $('.grade-tab').removeClass('active');
            $(`.grade-tab[data-grade="${grade}"]`).addClass('active');
            document.getElementById('exportPDF').style.display = 'block';
        } catch (error) {
            console.error("加载扣分数据失败:", error);
            $('#starClassCard').addClass('hidden'); // 出错时隐藏
        }
    }

    // excel的导出
    document.getElementById('exportExcel').addEventListener('click', async () => {
        // 获取当前选中的年份和周次
        const selectedYear = $('#yearSelector').val();
        const selectedWeek = $('#week').val();
        // 验证输入
        if (!selectedYear || !selectedWeek) {
            alert("请选择年份和周次！");
            return;
        }
        // 获取扣分数据
        const deductionsData = await fetchDeductions(selectedYear, selectedWeek);
        // 如果没有数据，提示用户
        if (deductionsData.length === 0) {
            alert("当前周次没有扣分记录！");
            return;
        }
 // 创建工作簿
        const wb = XLSX.utils.book_new();
        // 定义年级数组
        const grades = [1, 2, 3, 4, 5, 6];
        grades.forEach(grade => {
            // 过滤当前年级的数据
            const filteredData = deductionsData.filter(d => d.grade == grade);
            if (filteredData.length > 0) {
                // 创建 Excel 表头
                const mainHeader = [`${selectedYear}年第${selectedWeek}周班级常规`];
                const subHeader = [
                    "班级编号", "监督岗", "室内操", "室外操", "两操(师)", "室内卫生",
                    "室外卫生", "卫生(师)", "文明行为", "集会纪律", "其他纪律", "总分", "达标"
                ];
                const rows = [mainHeader, subHeader];
                // 转换数据到表格行
                filteredData.forEach(row => {
                    const totalPoints = [
                        row.honglingjin, row.indoor_exercise, row.outdoor_exercise, row.morning_exercise_teachers,
                        row.indoor_hygiene, row.outdoor_hygiene, row.hygiene_teachers, row.civilized_behavior,
                        row.assembly_discipline, row.other_discipline
                    ].reduce((sum, val) => sum + parseFloat(val || 0), 0).toFixed(1);
                    const starIcon = totalPoints === "100.0" ? "⭐" : "";
                    const rowData = [
                        `${row.grade}-${row.class}`, // 班级编号（如1-1）
                        row.honglingjin,
                        row.indoor_exercise,
                        row.outdoor_exercise,
                        row.morning_exercise_teachers,
                        row.indoor_hygiene,
                        row.outdoor_hygiene,
                        row.hygiene_teachers,
                        row.civilized_behavior,
                        row.assembly_discipline,
                        row.other_discipline,
                        totalPoints,
                        starIcon
                    ];
                    rows.push(rowData);
                });
                // 生成工作表
                const ws = XLSX.utils.aoa_to_sheet(rows);
                // 合并主表头单元格
                XLSX.utils.sheet_add_aoa(ws, [Array(subHeader.length).fill(null)], { origin: 'A1' });
                ws['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: subHeader.length - 1 } }];
                // 将工作表添加到工作簿
                XLSX.utils.book_append_sheet(wb, ws, `${grade}年级`);
            }
        });
        // 保存 Excel 文件
        XLSX.writeFile(wb, `${selectedYear}年第${selectedWeek}周班级常规.xlsx`);
    });

    // pdf的导出
    document.getElementById('exportPDF').addEventListener('click', async () => {
        const selectedYear = $('#yearSelector').val();
        const selectedWeek = $('#week').val();
        if (!selectedYear || !selectedWeek) {
            alert("请选择年份和周次！");
            return;
        }
        try {
            const deductionsData = await fetchDeductions(selectedYear, selectedWeek);
            if (deductionsData.length === 0) {
                alert("当前周次没有扣分记录！");
                return;
            }
            // 创建 PDF 实例
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('l', 'mm', 'a4'); // A4 横向
            const pdfWidth = pdf.internal.pageSize.getWidth(); // ~297mm
            const pdfHeight = pdf.internal.pageSize.getHeight(); // ~210mm
            // 遍历年级 1~6
            for (let grade = 1; grade <= 6; grade++) {
                const gradeData = deductionsData.filter(d => d.grade == grade);
                if (gradeData.length === 0) continue;
                // 创建临时 DOM 容器
                const container = document.createElement('div');
                container.style.width = '1400px';
                container.style.padding = '20px';
                container.style.backgroundColor = '#fff';
                container.style.fontFamily = '"Microsoft YaHei", sans-serif';
                // 添加标题
                const title = document.createElement('h3');
                title.innerText = `${selectedYear}年第${selectedWeek}周 ${grade}年级班级常规`;
                title.style.fontSize = '20px';
                title.style.marginBottom = '25px';
                title.style.textAlign = 'center';
                title.style.backgroundColor = 'white';
                container.appendChild(title);
                // 克隆并过滤当前年级的表格数据
                const table = document.getElementById('deductionTable').cloneNode(true);
                const tbody = table.querySelector('tbody');
                tbody.innerHTML = ''; // 清空原有内容
                // 填充当前年级数据
                gradeData.forEach(row => {
                    const totalPoints = [
                        row.honglingjin, row.indoor_exercise, row.outdoor_exercise,
                        row.morning_exercise_teachers, row.indoor_hygiene,
                        row.outdoor_hygiene, row.hygiene_teachers, row.civilized_behavior,
                        row.assembly_discipline, row.other_discipline
                    ].reduce((sum, val) => sum + parseFloat(val || 0), 0).toFixed(1);
                    const starIcon = totalPoints === "100.0" ? "⭐" : "";
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.grade}-${row.class}</td>
                        <td>${row.honglingjin}</td>
                        <td>${row.indoor_exercise}</td>
                        <td>${row.outdoor_exercise}</td>
                        <td>${row.morning_exercise_teachers}</td>
                        <td>${row.indoor_hygiene}</td>
                        <td>${row.outdoor_hygiene}</td>
                        <td>${row.hygiene_teachers}</td>
                        <td>${row.civilized_behavior}</td>
                        <td>${row.assembly_discipline}</td>
                        <td>${row.other_discipline}</td>
                        <td>${totalPoints}</td>
                        <td>${starIcon}</td>
                    `;
                    tbody.appendChild(tr);
                });
                // 将表格加入容器
                container.appendChild(table);
                // 插入 DOM 以便 html2canvas 正确渲染
                document.body.appendChild(container);
                // 使用 html2canvas 截图
                const canvas = await html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff'
                });
                // 移除临时 DOM
                document.body.removeChild(container);
                // 获取图像尺寸
                const imgData = canvas.toDataURL('image/jpeg');
                const imgProps = pdf.getImageProperties(imgData);
                const imgHeight = (imgProps.height * pdfWidth) / imgProps.width;
                // 添加图片到 PDF
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, imgHeight);
                // 如果不是最后一个年级，添加新页
                if (grade !== 6) {
                    pdf.addPage();
                }
            }
            // 保存 PDF
            pdf.save(`${selectedYear}年第${selectedWeek}周班级常规.pdf`);
        } catch (error) {
            console.error("PDF导出失败:", error);
            alert("PDF导出失败，请查看控制台日志");
        }
    });

    
// 导出星星班级为PDF
document.getElementById('exportStarPDF').addEventListener('click', async () => {
    if ($('#starClassCard').hasClass('hidden')) {
        alert("当前无星星班级数据，无法导出！");
        return;
    }

    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const maxImageHeight = pdfHeight * 0.9;

        const container = document.createElement('div');
        container.style.width = '1400px';
        container.style.padding = '20px';
        container.style.backgroundColor = '#fff';
        container.style.fontFamily = '"Microsoft YaHei", sans-serif';

        const title = document.createElement('h3');
        title.innerText = `${$('#yearSelector').val()}年第${$('#week').val()}周 星星班级`;
        title.style.fontSize = '20px';
        title.style.marginBottom = '25px';
        title.style.textAlign = 'center';
        container.appendChild(title);

        const starTable = document.getElementById('starClassTable').cloneNode(true);
        container.appendChild(starTable);

        document.body.appendChild(container);

        const canvas = await html2canvas(container, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        });

        document.body.removeChild(container);

        const imgData = canvas.toDataURL('image/jpeg', 0.8); // 使用 JPEG 格式

        const imgProps = pdf.getImageProperties(imgData);
        let imgWidth = imgProps.width;
        let imgHeight = imgProps.height;

        imgWidth = pdfWidth;
        imgHeight = (imgProps.height * imgWidth) / imgProps.width;

        if (imgHeight > maxImageHeight) {
            const scale = maxImageHeight / imgHeight;
            imgWidth *= scale;
            imgHeight *= scale;
        }

        pdf.addImage(imgData, 'JPEG', 0, 0, imgWidth, imgHeight);
        pdf.save(`${$('#yearSelector').val()}年第${$('#week').val()}周_星星班级.pdf`);
    } catch (error) {
        console.error("星星班级PDF导出失败:", error);
        alert("星星班级PDF导出失败，请查看控制台日志");
    }
});

//Excel导入
document.getElementById('importExcel').addEventListener('click', () => {
    const fileInput = document.getElementById('excelFileInput');
    if (fileInput) {
        fileInput.click(); // 触发文件选择框
    } else {
        console.error("未找到文件输入框");
    }
});
document.getElementById('excelFileInput').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });

            // 获取当前选中的年份和周次
            const year = $('#yearSelector').val();
            const week = $('#week').val();

            if (!year || !week) {
                alert("请先选择年份和周次！");
                return;
            }

            const allData = [];

            // 遍历所有工作表（每个年级一个工作表）
            workbook.SheetNames.forEach(sheetName => {
                const worksheet = workbook.Sheets[sheetName];
                const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                // 跳过标题行（第一行是合并的主标题，第二行是表头）
                const headers = json[1]; // 第二行为表头
                const rows = json.slice(2).filter(row => row.length > 0); // 数据行从第三行开始

                rows.forEach(row => {
                    const rowData = {};
                    headers.forEach((header, i) => {
                        rowData[header] = row[i];
                    });

                    // 提取班级编号（如 "1-1" → grade=1, class=1）
                    const [grade, classNum] = rowData["班级编号"].split("-");

                    // 构建标准化数据（不包含 total 和 is_star）
                    const deductionEntry = {
                        year,
                        week,
                        grade: parseInt(grade),
                        class: parseInt(classNum),
                        honglingjin: parseFloat(rowData["监督岗"]) || 0,
                        indoor_exercise: parseFloat(rowData["室内操"]) || 0,
                        outdoor_exercise: parseFloat(rowData["室外操"]) || 0,
                        morning_exercise_teachers: parseFloat(rowData["两操(师)"]) || 0,
                        indoor_hygiene: parseFloat(rowData["室内卫生"]) || 0,
                        outdoor_hygiene: parseFloat(rowData["室外卫生"]) || 0,
                        hygiene_teachers: parseFloat(rowData["卫生(师)"]) || 0,
                        civilized_behavior: parseFloat(rowData["文明行为"]) || 0,
                        assembly_discipline: parseFloat(rowData["集会纪律"]) || 0,
                        other_discipline: parseFloat(rowData["其他纪律"]) || 0
                    };

                    allData.push(deductionEntry);
                });
            });

            // 发送数据到后端
            const response = await fetch('save_data.php?action=import_deductions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ data: allData })
            });

            if (!response.ok) throw new Error("导入失败");

            alert("导入成功");
            displayDeductions($('.grade-tab.active').data('grade')); // 刷新当前年级数据

        } catch (error) {
            console.error("导入失败:", error);
            alert("导入失败：" + error.message);
        }
    };

    reader.readAsArrayBuffer(file);
});
</script>
</body>
</html>