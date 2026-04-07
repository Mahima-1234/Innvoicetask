<?php
$host = "localhost";
$username = "root";
$password = ""; 
$dbname = "invoice_db";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $username, $password);

$db_connected = false;
$db_error = "";

if (!$conn->connect_error) {
    $db_connected = true;
    
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);

    $conn->query("CREATE TABLE IF NOT EXISTS Customer (
        mobile_number VARCHAR(15) PRIMARY KEY,
        name VARCHAR(100),
        address TEXT,
        gst_number VARCHAR(15)
    )");

    $conn->query("INSERT IGNORE INTO Customer (mobile_number, name, address, gst_number) 
                  VALUES ('7499934522', 'Vishal Bharti', 'Tech Park, Pune, Maharastra', '24ABCDE1234F1B5')");

    $conn->query("CREATE TABLE IF NOT EXISTS Invoice (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(50),
        product VARCHAR(255),
        rate DECIMAL(10,2),
        quantity INT,
        total DECIMAL(10,2),
        gst_slab VARCHAR(10),
        total_price DECIMAL(10,2)
    )");
} else {
    $db_error = "Database Connection Failed. Please update credentials in db.php.";
}
?>
