<?php
include 'db.php';
include 'header.php';
include 'navbar.php';


// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Kunci enkripsi (harus sama dengan yang digunakan saat enkripsi password)
$encryption_key = "your_encryption_key";

// Tentukan jumlah data per halaman
$per_page = 10;

// Tentukan halaman aktif
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Hitung offset berdasarkan halaman aktif
$offset = ($current_page - 1) * $per_page;

// Ambil data pengguna dengan pagination
$sql = "SELECT id, username, AES_DECRYPT(password, ?) AS decrypted_password, role FROM users LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $encryption_key, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Hitung total data untuk menentukan jumlah halaman
$total_sql = "SELECT COUNT(*) AS total FROM users";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $per_page);
?>

<style>
    /* CSS Umum untuk Tampilan di Laptop */
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    table.table {
        font-size: 16px; /* Ukuran font sedikit lebih besar untuk kenyamanan di layar laptop */
    }

    .table th, .table td {
        padding: 12px 15px; /* Menambah ruang antar sel untuk tampilan yang lebih lega */
    }

    .pagination .page-item .page-link {
        font-size: 14px;
        padding: 10px 15px;
    }

    /* CSS untuk Tampilan Mobile */
    @media (max-width: 768px) {
        .table thead {
            display: none;
        }
        .table tbody tr {
            display: block;
            margin-bottom: 15px;
        }
        .table tbody td {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        .table tbody td:before {
            content: attr(data-label);
            font-weight: bold;
            flex-basis: 50%;
            text-align: left;
        }
    }

    /* CSS untuk Tampilan Laptop */
    @media (min-width: 769px) and (max-width: 1200px) {
        .table {
            width: 100%;
            margin-bottom: 20px;
        }

        .pagination {
            justify-content: center; /* Menempatkan pagination di tengah */
        }

        /* Memperbaiki margin dan padding untuk elemen-elemen agar lebih proporsional di layar laptop */
        .btn {
            margin: 5px;
            font-size: 14px;
        }

        .container {
            padding: 20px; /* Memberi ruang di sekitar konten agar tidak terlalu rapat */
        }
    }
</style>

<div class="container">
    <br><br><br><br>
    <h2>Kelola Pengguna</h2>
    <a href="add_user.php" class="btn btn-primary mb-3"><strong><i class="fas fa-plus-square"></i> Tambah Pengguna</a></strong>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengguna</th>
                <th>Password</th>
                <th>Peran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = $offset + 1; // Nomor urut berdasarkan halaman
            while ($row = $result->fetch_assoc()):
                $username = htmlspecialchars($row['username'] ?? ''); // Validasi nilai null
                $password = htmlspecialchars($row['decrypted_password'] ?? ''); // Validasi nilai null
                $role = htmlspecialchars($row['role'] ?? ''); // Validasi nilai null
            ?>
                <tr>
                    <td data-label="No"><?php echo $no++; ?></td>
                    <td data-label="Username"><?php echo $username; ?></td>
                    <td data-label="Password"><?php echo $password; ?></td>
                    <td data-label="Role"><?php echo $role; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"><strong><i class="fas fa-edit"></i> Edit</a></strong>
                        <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');"><strong><i class="fas fa-trash"></i> Hapus</a></strong>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>

    </table>

    <!-- Navigasi Pagination di Bawah Tabel -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Sebelumnya</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Berikutnya</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <a href="dashboard.php" class="btn btn-secondary"><strong><i class="fas fa-undo-alt"></i> Kembali</a></strong>
</div>

<?php include 'footer.php'; ?>
