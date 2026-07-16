<?php
// form.php

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$host = 'localhost';
$db   = '##############';  // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ
$user = '##############';  // เปลี่ยนเป็น username ของคุณ
$pass = '################';    // เปลี่ยนเป็น password ของคุณ
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
    $user_id = $_POST['user_id'] ?? 'user123';
    $product_name = $_POST['product_name'] ?? '';
    $purchase_date = $_POST['purchase_date'] ?? '';

    if ($product_name && $purchase_date) {
        $stmt = $pdo->prepare("INSERT INTO products (user_id, product_name, purchase_date) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_name, $purchase_date]);
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
    <title>บันทึกข้อมูลสินค้า IT</title>
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
                    บันทึกข้อมูลสินค้า IT
                </div>
                <div class="card-body">
                    <?php if($message): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <!-- รับค่า user_id จาก LIFF -->
                        <input type="hidden" name="user_id" id="user_id" value="">
                        <div class="form-group">
                            <label for="product_name">ชื่อสินค้า:</label>
                            <select class="form-control" id="product_name" name="product_name" required>
                                <option value="">-- เลือกสินค้า --</option>
                                <option value="notebook">Notebook</option>
                                <option value="keyboard">Keyboard</option>
                                <option value="monitor">Monitor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchase_date">วันที่ซื้อ:</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">บันทึกข้อมูล</button>
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
