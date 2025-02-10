<?php
include 'db.php';
include 'header.php';
include 'navbar.php';

session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Cek apakah ada ID pengguna yang diterima untuk dihapus
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Hapus transaksi yang terkait dengan pengguna ini
    $stmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Hapus pengguna
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Redirect ke halaman manage_user setelah berhasil dihapus
    header('Location: manage_user.php');
    exit;
} else {
    die("ID pengguna tidak valid.");
}
?>

<div class="container">
    <br><br><br>
    <h2>Pengguna Berhasil Dihapus</h2>
    <p>Pengguna dan semua transaksi yang terkait telah dihapus.</p>
    <a href="manage_user.php" class="btn btn-secondary">Kembali ke Kelola Pengguna</a>
</div>

<?php include 'footer.php'; ?>
