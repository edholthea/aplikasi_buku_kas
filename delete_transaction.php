<?php
include 'db.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil ID transaksi dari parameter URL
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Periksa apakah ID transaksi valid
if ($transaction_id > 0) {
    // Query untuk memastikan bahwa transaksi milik user yang login
    $sql = "SELECT * FROM transactions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika transaksi ditemukan, hapus
    if ($result->num_rows > 0) {
        $delete_sql = "DELETE FROM transactions WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $transaction_id);

        if ($delete_stmt->execute()) {
            // Redirect ke dashboard setelah berhasil menghapus
            header('Location: dashboard.php?message=deleted');
            exit;
        } else {
            echo "Terjadi kesalahan saat menghapus transaksi.";
        }
    } else {
        echo "Transaksi tidak ditemukan atau tidak milik Anda.";
    }
} else {
    echo "ID transaksi tidak valid.";
}

// Tutup koneksi
$conn->close();
?>
