<?php
// webhook.php

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
    error_log("Database connection failed: " . $e->getMessage());
    exit;
}

// กำหนด access token ของ Channel ที่ได้จาก LINE Developers Console
$accessToken = '##############################';

// อ่านข้อมูล request จาก LINE
$request = file_get_contents('php://input');
$requestJson = json_decode($request, true);

// สำหรับการ debug log (สามารถลบออกเมื่อใช้งานจริง)
error_log(print_r($requestJson, true));

// ตรวจสอบว่ามี events หรือไม่
if (isset($requestJson['events']) && is_array($requestJson['events'])) {
    foreach ($requestJson['events'] as $event) {
        // ตรวจสอบว่ามี replyToken และข้อความหรือไม่
        if (isset($event['replyToken']) && isset($event['message']['text'])) {
            $replyToken = $event['replyToken'];
            $text = trim($event['message']['text']);

            // ตรวจสอบข้อความ "ตรวจสอบประกัน" โดยใช้ mb_strtolower รองรับภาษาไทย
            if (mb_strtolower($text) === mb_strtolower("ตรวจสอบประกัน")) {
                // ดึง userId จาก event (หรือใช้ default)
                $userId = $event['source']['userId'] ?? 'user123';
                
                // ดึงข้อมูลสินค้าของผู้ใช้จากฐานข้อมูล
                $stmt = $pdo->prepare("SELECT product_name, purchase_date FROM products WHERE user_id = ?");
                $stmt->execute([$userId]);
                $products = $stmt->fetchAll();
                
                if (empty($products)) {
                    // หากไม่พบข้อมูลสินค้า ให้เตรียมข้อความแจ้งกลับ
                    $responseMessage = [
                        "type" => "text",
                        "text" => "ไม่พบข้อมูลสินค้าของคุณ"
                    ];
                } else {
                    // สร้าง Flex Message (หรือ Carousel หากมีหลายชิ้น)
                    $bubbles = [];
                    $currentDate = new DateTime();
                    
                    foreach ($products as $product) {
                        $productName = $product['product_name'];
                        $purchaseDateStr = $product['purchase_date'];
                        $purchaseDate = new DateTime($purchaseDateStr);
                        
                        // กำหนดระยะเวลารับประกันตามประเภทสินค้า
                        $warrantyYears = 1; // ค่า default
                        $lowerName = mb_strtolower($productName);
                        if (strpos($lowerName, 'notebook') !== false) {
                            $warrantyYears = 3;
                        } elseif (strpos($lowerName, 'keyboard') !== false) {
                            $warrantyYears = 2;
                        } elseif (strpos($lowerName, 'monitor') !== false) {
                            $warrantyYears = 1;
                        }
                        
                        // คำนวณวันหมดประกัน
                        $expirationDate = clone $purchaseDate;
                        $expirationDate->modify("+{$warrantyYears} years");
                        
                        // คำนวณระยะเวลาที่เหลืออยู่
                        if ($expirationDate >= $currentDate) {
                            $interval = $currentDate->diff($expirationDate);
                            $remaining = $interval->y . " ปี " . $interval->m . " เดือน " . $interval->d . " วัน";
                            $warrantyStatusText = "อยู่ในประกัน: " . $remaining;
                            $warrantyColor = "#008000"; // สีเขียว
                        } else {
                            $warrantyStatusText = "หมดประกัน";
                            $warrantyColor = "#FF0000"; // สีแดง
                        }
                        
                        // สร้าง bubble สำหรับแต่ละสินค้า
                        $bubble = [
                            "type" => "bubble",
                            "header" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => ucfirst($productName),
                                        "weight" => "bold",
                                        "size" => "xl"
                                    ]
                                ]
                            ],
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "วันที่ซื้อ: " . $purchaseDate->format('Y-m-d')
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "หมดประกัน: " . $expirationDate->format('Y-m-d')
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $warrantyStatusText,
                                        "color" => $warrantyColor,
                                        "weight" => "bold"
                                    ]
                                ]
                            ]
                        ];
                        $bubbles[] = $bubble;
                    }
                    
                    // หากมีสินค้าหลายชิ้น ให้ใช้ carousel
                    if (count($bubbles) > 1) {
                        $flexMessageContent = [
                            "type" => "carousel",
                            "contents" => $bubbles
                        ];
                    } else {
                        $flexMessageContent = $bubbles[0];
                    }
                    
                    $responseMessage = [
                        "type" => "flex",
                        "altText" => "ข้อมูลประกันสินค้า",
                        "contents" => $flexMessageContent
                    ];
                }
                
                // เตรียมข้อมูลสำหรับ reply message
                $response = [
                    "replyToken" => $replyToken,
                    "messages" => [$responseMessage]
                ];
                
                // ส่ง reply message ไปยัง LINE Messaging API ด้วย cURL
                $url = 'https://api.line.me/v2/bot/message/reply';
                $headers = [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $accessToken,
                ];
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                if(curl_errno($ch)){
                    error_log('Curl error: ' . curl_error($ch));
                }
                curl_close($ch);
                
                exit; // เมื่อส่ง reply แล้ว หยุดการทำงาน
            }
        }
    }
}
?>
