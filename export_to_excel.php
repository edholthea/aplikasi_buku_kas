<?php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Prepare SQL query to fetch all transactions for the logged-in user
$sql = "SELECT * FROM transactions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for Excel file download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan Buku Kas ' . date('d-m-Y') . '.xls"');

// Output the data to Excel
echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Deskripsi</th>
        <th>Debit</th>
        <th>Kredit</th>
        <th>Saldo</th>
      </tr>";

$no = 1;
$current_balance = 0; // Initialize balance
$total_debit = 0; // Initialize total debit
$total_credit = 0; // Initialize total credit

while ($row = $result->fetch_assoc()) {
    // Calculate current balance
    $current_balance += $row['debit'] - $row['credit'];

    // Update total debit and credit
    $total_debit += $row['debit'];
    $total_credit += $row['credit'];

    echo "<tr>
            <td>{$no}</td>
            <td>" . date('d-m-Y', strtotime($row['date'])) . "</td>
            <td>" . htmlspecialchars($row['description']) . "</td>
            <td>" . number_format($row['debit'], 0, ',', '.') . "</td>
            <td>" . number_format($row['credit'], 0, ',', '.') . "</td>
            <td>" . number_format($current_balance, 0, ',', '.') . "</td>
          </tr>";
    $no++;
}

// Output the totals
echo "<tr>
        <td colspan='3' style='text-align: right;'><strong>Total</strong></td>
        <td><strong>" . number_format($total_debit, 0, ',', '.') . "</strong></td>
        <td><strong>" . number_format($total_credit, 0, ',', '.') . "</strong></td>
        <td><strong>" . number_format($current_balance, 0, ',', '.') . "</strong></td>
      </tr>";

echo "</table>";
?>
