<?php
include 'db.php';
include 'header.php';
include 'navbar.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Prepare SQL queries
$sql = "SELECT * FROM transactions WHERE user_id = ?";
$count_sql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ?";

// Count total transactions
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_transactions = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_transactions / $limit);

// Fetch data for the current page
$stmt = $conn->prepare($sql . " ORDER BY date ASC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Calculate the initial balance from all previous pages if the page is greater than 1
$initial_balance = 0; // Default initial balance for the first page

if ($page > 1) {
    // Calculate the balance from the beginning until the previous page
    $previous_balance_stmt = $conn->prepare("SELECT SUM(debit) - SUM(credit) AS previous_balance FROM transactions WHERE user_id = ? AND date <= (SELECT date FROM transactions WHERE user_id = ? ORDER BY date ASC LIMIT 1 OFFSET ?)");
    $previous_offset = ($page - 2) * $limit + ($limit - 1);
    $previous_balance_stmt->bind_param("iii", $user_id, $user_id, $previous_offset);
    $previous_balance_stmt->execute();
    $previous_balance_result = $previous_balance_stmt->get_result();
    $initial_balance = $previous_balance_result->fetch_assoc()['previous_balance'] ?: 0;
}

// Get total debit and total credit from all transactions
$total_debit_query = "SELECT SUM(debit) AS total_debit, SUM(credit) AS total_credit FROM transactions WHERE user_id = ?";
$total_debit_stmt = $conn->prepare($total_debit_query);
$total_debit_stmt->bind_param("i", $user_id);
$total_debit_stmt->execute();
$total_debit_result = $total_debit_stmt->get_result();
$total_debit_row = $total_debit_result->fetch_assoc();

$total_debit = $total_debit_row['total_debit'] ?: 0; // Jika null, set ke 0
$total_credit = $total_debit_row['total_credit'] ?: 0; // Jika null, set ke 0

// Hitung saldo total berdasarkan rumus: Total Saldo = Total Debit - Total Kredit
$total_balance = $total_debit - $total_credit;

?>

<style>
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
    
    /* Tambahkan ini untuk tampilan laptop */
    @media (min-width: 1024px) {
        .table {
            width: 100%;
        }
        .container {
            max-width: 80%; /* Perlebar container untuk laptop */
        }
    }
</style>

<div class="container">
    <br><br><br>
    <h2>Dashboard</h2>
    <br>

    <a href="add_transaction.php" class="btn btn-success mb-3"><strong><i class="fas fa-plus-square"></i> Tambah Transaksi</strong></a>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Saldo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php 
                    $no = $offset + 1; // Initialize number with offset
                    $previous_balance = $initial_balance; // Saldo awal dari halaman sebelumnya
                    ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                        // Update balance based on debit and credit
                        $current_balance = $previous_balance + $row['debit'] - $row['credit']; // Hitung saldo saat ini
                        ?>
                        <tr>
                            <td data-label="No"><?php echo $no++; ?></td>
                            <td data-label="Tanggal"><?php echo date('d-m-Y', strtotime($row['date'])); ?></td>
                            <td data-label="Deskripsi"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td data-label="Debit"><?php echo 'Rp ' . number_format($row['debit'], 0, ',', '.'); ?></td>
                            <td data-label="Kredit"><?php echo 'Rp ' . number_format($row['credit'], 0, ',', '.'); ?></td>
                            <td data-label="Saldo"><?php echo 'Rp ' . number_format($current_balance, 0, ',', '.'); ?></td>
                            <td>
                                <a href="edit_transaction.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"><strong><i class="fas fa-edit"></i> Edit</strong></a>
                                <a href="delete_transaction.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');"><strong><i class="fas fa-trash-alt"></i> Hapus</strong></a>
                            </td>
                        </tr>
                        <?php 
                        // Set previous balance for next iteration
                        $previous_balance = $current_balance; // Update previous balance
                        ?>
                    <?php endwhile; ?>
                    <!-- Row for total amounts -->
                    <tr>
                        <td colspan="3" class="text-end"><center><strong>TOTAL</strong></center></td>
                        <td><strong><?php echo 'Rp ' . number_format($total_debit, 0, ',', '.'); ?></strong></td>
                        <td><strong><?php echo 'Rp ' . number_format($total_credit, 0, ',', '.'); ?></strong></td>
                        <td><strong><?php echo 'Rp ' . number_format($total_balance, 0, ',', '.'); ?></strong></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada transaksi untuk ditampilkan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Navigation -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <br>
    <a href="delete_all_data.php" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus semua data transaksi?');"><strong><i class="fas fa-trash-alt"></i> Hapus Semua Data</strong></a>
    
    <div class="d-flex flex-column flex-sm-row justify-content-center mb-3 mt-3">
        <a href="export.php" class="btn btn-warning me-2 mb-2 mb-sm-0">
            <strong><i class="fas fa-print"></i> Cetak PDF</strong>
        </a>
        <button id="copyButton" class="btn btn-primary me-2 mb-2 mb-sm-0" onclick="copyAllTableData()">
            <strong><i class="fas fa-copy"></i> Copy Data</strong>
        </button>
        <a href="export_to_excel.php" class="btn btn-success">
            <strong><i class="fas fa-file-excel"></i> Ekspor ke Excel</strong>
        </a>
    </div>

</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#copyButton').click(function() {
        $.ajax({
            url: 'fetch_all_transactions.php',
            method: 'GET',
            success: function(response) {
                var tempElement = document.createElement('textarea');
                tempElement.value = response;
                document.body.appendChild(tempElement);
                tempElement.select();
                document.execCommand('copy');
                document.body.removeChild(tempElement);
                alert('Data berhasil disalin. Anda dapat mem-paste di Excel.');
            },
            error: function() {
                alert('Gagal mengambil data.');
            }
        });
    });
});
</script>

