<?php
// login.php
session_start();
require_once 'config.php';
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['image_path'] = $user['image_path'];

        $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);

        header('Location: index.php');
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบบริหารจัดการข้อมูลบุคลากร</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .background-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 1;
        }

        .circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            animation: float 15s infinite;
            border-radius: 50%;
        }

        .circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -100px;
        }

        .circle:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -50px;
            animation-delay: 3s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
            }

            100% {
                transform: translateY(0) rotate(360deg);
            }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: #2c5364;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .login-header p {
            color: #666;
            margin-bottom: 0;
        }

        .form-control {
            border: 2px solid #e1e1e1;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #2c5364;
            box-shadow: 0 0 0 3px rgba(44, 83, 100, 0.1);
        }

        .form-label {
            color: #2c5364;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2c5364;
            z-index: 10;
        }

        .input-with-icon {
            padding-left: 40px;
        }

        .btn-login {
            background: linear-gradient(135deg, #2c5364, #203a43);
            border: none;
            padding: 0.8rem;
            border-radius: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #203a43, #2c5364);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .alert-custom {
            background: rgba(220, 53, 69, 0.1);
            border: none;
            border-radius: 12px;
            color: #dc3545;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .version-info {
            position: absolute;
            bottom: 20px;
            right: 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            z-index: 2;
        }
    </style>
</head>

<body>
    <div class="background-animation">
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>ยินดีต้อนรับ</h2>
                <p>เข้าสู่ระบบเพื่อจัดการข้อมูลบุคลากร</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-custom">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="username" class="form-control input-with-icon" required
                            placeholder="กรุณากรอกชื่อผู้ใช้">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">รหัสผ่าน</label>
                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control input-with-icon"
                            required placeholder="กรุณากรอกรหัสผ่าน">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <script>
                    const togglePassword = document.getElementById('togglePassword');
                    const password = document.getElementById('password');
                    const toggleIcon = document.getElementById('toggleIcon');

                    togglePassword.addEventListener('click', function () {
                        // Toggle type attribute
                        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                        password.setAttribute('type', type);

                        // Toggle icon
                        toggleIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
                    });
                </script>

                <button type="submit" class="btn btn-login btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                </button>
            </form>
        </div>
    </div>

    <div class="version-info">
        Version 1.0.0 | Developed by Devtaiban
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>