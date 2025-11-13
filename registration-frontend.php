
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e6f7ff 0%, #f2f9ff 100%);
            color: #333;
            line-height: 1.6;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        input {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 1rem;
            width: 100%;
        }

        button {
            padding: 0.5rem 1rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>用户注册</h1>
        <form id="registrationForm">
            <input type="text" id="username" placeholder="用户名" required>
            <input type="password" id="password" placeholder="密码" required>
            <button type="submit">注册</button>
        </form>
    </div>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

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
                        window.location.href = 'index.php';
                    } else {
                        alert('注册失败: ' + data.message);
                    }
                })
              .catch(error => {
                    alert('网络错误，请重试');
                });
        });
    </script>
</body>

</html>