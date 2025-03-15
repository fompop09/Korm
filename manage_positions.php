<?php
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
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $conn->prepare("INSERT INTO positions (position_name, position_code) VALUES (?, ?)");
                    $stmt->execute([$_POST['position_name'], $_POST['position_code']]);
                    $message = '<div class="alert alert-success">เพิ่มตำแหน่งสำเร็จ</div>';
                    break;

                case 'edit':
                    $stmt = $conn->prepare("UPDATE positions SET position_name = ?, position_code = ? WHERE position_id = ?");
                    $stmt->execute([$_POST['position_name'], $_POST['position_code'], $_POST['position_id']]);
                    $message = '<div class="alert alert-success">แก้ไขตำแหน่งสำเร็จ</div>';
                    break;

                case 'delete':
                    // ตรวจสอบว่ามีการใช้งานตำแหน่งนี้อยู่หรือไม่
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM personnel WHERE position_id = ?");
                    $stmt->execute([$_POST['position_id']]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("ไม่สามารถลบตำแหน่งนี้ได้เนื่องจากมีการใช้งานอยู่");
                    }
                    
                    $stmt = $conn->prepare("DELETE FROM positions WHERE position_id = ?");
                    $stmt->execute([$_POST['position_id']]);
                    $message = '<div class="alert alert-success">ลบตำแหน่งสำเร็จ</div>';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
    }
}

// ดึงข้อมูลตำแหน่งทั้งหมด
$positions = $conn->query("SELECT * FROM positions ORDER BY position_name")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<style>
.positions-container {
    padding: 2rem 0;
    background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
    min-height: calc(100vh - 180px);
}

.positions-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    padding: 2rem;
}

.table-custom th {
    background: #f8f9fa;
    color: #2c5364;
}

.position-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-edit, .btn-delete {
    padding: 0.5rem 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.btn-edit:hover, .btn-delete:hover {
    transform: translateY(-2px);
}

.modal-content {
    border-radius: 20px;
    border: none;
}

.modal-header {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: white;
    border-radius: 20px 20px 0 0;
}

.modal-body {
    padding: 2rem;
}
</style>

<div class="positions-container">
    <div class="container">
        <div class="positions-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">จัดการตำแหน่ง</h2>
                <button class="btn btn-primary" onclick="showAddModal()">
                    <i class="bi bi-plus-circle me-2"></i>เพิ่มตำแหน่ง
                </button>
            </div>

            <?= $message ?>

            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>รหัสตำแหน่ง</th>
                            <th>ชื่อตำแหน่ง</th>
                            <th>วันที่สร้าง</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positions as $position): ?>
                        <tr>
                            <td><?= htmlspecialchars($position['position_code']) ?></td>
                            <td><?= htmlspecialchars($position['position_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($position['created_at'])) ?></td>
                            <td>
                                <div class="position-actions">
                                    <button class="btn btn-warning btn-edit" onclick="showEditModal(<?= htmlspecialchars(json_encode($position)) ?>)">
                                        <i class="bi bi-pencil"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-danger btn-delete" onclick="confirmDelete(<?= $position['position_id'] ?>)">
                                        <i class="bi bi-trash"></i> ลบ
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับเพิ่ม/แก้ไขตำแหน่ง -->
<div class="modal fade" id="positionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">เพิ่มตำแหน่ง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="positionForm" method="POST">
                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="position_id" id="position_id">
                    
                    <div class="mb-3">
                        <label class="form-label">รหัสตำแหน่ง</label>
                        <input type="text" name="position_code" id="position_code" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">ชื่อตำแหน่ง</label>
                        <input type="text" name="position_name" id="position_name" class="form-control" required>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let positionModal;

document.addEventListener('DOMContentLoaded', function() {
    positionModal = new bootstrap.Modal(document.getElementById('positionModal'));
});

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มตำแหน่ง';
    document.getElementById('formAction').value = 'add';
    document.getElementById('positionForm').reset();
    positionModal.show();
}

function showEditModal(position) {
    document.getElementById('modalTitle').textContent = 'แก้ไขตำแหน่ง';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('position_id').value = position.position_id;
    document.getElementById('position_code').value = position.position_code;
    document.getElementById('position_name').value = position.position_name;
    positionModal.show();
}

function confirmDelete(positionId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบตำแหน่งนี้?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="position_id" value="${positionId}">
        `;
        document.body.append(form);
        form.submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>