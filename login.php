<?php
require_once __DIR__ . '/../helpers/auth.php';
if (current_user()) { header('Location: ' . base_url('dashboard.php')); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username'], 'role' => $user['role']];
        header('Location: ' . base_url('dashboard.php'));
        exit;
    }
    $errors[] = 'Username atau password salah.'; // pesan sama agar tidak membocorkan username
}
$title = 'Login';
require __DIR__ . '/../views/header.php';
?>
<div class="card login">
  <h1>Login</h1>
  <?php require __DIR__ . '/../views/errors.php'; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Username</label><input type="text" name="username" required autofocus>
    <label>Password</label><input type="password" name="password" required>
    <p><button>Masuk</button></p>
  </form>
  <p class="muted">Demo: admin / admin123 &middot; staff / staff123</p>
</div>
<?php require __DIR__ . '/../views/footer.php'; ?>
