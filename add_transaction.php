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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];
    // Hapus pemisah ribuan sebelum disimpan
    $debit = str_replace('.', '', $_POST['debit']);
    $credit = str_replace('.', '', $_POST['credit']);

    // Ambil saldo terakhir dari transaksi sebelumnya
    $stmt = $conn->prepare("SELECT balance FROM transactions WHERE user_id = ? ORDER BY date DESC, id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $last_balance = $result->fetch_assoc()['balance'] ?? 0;

    // Hitung saldo baru
    $new_balance = $last_balance + $debit - $credit;

    // Simpan transaksi baru
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, date, description, debit, credit, balance) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issddd", $user_id, $date, $description, $debit, $credit, $new_balance);

    if ($stmt->execute()) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Gagal menambahkan transaksi. Coba lagi.";
    }
}
?>

<div class="container">
<br><br><br><br><h4>Tambah Transaksi</h4>
    <form method="POST" action="">
        <div class="form-group">
            <label for="date">Tanggal</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
        <div class="form-group">
            <label for="debit">Debit (Rp) | Uang Masuk</label>
            <input type="text" class="form-control" id="debit" name="debit" step="0.01" value="0">
        </div>
        <div class="form-group">
            <label for="credit">Kredit (Rp) | Uang Keluar</label>
            <input type="text" class="form-control" id="credit" name="credit" step="0.01" value="0">
        </div>
        <button type="submit" class="btn btn-primary"><strong><i class="fas fa-save"></i> Simpan</button></strong>
        <a href="dashboard.php" class="btn btn-secondary"><strong><i class="fas fa-undo-alt"></i> Kembali</a></strong>
    </form>
</div>

<script>
    // Fungsi untuk menambahkan separator ribuan
    function formatRupiah(input) {
        let value = input.value;
        value = value.replace(/[^,\d]/g, ''); // Hapus karakter non-digit
        const parts = value.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Tambah separator
        input.value = parts.join(',');
    }

    document.getElementById('debit').addEventListener('input', function() {
        formatRupiah(this);
    });

    document.getElementById('credit').addEventListener('input', function() {
        formatRupiah(this);
    });
</script>

<?php include 'footer.php'; ?>
