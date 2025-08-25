<?php
session_start();

// Cấu hình tài khoản đăng nhập
$admin_username = 'cp123';
$admin_password = '123456'; // Bạn nên sử dụng mã hóa mật khẩu trong thực tế

// Đường dẫn tới file JSON
$filePath = 'data.json';

// Hàm trả về phản hồi JSON
function responseJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

// Đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['username']) && isset($input['password'])) {
        if ($input['username'] === $admin_username && $input['password'] === $admin_password) {
            $_SESSION['loggedin'] = true;
            responseJson(['success' => true, 'message' => 'Login success']);
        } else {
            responseJson(['success' => false, 'message' => 'Username or password is incorrect']);
        }
    } else {
        responseJson(['success' => false, 'message' => 'Thiếu thông tin đăng nhập']);
    }
    exit;
}

// Kiểm tra đăng nhập cho các hành động khác
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'checkLogin') {
        responseJson(['success' => false, 'message' => 'Please Login']);
    } else {
        // Trả về thông báo không có quyền truy cập
        responseJson(['success' => false, 'message' => 'Please Login']);
    }
    exit;
}

// Đọc dữ liệu từ file JSON
function readData() {
    global $filePath;
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        $data = json_decode($jsonData, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    return [];
}

// Ghi dữ liệu vào file JSON
function writeData($data) {
    global $filePath;
    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// Xử lý tìm kiếm Suburb theo từ khóa (search)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['query'])) {
    $query = strtolower(trim($_GET['query']));
    $data = readData();
    $results = [];

    foreach ($data as $key => $suburb) {
        if (strpos(strtolower($suburb['Suburb']), $query) === 0) {
            $suburb['index'] = $key; // Ghi lại chỉ số thực
            $results[] = $suburb;
        }
    }

    responseJson($results);
    exit;
}

// Cập nhật Suburb theo chỉ số
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    $input = json_decode(file_get_contents('php://input'), true);
    $data = readData();

    if ($index >= 0 && $index < count($data)) {
        // Kiểm tra dữ liệu bắt buộc
        if (!isset($input['Suburb']) || !isset($input['Run']) || !isset($input['Zone']) || !isset($input['Postcode'])) {
            responseJson(['success' => false, 'message' => 'Please enter data.']);
            exit;
        }

        // Cập nhật dữ liệu suburb
        $data[$index] = [
            'Suburb' => trim($input['Suburb']),
            'Run' => trim($input['Run']),
            'Zone' => trim($input['Zone']),
            'Postcode' => trim($input['Postcode'])
        ];

        if (writeData($data)) {
            responseJson(['success' => true, 'message' => 'Data Updated']);
        } else {
            responseJson(['success' => false, 'message' => 'Error']);
        }
    } else {
        responseJson(['success' => false, 'message' => 'Chỉ số không hợp lệ']);
    }
    exit;
}

// Mặc định trả về yêu cầu không hợp lệ
responseJson(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
?>
