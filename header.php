<!-- 文件名称：header.php
         作用：提供页面的头部部分，包含导航栏、登录和注册按钮等，用于整个项目的页面头部展示 -->
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班级管理系统</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        nav {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .login-register {
            display: flex;
            align-items: center;
        }

        .login-register button {
            background-color: transparent;
            color: white;
            border: none;
            cursor: pointer;
            margin-left: 1rem;
        }

        .login-register button:hover {
            text-decoration: underline;
        }

        /* 模态框样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal form {
            display: flex;
            flex-direction: column;
        }

        .modal input {
            margin: 0.5rem 0;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal button {
            margin-top: 1rem;
            padding: 0.5rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <nav>
        <div>
            <a href="index.php">首页</a>
            <a href="class_radar_chart.php">班级量化</a>
            <a href="correction-page.php">更正</a>
        </div>
        <div class="login-register">
            <?php
            session_start();
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                echo '<button onclick="logout()">注销</button>';
            } else {
                echo '<button onclick="openLoginModal()">登录</button>';
                echo '<button onclick="openRegistrationModal()">注册</button>';
            }
            ?>
        </div>
    </nav>

    <!-- 登录模态框 -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <h2>登录</h2>
            <form id="loginForm">
                <input type="text" id="loginUsername" placeholder="用户名" required>
                <input type="password" id="loginPassword" placeholder="密码" required>
                <button type="submit">登录</button>
            </form>
        </div>
    </div>

    <!-- 注册模态框 -->
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRegistrationModal()">&times;</span>
            <h2>注册</h2>
            <form id="registrationForm">
                <input type="text" id="registrationUsername" placeholder="用户名" required>
                <input type="password" id="registrationPassword" placeholder="密码" required>
                <button type="submit">注册</button>
            </form>
        </div>
    </div>

    <script>
        function openLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        function openRegistrationModal() {
            document.getElementById('registrationModal').style.display = 'block';
        }

        function closeRegistrationModal() {
            document.getElementById('registrationModal').style.display = 'none';
        }

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const username = document.getElementById('loginUsername').value;
            const password = document.getElementById('loginPassword').value;
            // 这里需要用户根据实际情况修改为正确的登录接口地址
            fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
              .then(response => response.json())
              .then(data => {
                    if (data.success) {
                        alert('登录成功');
                        closeLoginModal();
                        location.reload();
                    } else {
                        alert('登录失败: ' + data.message);
                    }
                })
              .catch(error => {
                    alert('网络错误，请重试');
                });
        });

        document.getElementById('registrationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const username = document.getElementById('registrationUsername').value;
            const password = document.getElementById('registrationPassword').value;
            // 这里需要用户根据实际情况修改为正确的注册接口地址
            fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
              .then(response => response.json())
              .then(data => {
                    if (data.success) {
                        alert('注册成功，请登录');
                        closeRegistrationModal();
                    } else {
                        alert('注册失败: ' + data.message);
                    }
                })
              .catch(error => {
                    alert('网络错误，请重试');
                });
        });

        function logout() {
            // 这里需要用户根据实际情况修改为正确的注销接口地址
            fetch('logout.php', {
                method: 'POST'
            })
              .then(() => {
                    location.reload();
                })
              .catch(error => {
                    alert('网络错误，请重试');
                });
        }
    </script>
</body>

</html>