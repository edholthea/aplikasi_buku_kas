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

if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];

    // Ambil data transaksi
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();

    if (!$transaction) {
        die("Transaksi tidak ditemukan.");
    }
} else {
    die("ID transaksi tidak valid.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];
    // Hapus pemisah ribuan sebelum menyimpan ke database
    $debit = str_replace('.', '', $_POST['debit']);
    $credit = str_replace('.', '', $_POST['credit']);
    $balance = $debit - $credit;

    $stmt = $conn->prepare("UPDATE transactions SET date = ?, description = ?, debit = ?, credit = ?, balance = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssddiii", $date, $description, $debit, $credit, $balance, $transaction_id, $user_id);

    if ($stmt->execute()) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Gagal memperbarui transaksi. Coba lagi.";
    }
}
?>

<div class="container">
    <br><br><br>
    <h2>Edit Transaksi</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="date">Tanggal</label>
            <input type="date" class="form-control" id="date" name="date" value="<?php echo $transaction['date']; ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($transaction['description']); ?>" required>
        </div>
        <div class="form-group">
            <label for="debit">Debit (Rp)</label>
            <input type="text" class="form-control" id="debit" name="debit" value="<?php echo number_format($transaction['debit'], 0, ',', '.'); ?>">
        </div>
        <div class="form-group">
            <label for="credit">Kredit (Rp)</label>
            <input type="text" class="form-control" id="credit" name="credit" value="<?php echo number_format($transaction['credit'], 0, ',', '.'); ?>">
        </div>
        <button type="submit" class="btn btn-primary"><strong><i class="fas fa-save"></i> Update</button></strong>
        <a href="dashboard.php" class="btn btn-secondary"><strong><i class="fas fa-undo-alt"></i> Kembali</a></strong>
    </form>
</div>

<script>
    // Fungsi untuk menambahkan separator ribuan
    function formatRupiah(input) {
        let value = input.value;
        // Hapus karakter non-digit
        value = value.replace(/[^,\d]/g, '');
        // Pisahkan menjadi array ribuan
        const parts = value.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
