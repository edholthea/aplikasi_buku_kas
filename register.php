<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user'; // Default role set to user

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        header('Location: login.php');
        exit;
    } else {
        $error = "Gagal mendaftar. Coba lagi.";
    }
}
?>

<?php include 'header.php'; ?>
<div class="container">
    <h2>Register</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password"><i class="fas fa-key"></i> Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-sign-in-alt"></i> Register</button>
        <a href="index.php" class="btn btn-secondary"><strong><i class="fas fa-undo-alt"></i> Kembali</a></strong>
    </form>
</div>
<?php include 'footer.php'; ?>
