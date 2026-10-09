<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // First time setup: If users table is empty, create a default admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, password, role) VALUES ('admin', '$hash', 'admin')");
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'] ?: $user['username'];
        $_SESSION['unit_id'] = $user['unit_id'] ?: null;

        if (!empty($user['unit_id'])) {
            $uStmt = $pdo->prepare("SELECT name, short_code, color_code FROM units WHERE id = ?");
            $uStmt->execute([$user['unit_id']]);
            $uData = $uStmt->fetch();
            if ($uData) {
                $_SESSION['unit_name'] = $uData['name'];
                $_SESSION['unit_code'] = $uData['short_code'];
                $_SESSION['unit_color'] = $uData['color_code'];
            }
        }

        if ($user['role'] === 'nodal') {
            header("Location: " . BASE_URL . "/admin/teams");
            exit;
        } elseif ($user['role'] === 'volunteer') {
            header("Location: " . BASE_URL . "/volunteer");
            exit;
        } elseif ($user['role'] === 'photographer') {
            header("Location: " . BASE_URL . "/admin/photos");
            exit;
        } else {
            header("Location: " . BASE_URL . "/admin/dashboard");
            exit;
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin & Team Manager Login | HPCL Tournament 2026</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE css -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page bg-dark">
<div class="login-box">
  <div class="login-logo">
    <a href="<?= BASE_URL ?>" class="text-white"><b>HPCL</b> Sports Portal</a>
  </div>
  <!-- /.login-logo -->
  <div class="card elevation-3">
    <div class="card-body login-card-body">
      <p class="login-box-msg font-weight-bold text-dark">Sign in to start your session</p>

      <?php if ($error): ?>
          <div class="alert alert-danger p-2 text-center small"><?= $error ?></div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/admin/login" method="post">
        <div class="input-group mb-3">
          <input type="text" name="username" id="login_user" class="form-control" placeholder="Username" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" id="login_pass" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block font-weight-bold">
              <i class="fas fa-sign-in-alt mr-1"></i> Sign In
            </button>
          </div>
        </div>
            </form>
    </div>
    <!-- /.login-card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
