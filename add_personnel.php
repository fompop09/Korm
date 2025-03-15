<?php
// add_personnel.php
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
        $conn->beginTransaction();

        // สร้างผู้ใช้ใหม่
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        $userId = $conn->lastInsertId();

        // อัพโหลดรูปภาพ
        $imagePath = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newname = uniqid() . "." . $ext;
                $destination = "uploads/" . $newname;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $imagePath = $destination;
                }
            }
        }

        if ($imagePath) {
            $stmt = $conn->prepare("UPDATE users SET image_path = ? WHERE user_id = ?");
            $stmt->execute([$imagePath, $userId]);
        }

        // เพิ่มข้อมูลบุคลากร
        $stmt = $conn->prepare("
            INSERT INTO personnel (
                user_id, first_name, last_name, 
                position_id, email, phone, address
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['position_id'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address']
        ]);

        $conn->commit();
        $message = '<div class="alert alert-success">เพิ่มข้อมูลบุคลากรสำเร็จ</div>';
    } catch (Exception $e) {
        $conn->rollBack();
        $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
    }
}

require_once 'header.php';
?>

<style>
.add-personnel-container {
    padding: 2rem 0;
    background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
    min-height: calc(100vh - 180px);
}

.form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    padding: 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    color: #2c5364;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.image-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    margin: 1rem auto;
    border: 3px solid #fff;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.form-control {
    border: 2px solid #e1e1e1;
    border-radius: 12px;
    padding: 0.8rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #2c5364;
    box-shadow: 0 0 0 0.2rem rgba(44, 83, 100, 0.1);
}

.submit-btn {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<div class="add-personnel-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                    <h2 class="text-center mb-4">เพิ่มข้อมูลบุคลากร</h2>
                    
                    <?= $message ?>

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <!-- ข้อมูลผู้ใช้ -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="bi bi-person-circle"></i> ข้อมูลผู้ใช้ระบบ
                            </h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ชื่อผู้ใช้</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">รหัสผ่าน</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ตำแหน่ง</label>
                                <select name="role" class="form-select" required>
                                    <option value="teacher">ครู</option>
                                    <option value="staff">เจ้าหน้าที่</option>
                                </select>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนตัว -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="bi bi-person-vcard"></i> ข้อมูลส่วนตัว
                            </h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ชื่อ</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">นามสกุล</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ตำแหน่งงาน</label>
                                <select name="position_id" class="form-select" required>
                                    <option value="">เลือกตำแหน่งงาน</option>
                                    <?php 
                                    // ดึงข้อมูลตำแหน่งทั้งหมด
                                    $stmt = $conn->query("SELECT * FROM positions ORDER BY position_name");
                                    while ($position = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . $position['position_id'] . '">' 
                                            . htmlspecialchars($position['position_name']) 
                                            . ' (' . htmlspecialchars($position['position_code']) . ')'
                                            . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- ข้อมูลการติดต่อ -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="bi bi-envelope"></i> ข้อมูลการติดต่อ
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- รูปโปรไฟล์ -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="bi bi-image"></i> รูปโปรไฟล์
                            </h5>
                            <div class="text-center">
                                <div class="image-preview">
                                    <img src="images/default-profile.png" id="preview">
                                </div>
                                <input type="file" name="profile_image" class="form-control mt-3" 
                                       accept="image/*" onchange="previewImage(event)">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary submit-btn w-100">
                            <i class="bi bi-plus-circle me-2"></i>เพิ่มข้อมูลบุคลากร
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// แสดงตัวอย่างรูปภาพ
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('preview');
        preview.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}

// ตรวจสอบฟอร์ม
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php require_once 'footer.php'; ?>