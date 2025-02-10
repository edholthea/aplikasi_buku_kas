<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Access denied";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all transactions
$sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$output = "No\tTanggal\tDeskripsi\tDebit\tKredit\tSaldo\n";
$no = 1;
$balance = 0;

while ($row = $result->fetch_assoc()) {
    $balance += $row['debit'] - $row['credit'];
    $output .= $no++ . "\t" . date('d-m-Y', strtotime($row['date'])) . "\t" . $row['description'] . "\t" . 
               number_format($row['debit'], 0, ',', '.') . "\t" . number_format($row['credit'], 0, ',', '.') . "\t" . 
               number_format($balance, 0, ',', '.') . "\n";
}

// Return data as a plain text response
header('Content-Type: text/plain');
echo $output;
?>
