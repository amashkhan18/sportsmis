<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $title ?? 'Admin Dashboard | HPCL Tournament 2026' ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE css -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>" class="nav-link font-weight-bold text-primary" target="_blank">
          <i class="fas fa-external-link-alt me-1"></i> Public Dashboard
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/volunteer" class="nav-link text-success" target="_blank">
          <i class="fas fa-mobile-alt me-1"></i> Volunteer Scoring PWA
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/social" class="nav-link text-info" target="_blank">
          <i class="fas fa-camera me-1"></i> Social Content Engine
        </a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-muted">
          <i class="fas fa-user-circle me-1"></i> Logged in as: <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User') ?></strong>
          <?php if (($role = $_SESSION['role'] ?? '') === 'nodal'): ?>
            <span class="badge badge-info ml-1"><i class="fas fa-user-tie mr-1"></i> Team Manager <?= !empty($_SESSION['unit_code']) ? '(' . htmlspecialchars($_SESSION['unit_code']) . ')' : '' ?></span>
          <?php elseif ($role === 'photographer'): ?>
            <span class="badge badge-warning text-dark ml-1"><i class="fas fa-camera mr-1"></i> Media Photographer</span>
          <?php else: ?>
            <span class="badge badge-secondary ml-1"><?= ucfirst($role ?: 'Admin') ?></span>
          <?php endif; ?>
        </span>
      </li>
      <li class="nav-item">
        <a href="<?= BASE_URL ?>/admin/logout" class="nav-link text-danger font-weight-bold">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/admin" class="brand-link">
      <i class="fas fa-medal ml-3 mr-2 text-warning"></i>
      <span class="brand-text font-weight-bold">HPCL Sports MIS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <?php if (($role = $_SESSION['role'] ?? '') === 'nodal'): ?>
          <!-- TEAM MANAGER SIDEBAR -->
          <ul class="nav nav-pills nav-sidebar flex-column nav-compact" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-header">TEAM MANAGER WORKSPACE</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/teams" class="nav-link <?= ($active_menu == 'teams') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-users text-warning"></i>
                <p>Teams & Athlete Rosters <span class="badge badge-success right">Manage</span></p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-link <?= ($active_menu == 'dashboard') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Tournament Overview</p>
              </a>
            </li>

            <li class="nav-header">PUBLIC HUBS</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>" class="nav-link" target="_blank">
                <i class="nav-icon fas fa-external-link-alt text-primary"></i>
                <p>Live Public Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/social" class="nav-link" target="_blank">
                <i class="nav-icon fas fa-magic text-info"></i>
                <p>Scorecard Studio</p>
              </a>
            </li>
          </ul>
        <?php elseif ($role === 'photographer'): ?>
          <!-- PHOTOGRAPHER SIDEBAR -->
          <ul class="nav nav-pills nav-sidebar flex-column nav-compact" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-header">MEDIA & PHOTOGRAPHY</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/photos" class="nav-link <?= ($active_menu == 'photos') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-camera text-warning"></i>
                <p>Photo Repository <span class="badge badge-success right">Upload</span></p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/social" class="nav-link" target="_blank">
                <i class="nav-icon fas fa-magic text-info"></i>
                <p>Scorecard Studio</p>
              </a>
            </li>

            <li class="nav-header">PUBLIC HUBS</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>" class="nav-link" target="_blank">
                <i class="nav-icon fas fa-external-link-alt text-primary"></i>
                <p>Live Public Dashboard</p>
              </a>
            </li>
          </ul>
        <?php else: ?>
          <!-- CENTRAL ADMIN SIDEBAR -->
          <ul class="nav nav-pills nav-sidebar flex-column nav-compact" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin" class="nav-link <?= ($active_menu == 'dashboard') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            
            <li class="nav-header">TOURNAMENT SETUP</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/games" class="nav-link <?= ($active_menu == 'games') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-gamepad"></i>
                <p>Games & Disciplines</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/score-formats" class="nav-link <?= ($active_menu == 'score_formats') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-sliders-h text-warning"></i>
                <p>Custom Score Formats</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/units" class="nav-link <?= ($active_menu == 'units') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-building"></i>
                <p>Units & Teams</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/teams" class="nav-link <?= ($active_menu == 'teams') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-users"></i>
                <p>Player Rosters</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/facilities" class="nav-link <?= ($active_menu == 'facilities') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-map-marker-alt"></i>
                <p>Facilities & Courts</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/ceremonies" class="nav-link <?= ($active_menu == 'ceremonies') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-flag-checkered"></i>
                <p>TMM & Ceremonies</p>
              </a>
            </li>
            
            <li class="nav-header">TOURNAMENT OPERATIONS</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/draws" class="nav-link <?= ($active_menu == 'draws') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-sitemap"></i>
                <p>Draws & Pairings</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/schedules" class="nav-link <?= ($active_menu == 'schedules') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Schedules & Clash</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/scores" class="nav-link <?= ($active_menu == 'scores') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-edit"></i>
                <p>Live Scores & Edit</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/points" class="nav-link <?= ($active_menu == 'points') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-trophy"></i>
                <p>Leaderboard & Points</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/photos" class="nav-link <?= ($active_menu == 'photos') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-images"></i>
                <p>Photo Repository</p>
              </a>
            </li>

            <li class="nav-header">SYSTEM & AUDIT</li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/users" class="nav-link <?= ($active_menu == 'users') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>User Roles</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/audit" class="nav-link <?= ($active_menu == 'audit') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-clipboard-list"></i>
                <p>Audit Trail</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>/admin/export" class="nav-link <?= ($active_menu == 'export') ? 'active' : '' ?>">
                <i class="nav-icon fas fa-file-export"></i>
                <p>Data Export</p>
              </a>
            </li>
          </ul>
        <?php endif; ?>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <?php require $admin_content; ?>
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
      HPCL 20th All India Inter Unit Sports & Games | Balewadi, Pune
    </div>
    <strong>Copyright &copy; 2026 HPCL.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<?php if(isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>
