<?php
// index.php
session_start();
require_once 'config.php';
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getConnection();

// Get user info
$stmt = $conn->prepare("
    SELECT u.*, p.*
    FROM users u
    LEFT JOIN personnel p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get personnel statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_teachers' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
    'total_staff' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn(),
    'total_admin' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn()
];

// Get monthly login statistics (last 6 months)
$monthlyLogins = $conn->query("
    SELECT DATE_FORMAT(last_login, '%Y-%m') as month, 
           COUNT(*) as login_count
    FROM users
    WHERE last_login >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month
")->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities
$recent_activities = $conn->query("
    SELECT u.username, u.role, u.last_login
    FROM users u
    WHERE last_login IS NOT NULL
    ORDER BY last_login DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<style>
    .dashboard-container {
        padding: 2rem 0;
        min-height: calc(100vh - 180px);
        background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
    }

    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .chart-title {
        color: #2c5364;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 600;
        color: #2c5364;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .role-distribution {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
</style>

<div class="dashboard-container">
    <div class="container">
        <!-- Welcome Section -->
        <div class="stats-card mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?= $user['image_path'] ?? 'images/default-profile.png' ?>" class="rounded-circle"
                        style="width: 80px; height: 80px; object-fit: cover;">
                </div>
                <div class="col">
                    <h3 class="mb-2">ยินดีต้อนรับ,
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                    <p class="mb-0 text-muted">
                        <i class="bi bi-clock me-2"></i>เข้าสู่ระบบล่าสุด:
                        <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <?php
        // ดึงข้อมูลจำนวนการเข้าใช้งานรายเดือน
        $monthlyLogins = $conn->query("
    SELECT 
        DATE_FORMAT(last_login, '%Y-%m') as month,
        DATE_FORMAT(last_login, '%M') as month_name,
        COUNT(*) as login_count
    FROM users
    WHERE last_login >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month, month_name
    ORDER BY month
")->fetchAll(PDO::FETCH_ASSOC);

        // ดึงข้อมูลจำนวนผู้ใช้แต่ละประเภท
        $roleStats = $conn->query("
    SELECT 
        role,
        COUNT(*) as count
    FROM users 
    GROUP BY role
")->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <!-- เพิ่ม Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <h5 class="chart-title">
                        <i class="bi bi-bar-chart-fill me-2"></i>สถิติการเข้าใช้งานรายเดือน
                    </h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="monthlyLoginChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <h5 class="chart-title">
                        <i class="bi bi-pie-chart-fill me-2"></i>สัดส่วนบุคลากร
                    </h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="roleDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // เก็บอ้างอิงไปยังแผนภูมิ
            let monthlyChart = null;
            let roleChart = null;

            // แปลงข้อมูลจาก PHP เป็น JavaScript
            const monthlyData = {
                labels: <?= json_encode(array_map(function ($item) {
                    return date('M Y', strtotime($item['month'] . '-01'));
                }, $monthlyLogins)) ?>,
                datasets: [{
                    label: 'จำนวนครั้งการเข้าใช้งาน',
                    data: <?= json_encode(array_map(function ($item) {
                        return $item['login_count'];
                    }, $monthlyLogins)) ?>,
                    backgroundColor: '#1e3c72',
                    borderColor: '#1e3c72',
                    borderWidth: 1
                }]
            };

            const roleData = {
                labels: <?= json_encode(array_map(function ($item) {
                    $roleNames = [
                        'admin' => 'ผู้ดูแลระบบ',
                        'teacher' => 'ครู',
                        'staff' => 'เจ้าหน้าที่'
                    ];
                    return $roleNames[$item['role']] ?? $item['role'];
                }, $roleStats)) ?>,
                datasets: [{
                    data: <?= json_encode(array_map(function ($item) {
                        return $item['count'];
                    }, $roleStats)) ?>,
                    backgroundColor: ['#1e3c72', '#2a5298', '#4776E6'],
                    borderWidth: 0
                }]
            };

            // ฟังก์ชันสร้างแผนภูมิ
            function createCharts() {
                // ล้างแผนภูมิเก่าถ้ามี
                if (monthlyChart) {
                    monthlyChart.destroy();
                }
                if (roleChart) {
                    roleChart.destroy();
                }

                // สร้างแผนภูมิแท่ง
                const monthlyCtx = document.getElementById('monthlyLoginChart').getContext('2d');
                monthlyChart = new Chart(monthlyCtx, {
                    type: 'bar',
                    data: monthlyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `จำนวน: ${context.formattedValue} ครั้ง`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    callback: function (value) {
                                        return value + ' ครั้ง';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // สร้างแผนภูมิวงกลม
                const roleCtx = document.getElementById('roleDistributionChart').getContext('2d');
                roleChart = new Chart(roleCtx, {
                    type: 'doughnut',
                    data: roleData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((context.raw / total) * 100);
                                        return `${context.label}: ${context.formattedValue} คน (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '50%'
                    }
                });
            }

            // สร้างแผนภูมิเมื่อโหลดหน้าเว็บ
            document.addEventListener('DOMContentLoaded', createCharts);
        </script>

        <style>
            .chart-container {
                background: white;
                border-radius: 20px;
                padding: 1.5rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                margin-bottom: 1.5rem;
                transition: all 0.3s ease;
            }

            .chart-container:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            }

            .chart-title {
                color: #2c5364;
                font-weight: 600;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
            }
        </style>

        <div class="row">
            <!-- Quick Actions -->
            <div class="col-md-6 mb-4">
                <div class="stats-card h-100">
                    <h5 class="chart-title">เมนูด่วน</h5>
                    <div class="d-grid gap-3">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="bi bi-person-circle me-2"></i>แก้ไขข้อมูลส่วนตัว
                        </a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="manage_users.php" class="btn btn-outline-success">
                                <i class="bi bi-people-fill me-2"></i>จัดการผู้ใช้
                            </a>
                            <a href="reports.php" class="btn btn-outline-info">
                                <i class="bi bi-file-earmark-text me-2"></i>ดูรายงาน
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-6 mb-4">
                <div class="stats-card h-100">
                    <h5 class="chart-title">กิจกรรมล่าสุด</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="bi bi-person-circle me-2"></i>
                                            <?= htmlspecialchars($activity['username']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($activity['role']) ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($activity['last_login'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>