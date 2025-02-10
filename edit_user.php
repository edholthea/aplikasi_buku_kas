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

// Cek apakah ada ID pengguna yang diterima untuk diedit
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Ambil data pengguna berdasarkan ID
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("Pengguna tidak ditemukan.");
    }
} else {
    die("ID pengguna tidak valid.");
}

// Proses form ketika disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Jika password tidak diubah, jangan perbarui field password
    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = AES_ENCRYPT(?, 'your_encryption_key'), role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $password, $role, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $user_id);
    }

    if ($stmt->execute()) {
        header('Location: manage_user.php');
        exit;
    } else {
        $error = "Gagal memperbarui pengguna. Coba lagi.";
    }
}
?>

<div class="container">
    <br><br><br>
    <h2>Edit Pengguna</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password"><i class="fas fa-key"></i> Password (Kosongkan jika tidak ingin mengubah)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="role">Peran</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Pengguna</button>
        <a href="manage_user.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?php include 'footer.php'; ?>
