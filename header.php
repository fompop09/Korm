<!-- header.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการข้อมูลบุคลากร</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4776E6;
            --text-light: rgba(255, 255, 255, 0.9);
        }

        body {
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .navbar-nav {
            gap: 0.5rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: var(--text-light) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        /* User Menu */
        .user-menu {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            color: var(--text-light);
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .user-profile img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logout-btn {
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
            color: var(--text-light);
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        @media (max-width: 991.98px) {
            .user-menu {
                margin-top: 1rem;
                width: 100%;
                flex-direction: column;
            }

            .user-profile, .logout-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i>
                ระบบบุคลากร
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-house-door"></i> หน้าแรก
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                                <i class="bi bi-people"></i> จัดการผู้ใช้
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_personnel.php' ? 'active' : '' ?>" href="manage_personnel.php">
                                <i class="bi bi-people-fill"></i> จัดการบุคลากร
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_positions.php' ? 'active' : '' ?>" href="manage_positions.php">
                                <i class="bi bi-briefcase"></i> จัดการตำแหน่ง
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                                <i class="bi bi-file-text"></i> รายงาน
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="user-menu">
                    <a href="profile.php" class="user-profile">
                        <img src="<?= $_SESSION['image_path'] ?? 'images/default-profile.png' ?>" alt="Profile">
                        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                    </a>
                    <a href="logout.php" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/react@17/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/recharts/umd/Recharts.js"></script>