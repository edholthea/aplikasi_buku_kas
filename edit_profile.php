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

// Ambil data pengguna dari database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Jika password diisi, hash password baru
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
    } else {
        // Jika password kosong, hanya update username
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $user_id);
    }

    if ($stmt->execute()) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Gagal memperbarui profil. Coba lagi.";
    }
}
?>

<style>
    /* CSS Umum untuk Tampilan di Laptop */
    .container {
        max-width: 1200px; /* Batasan lebar agar tidak terlalu lebar di layar laptop */
        margin: 0 auto;
        padding: 20px;
        background-color: #f8f9fa; /* Warna latar belakang yang lembut */
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Bayangan lembut */
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        font-size: 16px;
        font-weight: bold;
    }

    .form-control {
        font-size: 14px;
        padding: 10px;
        border-radius: 5px;
    }

    .btn {
        font-size: 14px;
        padding: 10px 20px;
        margin-top: 10px;
        margin-right: 5px;
    }

    /* CSS untuk Tampilan Mobile */
    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }

        h2 {
            font-size: 24px;
        }

        .btn {
            font-size: 12px;
            padding: 8px 15px;
        }
    }

    /* CSS untuk Tampilan Laptop */
    @media (min-width: 769px) and (max-width: 1200px) {
        .container {
            max-width: 700px; /* Memperkecil sedikit lebar untuk layar laptop */
        }

        h2 {
            font-size: 28px; /* Ukuran font lebih besar untuk judul di laptop */
        }

        label {
            font-size: 16px;
        }

        .form-control {
            font-size: 15px;
        }

        .btn {
            font-size: 15px;
            padding: 10px 18px;
        }
    }
</style>

<div class="container">
    <br><br><br>
    <h2>Edit Profil</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password"><i class="fas fa-key"></i> Password (biarkan kosong jika tidak ingin mengubah)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <button type="submit" class="btn btn-primary"><strong><i class="fas fa-save"></i> Simpan</button></strong>
        <a href="dashboard.php" class="btn btn-secondary"><strong><i class="fas fa-undo-alt"></i> Kembali</a></strong>
    </form>
</div>

<?php include 'footer.php'; ?>
