<?php
session_start();

// Xóa tất cả các biến phiên (session)
$_SESSION = array();

// Hủy bỏ phiên làm việc
session_destroy();

// Trả về phản hồi JSON để JavaScript xử lý
echo json_encode(['success' => true]);
exit;
?>
