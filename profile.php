<?php
// profile.php
session_start();
require_once 'config.php';
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getConnection();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . "." . $ext;
            $destination = "uploads/" . $newname;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $stmt = $conn->prepare("UPDATE users SET image_path = ? WHERE user_id = ?");
                $stmt->execute([$destination, $_SESSION['user_id']]);
            }
        }
    }
    
    // Update personnel info
    $stmt = $conn->prepare("
        INSERT INTO personnel (user_id, first_name, last_name, email, phone, address)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        first_name = VALUES(first_name),
        last_name = VALUES(last_name),
        email = VALUES(email),
        phone = VALUES(phone),
        address = VALUES(address)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $first_name,
        $last_name,
        $email,
        $phone,
        $address
    ]);
    
    echo "<script>alert('บันทึกข้อมูลสำเร็จ');</script>";
}

// Get current profile
$stmt = $conn->prepare("
    SELECT p.*, u.image_path, u.role
    FROM users u
    LEFT JOIN personnel p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<style>
.profile-container {
    padding: 2rem 0;
    min-height: calc(100vh - 180px);
    background: linear-gradient(135deg, rgba(15, 32, 39, 0.05), rgba(32, 58, 67, 0.05), rgba(44, 83, 100, 0.05));
}

/* Profile Card */
.profile-card {
    background: white;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.profile-header {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(rgba(255, 255, 255, 0.1) 0%, transparent 60%);
    transform: rotate(45deg);
}

.profile-image-container {
    position: relative;
    display: inline-block;
    margin-bottom: 1.5rem;
}

.profile-image {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    border: 5px solid white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    object-fit: cover;
    transition: all 0.3s ease;
}

.profile-image:hover {
    transform: scale(1.05);
}

.profile-name {
    color: white;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.profile-role {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Edit Form Card */
.edit-card {
    background: white;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.edit-header {
    background: transparent;
    border-bottom: 2px solid #f0f0f0;
    padding: 1.5rem;
    font-weight: 600;
    color: #2c5364;
}

.form-container {
    padding: 2rem;
}

.form-label {
    color: #2c5364;
    font-weight: 500;
    margin-bottom: 0.5rem;
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

.file-upload {
    background: #f8f9fa;
    border: 2px dashed #e1e1e1;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload:hover {
    border-color: #2c5364;
    background: #fff;
}

.submit-btn {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    background: linear-gradient(135deg, #2a5298, #1e3c72);
}
</style>

<div class="profile-container">
    <div class="container">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-md-4 mb-4">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <img src="<?= $profile['image_path'] ?? 'images/default-profile.png' ?>" 
                                 class="profile-image" 
                                 alt="Profile Image">
                        </div>
                        <h5 class="profile-name">
                            <?= htmlspecialchars($profile['first_name'] ?? '') ?> 
                            <?= htmlspecialchars($profile['last_name'] ?? '') ?>
                        </h5>
                        <div class="profile-role">
                            <i class="bi bi-person-badge me-2"></i>
                            <?= htmlspecialchars($profile['role']) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Form -->
            <div class="col-md-8 mb-4">
                <div class="edit-card">
                    <div class="edit-header">
                        <i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลส่วนตัว
                    </div>
                    <div class="form-container">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label">รูปโปรไฟล์</label>
                                <div class="file-upload">
                                    <i class="bi bi-cloud-upload me-2"></i>
                                    <input type="file" name="profile_image" class="form-control" accept="image/*">
                                    <small class="text-muted d-block mt-2">อัพโหลดรูปภาพขนาดไม่เกิน 2MB</small>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col">
                                    <label class="form-label">ชื่อ</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="first_name" class="form-control" 
                                               value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">นามสกุล</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="last_name" class="form-control" 
                                               value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">อีเมล</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">ที่อยู่</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <textarea name="address" class="form-control" 
                                              rows="3"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary submit-btn">
                                <i class="bi bi-check2-circle me-2"></i>บันทึกข้อมูล
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>