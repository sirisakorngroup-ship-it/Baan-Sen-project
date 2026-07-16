<?php
// form.php

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$host = 'localhost';
$db   = 'Baan sen family';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่า user_id ที่ได้จาก LIFF ผ่าน hidden field
    $user_id = $_POST['User_id'] ?? 'user123';
    $fullname = $_POST['Fullname'];
    $Birthday = $_POST['Birthday'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if ($fullname && $Birthday && $phone && $address) {
        $stmt = $pdo->prepare("INSERT INTO `สมัครสมาชิก`(User_id,Fullname,Birthday,phone,address)VALUES (?,?,?,?,?)");
        $stmt->execute([$User_id, $Fullname, $Birthday, $phone, $address]);
        $message = "บันทึกข้อมูลเรียบร้อยแล้ว!";
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 50px;
        }
        .card-header {
            font-weight: bold;
            font-size: 1.25rem;
        }
    </style>
    <!-- LIFF SDK -->
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script>
      // เมื่อหน้าโหลดเสร็จ ให้ initialize LIFF  
      window.onload = function() {
          liff.init({ liffId: '#########################' })
          .then(() => {
              // ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบแล้วหรือยัง
              if (!liff.isLoggedIn()) {
                  // ถ้ายังไม่ได้เข้าสู่ระบบให้เรียก liff.login() เพื่อให้ผู้ใช้ล็อกอิน
                  liff.login();
              } else {
                  // เมื่อเข้าสู่ระบบแล้วให้ดึงข้อมูลโปรไฟล์
                  liff.getProfile()
                  .then(profile => {
                      // นำค่า userId ไปใส่ใน hidden field
                      document.getElementById('user_id').value = profile.userId;
                  })
                  .catch(err => {
                      console.error('Error getting profile:', err);
                      // กรณีเกิดข้อผิดพลาด ให้ใช้ค่า default แทน
                      document.getElementById('user_id').value = "user123";
                  });
              }
          })
          .catch(err => {
              console.error('LIFF initialization failed:', err);
          });
      };
    </script>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    สมัครสมาชิก
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="User_id" id="user_id">
                    <div class="form-group">
                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" class="form-control" name="Fullname" required>
                </div>
                <div class="form-group">
                    <label>วันเกิด</label>
                    <input type="date" class="form-control" name="Birthday" required>
                </div>
                <div class="form-group">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" name="phone" required>
                </div>
                <div class="form-group">
                    <label>ที่อยู่</label>
                    <textarea class="form-control" name="address" rows="3" required></textarea>
                </div>
                        <button type="submit" class="btn btn-primary btn-block">สมัครสมาชิก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS และ dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
