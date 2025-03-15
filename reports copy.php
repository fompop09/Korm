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

// Get statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_teachers' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
    'total_staff' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn()
];

// Get all personnel with their roles
$stmt = $conn->query("
    SELECT p.*, u.role, u.image_path
    FROM personnel p
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.last_name, p.first_name
");
$personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="reports-container">
    <div class="container">
        <h2 class="page-title">
            <i class="bi bi-file-earmark-text me-2"></i>รายงานข้อมูลบุคลากร
        </h2>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total-users">
                <i class="bi bi-people-fill stat-icon"></i>
                <div>
                    <div class="stat-title">จำนวนผู้ใช้ทั้งหมด</div>
                    <div class="stat-value"><?= $stats['total_users'] ?></div>
                </div>
            </div>
            <div class="stat-card total-teachers">
                <i class="bi bi-person-workspace stat-icon"></i>
                <div>
                    <div class="stat-title">จำนวนครู</div>
                    <div class="stat-value"><?= $stats['total_teachers'] ?></div>
                </div>
            </div>
            <div class="stat-card total-staff">
                <i class="bi bi-person-badge stat-icon"></i>
                <div>
                    <div class="stat-title">จำนวนเจ้าหน้าที่</div>
                    <div class="stat-value"><?= $stats['total_staff'] ?></div>
                </div>
            </div>
        </div>

        <!-- Personnel List -->
        <div class="personnel-card">
            <div class="personnel-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>รายชื่อบุคลากรทั้งหมด
                </h5>
                <button class="print-btn" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
                </button>
            </div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="personnel-table">
                        <thead>
                            <tr>
                                <th>รูป</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ตำแหน่ง</th>
                                <th>อีเมล</th>
                                <th>เบอร์โทร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $person): ?>
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
                                        <span class="personnel-role role-<?= strtolower($person['role']) ?>">
                                            <?= htmlspecialchars($person['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($person['email']) ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($person['phone']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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