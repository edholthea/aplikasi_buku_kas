<?php
include 'db.php';
include 'header.php';
include 'navbar.php';

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Hapus semua transaksi untuk user yang login
$stmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header('Location: dashboard.php');
    exit;
} else {
    echo "<div class='container'><h2>Gagal menghapus semua data transaksi. Coba lagi.</h2></div>";
}
?>

<?php include 'footer.php'; ?>
