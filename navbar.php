<?php
include 'db.php'; // Sertakan file koneksi database
session_start();

// Atur waktu timeout (dalam detik)
$timeout_duration = 600; // 10 menit = 600 detik

// Cek waktu terakhir sesi aktif
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Jika sesi sudah lebih dari 10 menit tidak aktif, lakukan logout
    session_unset();     // Menghapus semua variabel sesi
    session_destroy();   // Menghancurkan sesi
    header('Location: login.php'); // Arahkan kembali ke halaman login
    exit;
}

// Update waktu terakhir sesi aktif
$_SESSION['LAST_ACTIVITY'] = time();

// Ambil username pengguna yang sedang aktif dari database
$username = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']); // Ambil username
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><strong><i class="fas fa-book"></i> Buku Kas</a></strong>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><strong><i class="fas fa-tachometer-alt"></i> Dashboard</a></strong>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="edit_profile.php"><strong><i class="fas fa-user"></i> Edit Profile</a></strong>
                </li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_user.php"><strong><i class="fas fa-users"></i> Manage User</a></strong>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="https://wa.me/6283865948003" target="_blank"><strong><i class="fab fa-whatsapp"></i> Hubungi Admin</a></strong>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><strong><i class="fas fa-sign-out-alt"></i> Logout</a></strong>
                </li>
            </ul>
        </div>
        <!-- Menampilkan username di pojok kanan -->
        <span class="navbar-text ml-auto">
            Selamat Datang! <?php echo $username; ?>
        </span>
    </div>
</nav>
