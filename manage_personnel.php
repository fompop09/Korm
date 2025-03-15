<?php
// manage_personnel.php
session_start();
require_once 'config.php';
require_once 'connection.php';

// ตรวจสอบสิทธิ์แอดมิน
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$message = '';

// จัดการการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM personnel WHERE personnel_id = ?");
                    $stmt->execute([$_POST['personnel_id']]);
                    $message = '<div class="alert alert-success">ลบข้อมูลสำเร็จ</div>';
                    break;
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
        }
    }
}
// ดึงข้อมูลบุคลากรทั้งหมด
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
    ORDER BY p.first_name, p.last_name
");
$personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<style>
    .personnel-container {
        padding: 2rem 0;
        background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
        min-height: calc(100vh - 180px);
    }

    .personnel-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .person-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .person-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .person-header {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .person-image {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .person-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .person-position {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
        background: rgba(44, 83, 100, 0.1);
        color: #2c5364;
    }

    .person-details {
        padding: 1rem;
    }

    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .detail-item i {
        color: #2c5364;
        width: 20px;
    }

    .person-actions {
        padding: 1rem;
        background: #f8f9fa;
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }

    .search-box {
        margin-bottom: 2rem;
    }

    .search-input {
        border-radius: 50px;
        padding: 0.75rem 1.5rem;
        border: 2px solid #e1e1e1;
        width: 100%;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: #2c5364;
        box-shadow: 0 0 0 0.2rem rgba(44, 83, 100, 0.1);
    }
</style>

<div class="personnel-container">
    <div class="container">
        <div class="personnel-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">จัดการข้อมูลบุคลากร</h2>
                <a href="add_personnel.php" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>เพิ่มบุคลากร
                </a>
            </div>

            <?= $message ?>

            <!-- Search Box -->
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="ค้นหาบุคลากร..."
                    onkeyup="searchPersonnel()">
            </div>

            <!-- Personnel List -->
            <div id="personnelList">
                <?php foreach ($personnel as $person): ?>
                    <div class="person-card">
                        <div class="person-header">
                            <img src="<?= $person['image_path'] ?? 'images/default-profile.png' ?>" class="person-image"
                                alt="Profile Image">
                            <div>
                                <h5 class="person-title">
                                    <?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?>
                                </h5>
                                <span class="person-position">
                                    <?= htmlspecialchars($person['position_name']) ?>
                                    (<?= htmlspecialchars($person['position_code']) ?>)
                                </span>
                            </div>
                        </div>

                        <div class="person-details">
                            <?php if ($person['email']): ?>
                                <div class="detail-item">
                                    <i class="bi bi-envelope"></i>
                                    <?= htmlspecialchars($person['email']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($person['phone']): ?>
                                <div class="detail-item">
                                    <i class="bi bi-telephone"></i>
                                    <?= htmlspecialchars($person['phone']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($person['address']): ?>
                                <div class="detail-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($person['address']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="person-actions">
                            <a href="edit_personnel.php?id=<?= $person['personnel_id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i>แก้ไข
                            </a>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $person['personnel_id'] ?>)">
                                <i class="bi bi-trash me-1"></i>ลบ
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(personnelId) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลบุคลากรนี้?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="personnel_id" value="${personnelId}">
        `;
            document.body.append(form);
            form.submit();
        }
    }

    function searchPersonnel() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const cards = document.getElementsByClassName('person-card');

        Array.from(cards).forEach(card => {
            const name = card.querySelector('.person-title').textContent;
            const position = card.querySelector('.person-position').textContent;
            const shouldShow = name.toLowerCase().includes(filter) ||
                position.toLowerCase().includes(filter);
            card.style.display = shouldShow ? '' : 'none';
        });
    }
</script>

<?php require_once 'footer.php'; ?>