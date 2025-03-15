<?php
// manage_users.php
session_start();
require_once 'config.php';
require_once 'connection.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$conn = getConnection();

// Handle form submissions via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($_POST['action']) {
            case 'add':
                $username = $_POST['username'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $password, $role])) {
                    $response = ['success' => true, 'message' => 'เพิ่มผู้ใช้สำเร็จ'];
                }
                break;
                
            case 'edit':
                $user_id = $_POST['user_id'];
                $username = $_POST['username'];
                $role = $_POST['role'];
                
                $sql = "UPDATE users SET username = ?, role = ?";
                $params = [$username, $role];
                
                // Only update password if provided
                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE user_id = ?";
                $params[] = $user_id;
                
                $stmt = $conn->prepare($sql);
                if ($stmt->execute($params)) {
                    $response = ['success' => true, 'message' => 'แก้ไขข้อมูลสำเร็จ'];
                }
                break;
                
            case 'delete':
                $user_id = $_POST['user_id'];
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                if ($stmt->execute([$user_id])) {
                    $response = ['success' => true, 'message' => 'ลบผู้ใช้สำเร็จ'];
                }
                break;
                
            case 'get':
                $user_id = $_POST['user_id'];
                $stmt = $conn->prepare("SELECT user_id, username, role FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $response = ['success' => true, 'data' => $user];
                }
                break;
        }
    } catch (PDOException $e) {
        $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get all users
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<style>
.user-management-container {
    padding: 2rem 0;
    min-height: calc(100vh - 180px);
    background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
}

.page-header {
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.page-title {
    font-weight: 600;
    color: #2c5364;
    margin: 0;
}

.add-user-btn {
    background: linear-gradient(135deg, #2c5364, #203a43);
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.add-user-btn:hover {
    background: linear-gradient(135deg, #203a43, #2c5364);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.users-table-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-container {
    padding: 1rem;
}

.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.5rem;
}

.custom-table thead th {
    background: transparent;
    border: none;
    color: #2c5364;
    font-weight: 600;
    padding: 1rem;
}

.custom-table tbody tr {
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    transition: all 0.3s ease;
}

.custom-table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.custom-table td {
    padding: 1rem;
    border: none;
    vertical-align: middle;
}

.role-badge {
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

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-edit {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.btn-edit:hover {
    background: #ffc107;
    color: white;
}

.btn-delete {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.btn-delete:hover {
    background: #dc3545;
    color: white;
}
</style>

<div class="user-management-container">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <h2 class="page-title">
                <i class="bi bi-people-fill me-2"></i>จัดการผู้ใช้งาน
            </h2>
            <button class="btn add-user-btn" onclick="showAddModal()">
                <i class="bi bi-plus-circle me-2"></i>เพิ่มผู้ใช้ใหม่
            </button>
        </div>
        
        <!-- Users Table -->
        <div class="users-table-card">
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>ตำแหน่ง</th>
                            <th>วันที่สร้าง</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2 fs-5"></i>
                                    <?= htmlspecialchars($user['username']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?= strtolower($user['role']) ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <i class="bi bi-calendar3 me-2"></i>
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick="showEditModal(<?= $user['user_id'] ?>)">
                                        <i class="bi bi-pencil-square me-1"></i>แก้ไข
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteUser(<?= $user['user_id'] ?>)">
                                        <i class="bi bi-trash me-1"></i>ลบ
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

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">เพิ่มผู้ใช้ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="user_id" name="user_id">
                    <input type="hidden" id="action" name="action">
                    
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่าน</label>
                        <input type="password" id="password" name="password" class="form-control">
                        <div id="passwordHelp" class="form-text"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">ตำแหน่ง</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="admin">ผู้ดูแลระบบ</option>
                            <option value="teacher">ครู</option>
                            <option value="staff">เจ้าหน้าที่</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
let modal;
document.addEventListener('DOMContentLoaded', function() {
    modal = new bootstrap.Modal(document.getElementById('userModal'));
});

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มผู้ใช้ใหม่';
    document.getElementById('userForm').reset();
    document.getElementById('action').value = 'add';
    document.getElementById('password').required = true;
    document.getElementById('passwordHelp').textContent = '';
    modal.show();
}

function showEditModal(userId) {
    document.getElementById('modalTitle').textContent = 'แก้ไขข้อมูลผู้ใช้';
    document.getElementById('userForm').reset();
    document.getElementById('action').value = 'edit';
    document.getElementById('user_id').value = userId;
    document.getElementById('password').required = false;
    document.getElementById('passwordHelp').textContent = 'เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน';
    
    // Fetch user data
    fetch('manage_users.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'get',
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('username').value = data.data.username;
            document.getElementById('role').value = data.data.role;
            modal.show();
        }
    });
}

function saveUser() {
    const form = document.getElementById('userForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    fetch('manage_users.php', {
        method: 'POST',
        body: new URLSearchParams(new FormData(form))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            modal.hide();
            location.reload();
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    });
}

function deleteUser(userId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
        fetch('manage_users.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'delete',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'เกิดข้อผิดพลาด');
            }
        });
    }
}
</script>

<?php require_once 'footer.php'; ?>