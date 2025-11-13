<!-- 文件名称：class_radar_chart.php
         作用：提供一个页面用于展示班级指标蛛网图，用户可选择年度，页面会根据选择的年度加载相应的扣分数据并生成蛛网图，同时支持下载所有图片的功能 -->
         <?php
session_start();
include 'header.php';
?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班级指标蛛网图</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e6f7ff 0%, #f2f9ff 100%);
            color: #333;
            line-height: 1.6;
            margin: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-align: center;
        }

        select {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .chart-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        button {
            padding: 0.5rem 1rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>班级指标蛛网图</h1>
        <select id="yearSelector">
            <option value="">请选择年度</option>
        </select>
        <div class="charts-container" id="chartsContainer"></div>
        <button id="downloadAllButton">下载所有图片</button>
    </div>

    <script>
        function initYearSelector() {
            fetch('getinfo.php?action=get_years_list')
              .then(response => response.json())
              .then(years => {
                    const yearSelect = document.getElementById('yearSelector');
                    yearSelect.innerHTML = '';
                    if (years.length === 0) {
                        const option = document.createElement('option');
                        option.textContent = '暂无可用年度';
                        option.disabled = true;
                        yearSelect.appendChild(option);
                        return;
                    }

                    years.sort((a, b) => b - a);
                    years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        yearSelect.appendChild(option);
                    });

                    // 自动选择第一个年份
                    if (years.length > 0) {
                        yearSelect.value = years[0];
                        loadCharts(years[0]);
                    }
                })
              .catch(error => {
                    console.error('获取年份失败:', error);
                    const yearSelect = document.getElementById('yearSelector');
                    yearSelect.innerHTML = '<option value="" disabled>加载年份失败</option>';
                });
        }

        function loadCharts(year) {
            const chartsContainer = document.getElementById('chartsContainer');
            chartsContainer.innerHTML = '';

            fetch(`getinfo.php?action=get_deductions_by_year&year=${year}`)
              .then(response => response.json())
              .then(data => {
                    if (!data.success || !data.data) {
                        console.error('获取数据失败:', data.message);
                        return;
                    }

                    const gradeData = {};
                    data.data.forEach(entry => {
                        const grade = entry.grade;
                        if (!gradeData[grade]) {
                            gradeData[grade] = [];
                        }
                        gradeData[grade].push(entry);
                    });

                    Object.keys(gradeData).forEach(grade => {
                        const gradeEntries = gradeData[grade];
                        const canvas = document.createElement('canvas');
                        const chartCard = document.createElement('div');
                        chartCard.classList.add('chart-card');
                        chartCard.appendChild(canvas);
                        chartsContainer.appendChild(chartCard);

                        const ctx = canvas.getContext('2d');
                        const labels = ['红领巾监督岗', '室内操', '室外操', '两操(师)', '室内卫生', '室外卫生', '卫生（师）', '文明行为', '集会纪律', '其他纪律'];
                        const datasets = gradeEntries.map(entry => {
                            return {
                                label: `${grade}年级${entry.class}班`,
                                data: [
                                    entry.honglingjin,
                                    entry.indoor_exercise,
                                    entry.outdoor_exercise,
                                    entry.morning_exercise_teachers,
                                    entry.indoor_hygiene,
                                    entry.outdoor_hygiene,
                                    entry.hygiene_teachers,
                                    entry.civilized_behavior,
                                    entry.assembly_discipline,
                                    entry.other_discipline
                                ],
                                borderColor: getRandomColor(),
                                backgroundColor: getRandomColor(0.2),
                                borderWidth: 2
                            };
                        });

                        new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: labels,
                                datasets: datasets
                            },
                            options: {
                                elements: {
                                    line: {
                                        tension: 0.1
                                    }
                                },
                                scales: {
                                    r: {
                                        min: 0,
                                        max: 10,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: `${grade}年级班级指标蛛网图`
                                    }
                                }
                            }
                        });

                        const downloadButton = document.createElement('button');
                        downloadButton.textContent = `下载 ${grade} 年级图片`;
                        downloadButton.addEventListener('click', () => {
                            const link = document.createElement('a');
                            link.href = canvas.toDataURL('image/png');
                            link.download = `${year}-${grade}年级班级指标蛛网图.png`;
                            link.click();
                        });
                        chartCard.appendChild(downloadButton);
                    });
                })
              .catch(error => {
                    console.error('加载图表失败:', error);
                });
        }

        function getRandomColor(alpha = 1) {
            const r = Math.floor(Math.random() * 256);
            const g = Math.floor(Math.random() * 256);
            const b = Math.floor(Math.random() * 256);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        document.getElementById('yearSelector').addEventListener('change', function () {
            const selectedYear = this.value;
            if (selectedYear) {
                loadCharts(selectedYear);
            }
        });

        document.getElementById('downloadAllButton').addEventListener('click', function () {
            const zip = new JSZip();
            const chartsContainer = document.getElementById('chartsContainer');
            const canvasElements = chartsContainer.querySelectorAll('canvas');
            const year = document.getElementById('yearSelector').value;

            canvasElements.forEach((canvas, index) => {
                const dataURL = canvas.toDataURL('image/png');
                const base64Data = dataURL.replace(/^data:image\/(png|jpg);base64,/, '');
                zip.file(`${year}-年级${index + 1}班级指标蛛网图.png`, base64Data, { base64: true });
            });

            zip.generateAsync({ type: 'blob' })
              .then(function (content) {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(content);
                    link.download = `${year}-所有班级指标蛛网图.zip`;
                    link.click();
                });
        });

        window.onload = function () {
            initYearSelector();
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
</body>

</html>