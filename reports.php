<?php
// reports.php
session_start();
require_once 'config.php';
require_once 'connection.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$conn = getConnection();

// ดึงข้อมูลสถิติรวม
$stats = [
    'total_personnel' => $conn->query("SELECT COUNT(*) FROM personnel")->fetchColumn(),
    'total_positions' => $conn->query("SELECT COUNT(*) FROM positions")->fetchColumn()
];

// ดึงข้อมูลจำนวนบุคลากรแยกตามตำแหน่ง
$positionStats = $conn->query("
    SELECT 
        pos.position_name,
        pos.position_code,
        COUNT(p.personnel_id) as count
    FROM positions pos
    LEFT JOIN personnel p ON pos.position_id = p.position_id
    GROUP BY pos.position_id
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลบุคลากรทั้งหมดพร้อมตำแหน่ง
$stmt = $conn->query("
    SELECT 
        p.*,
        pos.position_name,
        pos.position_code,
        u.image_path
    FROM personnel p
    LEFT JOIN positions pos ON p.position_id = pos.position_id
    LEFT JOIN users u ON p.user_id = u.user_id
    WHERE u.role != 'admin'
    ORDER BY pos.position_name, p.first_name, p.last_name
");
$personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

// จัดกลุ่มบุคลากรตามตำแหน่ง
$personnelByPosition = [];
foreach ($personnel as $person) {
    $position = $person['position_name'] ?? 'ไม่ระบุตำแหน่ง';
    if (!isset($personnelByPosition[$position])) {
        $personnelByPosition[$position] = [];
    }
    $personnelByPosition[$position][] = $person;
}

require_once 'header.php';
?>

<div class="reports-container">
    <div class="container">
        <h2 class="page-title">
            <i class="bi bi-file-earmark-text me-2"></i>รายงานข้อมูลบุคลากร
        </h2>

        <!-- Overview Statistics -->
        <div class="stats-grid">
            <div class="stat-card total-users">
                <i class="bi bi-people-fill stat-icon"></i>
                <div>
                    <div class="stat-title">จำนวนบุคลากรทั้งหมด</div>
                    <div class="stat-value"><?= $stats['total_personnel'] ?></div>
                </div>
            </div>
            <div class="stat-card total-users">
                <i class="bi bi-briefcase-fill stat-icon"></i>
                <div>
                    <div class="stat-title">จำนวนตำแหน่งทั้งหมด</div>
                    <div class="stat-value"><?= $stats['total_positions'] ?></div>
                </div>
            </div>
        </div>

        <!-- Position Statistics -->
        <div class="position-stats-card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart-fill me-2"></i>สัดส่วนบุคลากรตามตำแหน่ง</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($positionStats as $stat): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="position-stat-item">
                                <div class="position-name">
                                    <?= htmlspecialchars($stat['position_name']) ?>
                                    <small class="position-code">(<?= htmlspecialchars($stat['position_code']) ?>)</small>
                                </div>
                                <div class="position-count"><?= $stat['count'] ?> คน</div>
                                <div class="position-percentage">
                                    <?php 
                                    $percentage = ($stats['total_personnel'] > 0) 
                                        ? round(($stat['count'] / $stats['total_personnel']) * 100, 1)
                                        : 0;
                                    echo $percentage . '%';
                                    ?>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Personnel List by Position -->
        <div class="personnel-card">
            <div class="personnel-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>รายชื่อบุคลากรแยกตามตำแหน่ง
                </h5>
                <button class="print-btn" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
                </button>
            </div>
            <div class="p-4">
                <?php foreach ($personnelByPosition as $position => $positionPersonnel): ?>
                    <div class="position-section mb-4">
                        <h6 class="position-title mb-3">
                            <i class="bi bi-bookmark-fill me-2"></i><?= htmlspecialchars($position) ?>
                            <span class="position-count">(<?= count($positionPersonnel) ?> คน)</span>
                        </h6>
                        <div class="table-responsive">
                            <table class="personnel-table">
                                <thead>
                                    <tr>
                                        <th width="50">รูป</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>อีเมล</th>
                                        <th>เบอร์โทร</th>
                                        <th>ที่อยู่</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($positionPersonnel as $person): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= $person['image_path'] ?? 'images/default-profile.png' ?>"
                                                     class="profile-image" alt="Profile Image">
                                            </td>
                                            <td>
                                                <div class="fw-500">
                                                    <?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="bi bi-envelope me-2"></i>
                                                <?= htmlspecialchars($person['email'] ?: '-') ?>
                                            </td>
                                            <td>
                                                <i class="bi bi-telephone me-2"></i>
                                                <?= htmlspecialchars($person['phone'] ?: '-') ?>
                                            </td>
                                            <td>
                                                <i class="bi bi-geo-alt me-2"></i>
                                                <?= htmlspecialchars($person['address'] ?: '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* สไตล์ที่เพิ่มเติม */
.position-stats-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.position-stats-card .card-header {
    padding: 1.5rem;
    border-bottom: 2px solid #f0f0f0;
}

.position-stat-item {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 15px;
    height: 100%;
}

.position-name {
    font-weight: 600;
    color: #2c5364;
    margin-bottom: 0.5rem;
}

.position-code {
    color: #6c757d;
}

.position-count {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e3c72;
    margin-bottom: 0.25rem;
}

.position-percentage {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.progress {
    height: 6px;
    border-radius: 3px;
    background-color: #e9ecef;
}

.progress-bar {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    border-radius: 3px;
}

.position-section {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.position-title {
    color: #2c5364;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.position-title .position-count {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: normal;
}

@media print {
    .position-stats-card {
        break-inside: avoid;
    }
    
    .position-section {
        break-inside: avoid;
    }
}
</style>

<?php require_once 'footer.php'; ?>

<style>
    .reports-container {
        padding: 2rem 0;
        min-height: calc(100vh - 180px);
        background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
    }

    .page-title {
        color: #2c5364;
        font-weight: 600;
        margin-bottom: 2rem;
        padding: 1rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        border-radius: 20px;
        padding: 1.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card.total-users {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
    }

    .stat-card.total-teachers {
        background: linear-gradient(135deg, #2193b0, #6dd5ed);
    }

    .stat-card.total-staff {
        background: linear-gradient(135deg, #11998e, #38ef7d);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .stat-title {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 600;
    }

    /* Personnel List */
    .personnel-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: none;
    }

    .personnel-header {
        background: linear-gradient(135deg, #2c5364, #203a43);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .print-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .print-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .personnel-table {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
        width: 100%;
    }

    .personnel-table th {
        background: transparent;
        color: #2c5364;
        font-weight: 600;
        padding: 1rem;
        border: none;
    }

    .personnel-table tr {
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }

    .personnel-table tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .personnel-table td {
        padding: 1rem;
        border: none;
        vertical-align: middle;
    }

    .profile-image {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .profile-image:hover {
        transform: scale(1.1);
    }

    .personnel-role {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .role-admin {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .role-teacher {
        background: rgba(32, 201, 151, 0.1);
        color: #20c997;
    }

    .role-staff {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    @media print {
        .reports-container {
            background: white;
            padding: 0;
        }

        .personnel-header {
            background: none !important;
            color: black;
        }

        .print-btn {
            display: none;
        }

        .personnel-table tr {
            box-shadow: none;
        }

        .personnel-role {
            border: 1px solid currentColor;
        }
    }
</style>