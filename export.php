<?php
include 'db.php';
session_start();

// Cek sesi pengguna
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Debug: Cek apakah username ada dalam session
if (isset($_SESSION['username'])) {
    $username = htmlspecialchars($_SESSION['username']);
} else {
    $username = ".........................."; // Atau bisa juga kosong atau pesan kesalahan
}

// Include TCPDF
require_once('tcpdf/tcpdf.php'); // Sesuaikan dengan lokasi TCPDF Anda

// Ambil data transaksi dari database
$sql = "SELECT date, description, debit, credit, balance FROM transactions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Buat instance baru dari TCPDF dengan orientasi landscape
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Kebun Data');
$pdf->SetTitle('Laporan Buku Kas | sistem by www.kebundata.my.id');
$pdf->SetSubject('Laporan Buku Kas PDF');

// Tambahkan halaman
$pdf->AddPage();

// Set font untuk judul
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Buku Kas', 0, 1, 'C');

// Menambahkan keterangan
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 5, 'Dibuat oleh sistem kebun data | www.kebundata.my.id', 0, 1, 'C');
$pdf->Ln();

// Menambahkan username
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 5, 'Nama Usaha : ' . $username, 0, 1, 'C'); // Menampilkan username
$pdf->Line(10, 40, $pdf->getPageWidth() - 10, 40);
$pdf->Ln();

// Set font untuk tabel
$pdf->SetFont('helvetica', 'B', 12);

// Menghitung lebar kolom untuk memenuhi halaman
$header_widths = [10, 16, 80, 30, 30, 30]; // Lebar kolom dalam mm
$total_width = array_sum($header_widths); // Total lebar
$scale_factor = ($pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT) / $total_width; // Menghitung faktor skala

$pdf->Cell($header_widths[0] * $scale_factor, 10, 'No', 1, 0, 'C');
$pdf->Cell($header_widths[1] * $scale_factor, 10, 'Tanggal', 1, 0, 'C');
$pdf->Cell($header_widths[2] * $scale_factor, 10, 'Deskripsi', 1, 0, 'C');
$pdf->Cell($header_widths[3] * $scale_factor, 10, 'Debet', 1, 0, 'C');
$pdf->Cell($header_widths[4] * $scale_factor, 10, 'Kredit', 1, 0, 'C');
$pdf->Cell($header_widths[5] * $scale_factor, 10, 'Saldo', 1, 0, 'C');
$pdf->Ln();

// Data tabel
$pdf->SetFont('helvetica', '', 10);
$no = 1; // Inisialisasi nomor
$total_debit = 0;
$total_credit = 0;
$previous_balance = 0;

while ($row = $result->fetch_assoc()) {
    // Update total debit and credit
    $total_debit += $row['debit'];
    $total_credit += $row['credit'];

    // Calculate the balance based on the rule: debit = credit + saldo
    $balance = $previous_balance + $row['debit'] - $row['credit'];
    $previous_balance = $balance;

    $pdf->Cell($header_widths[0] * $scale_factor, 10, $no++, 1, 0, 'C'); // Tampilkan nomor

    // Format tanggal menjadi DD/MM/YYYY
    $formatted_date = date('d/m/Y', strtotime($row['date']));
    $pdf->Cell($header_widths[1] * $scale_factor, 10, $formatted_date, 1, 0, 'L');
    
    $pdf->Cell($header_widths[2] * $scale_factor, 10, htmlspecialchars($row['description']), 1, 0, 'L');
    
    // Format untuk debit
    $debit = 'Rp ' . number_format($row['debit'], 0, ',', '.');
    $pdf->Cell($header_widths[3] * $scale_factor, 10, $debit, 1, 0, 'L'); // Right align for monetary values
    
    // Format untuk kredit
    $credit = 'Rp ' . number_format($row['credit'], 0, ',', '.');
    $pdf->Cell($header_widths[4] * $scale_factor, 10, $credit, 1, 0, 'L'); // Right align for monetary values
    
    // Format untuk saldo
    $balance = 'Rp ' . number_format($balance, 0, ',', '.');
    $pdf->Cell($header_widths[5] * $scale_factor, 10, $balance, 1, 0, 'L'); // Right align for monetary values
    $pdf->Ln();
}

// Tambahkan baris total di bawah tabel
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(($header_widths[0] + $header_widths[1] + $header_widths[2]) * $scale_factor, 10, 'TOTAL', 1, 0, 'C');
$pdf->Cell($header_widths[3] * $scale_factor, 10, 'Rp ' . number_format($total_debit, 0, ',', '.'), 1, 0, 'L');
$pdf->Cell($header_widths[4] * $scale_factor, 10, 'Rp ' . number_format($total_credit, 0, ',', '.'), 1, 0, 'L');
$pdf->Cell($header_widths[5] * $scale_factor, 10, 'Rp ' . number_format($previous_balance, 0, ',', '.'), 1, 0, 'L');
$pdf->Ln();

// Menambahkan tanggal saat ini di halaman terakhir
$date_now = date('d-m-Y'); // Format tanggal menjadi d-m-Y
$pdf->Ln(10); // Tambahkan jarak
$pdf->Cell(0, 10, 'Tanggal: ' . $date_now, 0, 1, 'C'); // Menampilkan tanggal saat ini

$pdf->Ln(5); // Tambahkan jarak
$pdf->Ln(5); // Tambahkan jarak
$pdf->Ln(5); // Tambahkan jarak

// Tanda Tangan
$pdf->Cell(0, 10, '(___________________________)', 0, 1, 'C'); // Menampilkan tulisan Tanda Tangan

// Output PDF dengan nama yang disesuaikan
$pdf->Output('Laporan Buku Kas ' . $date_now . '.pdf', 'D');
exit;
?>
