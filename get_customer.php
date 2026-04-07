<?php
require_once 'db.php';
if (isset($_GET['mobile']) && $db_connected) {
    header('Content-Type: application/json');
    $mobile = $conn->real_escape_string($_GET['mobile']);
    $result = $conn->query("SELECT * FROM Customer WHERE mobile_number = '$mobile'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['found' => true, 'data' => $row]);
    } else {
        echo json_encode(['found' => false]);
    }
} else {
    echo json_encode(['found' => false, 'error' => 'Database not connected or invalid request']);
}
?>