<?php
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'volunteer';
        $unit_id = (int)($_POST['unit_id'] ?? 0) ?: null;

        if ($username && $password && $full_name) {
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username]);
            if ($check->fetch()) {
                $err = "Username '$username' already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, unit_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $hash, $full_name, $role, $unit_id]);
                log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'USER_CREATE', "Created user '$username' with role '$role'");
                $_SESSION['flash_msg'] = "User '$username' created successfully.";
                header("Location: " . BASE_URL . "/admin/users");
                exit;
            }
        } else {
            $err = "Username, password, and full name are required.";
        }
    } elseif ($action === 'change_password') {
        $id = (int)($_POST['id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($id <= 0) {
            $err = "Invalid user specified.";
        } elseif (empty($new_password)) {
            $err = "Password cannot be empty.";
        } elseif (strlen($new_password) < 4) {
            $err = "Password must be at least 4 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $err = "New password and Confirm password do not match.";
        } else {
            $uStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $uStmt->execute([$id]);
            $uData = $uStmt->fetch();

            if (!$uData) {
                $err = "User not found in the database.";
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hash, $id]);

                log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'USER_PASSWORD_CHANGE', "Changed password for user '{$uData['username']}' (ID #$id)");
                $_SESSION['flash_msg'] = "Password for user '{$uData['username']}' updated successfully.";
                header("Location: " . BASE_URL . "/admin/users");
                exit;
            }
        }
    } elseif ($action === 'edit_user') {
        $id = (int)($_POST['id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'volunteer';
        $unit_id = (int)($_POST['unit_id'] ?? 0) ?: null;

        if ($id <= 0) {
            $err = "Invalid user specified.";
        } elseif (empty($full_name)) {
            $err = "Full name cannot be empty.";
        } else {
            // If primary admin (#1), preserve admin role
            if ($id === 1) {
                $role = 'admin';
            }

            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ?, unit_id = ? WHERE id = ?");
            $stmt->execute([$full_name, $role, $unit_id, $id]);

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'USER_EDIT', "Updated details/unit assignment for user ID #$id (Role: $role, Unit ID: " . ($unit_id ?: 'Central') . ")");
            $_SESSION['flash_msg'] = "User details updated successfully.";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        }
    } elseif ($action === 'delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 1) { // Prevent deleting primary admin
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'USER_DELETE', "Deleted user #$id");
            $_SESSION['flash_msg'] = "User removed.";
            header("Location: " . BASE_URL . "/admin/users");
            exit;
        } else {
            $err = "Primary Administrator cannot be deleted.";
        }
    }
}

// Handle GET delete for users
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id > 1) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'USER_DELETE', "Deleted user #$id");
        $_SESSION['flash_msg'] = "User removed.";
        header("Location: " . BASE_URL . "/admin/users");
        exit;
    } elseif ($id === 1) {
        $err = "Primary Administrator cannot be deleted.";
    }
}

// Fetch users
$users = $pdo->query("
    SELECT u.*, un.short_code as unit_code, un.name as unit_name
    FROM users u
    LEFT JOIN units un ON u.unit_id = un.id
    ORDER BY FIELD(u.role, 'admin', 'nodal', 'photographer', 'volunteer'), u.username ASC
")->fetchAll();

$allUnits = $pdo->query("SELECT id, name, short_code FROM units ORDER BY short_code ASC")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-user-shield text-primary mr-2"></i> User Roles & Credential Management</h1>
        <p class="text-muted mb-0">Role-based access: Admin (full), Team Manager (unit teams & athletes), Volunteer (PWA scoring), Photographer (media upload)</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
          <i class="fas fa-user-plus mr-1"></i> Add System User
        </button>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <div class="card elevation-2">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Active System Accounts</h3>
        <span class="badge badge-info"><?= count($users) ?> Users Registered</span>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Username</th>
              <th>System Role</th>
              <th>Assigned Unit</th>
              <th>Created At</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><strong>#<?= $u['id'] ?></strong></td>
                <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                <td>
                  <?php if ($u['role'] === 'admin'): ?>
                    <span class="badge badge-danger text-uppercase"><i class="fas fa-crown mr-1"></i> Admin</span>
                  <?php elseif ($u['role'] === 'nodal'): ?>
                    <span class="badge badge-info text-uppercase font-weight-bold"><i class="fas fa-user-tie mr-1"></i> Team Manager</span>
                  <?php elseif ($u['role'] === 'photographer'): ?>
                    <span class="badge badge-secondary text-uppercase"><i class="fas fa-camera mr-1"></i> Photographer</span>
                  <?php else: ?>
                    <span class="badge badge-success text-uppercase"><i class="fas fa-mobile-alt mr-1"></i> Volunteer Scorer</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($u['unit_code'])): ?>
                    <span class="badge badge-light border text-dark font-weight-bold mr-1"><?= htmlspecialchars($u['unit_code']) ?></span>
                    <span class="font-weight-600"><?= htmlspecialchars($u['unit_name']) ?></span>
                  <?php elseif ($u['role'] === 'nodal'): ?>
                    <span class="badge badge-warning text-dark font-weight-bold">
                      <i class="fas fa-exclamation-triangle mr-1"></i> No Unit Assigned
                    </span>
                    <small class="d-block text-danger font-weight-bold">Sees all 12 units (EZ, MR, etc.)</small>
                  <?php else: ?>
                    <span class="text-muted"><i class="fas fa-building mr-1"></i> All Units (HQ / Central)</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td class="text-right text-nowrap">
                  <button type="button" class="btn btn-sm btn-outline-primary btn-edit-user mr-1"
                          data-id="<?= $u['id'] ?>"
                          data-username="<?= htmlspecialchars($u['username']) ?>"
                          data-fullname="<?= htmlspecialchars($u['full_name']) ?>"
                          data-role="<?= $u['role'] ?>"
                          data-unit-id="<?= $u['unit_id'] ?? '' ?>"
                          title="Edit User & Unit Assignment">
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>

                  <button type="button" class="btn btn-sm btn-outline-warning btn-change-pwd mr-1" 
                          data-id="<?= $u['id'] ?>" 
                          data-username="<?= htmlspecialchars($u['username']) ?>" 
                          data-fullname="<?= htmlspecialchars($u['full_name']) ?>"
                          title="Change Password">
                    <i class="fas fa-key mr-1"></i> Change Password
                  </button>

                  <?php if ($u['id'] > 1): ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete user \'<?= htmlspecialchars($u['username']) ?>\'?');">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="badge badge-secondary" title="Master Account cannot be deleted">Master</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create_user">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus text-primary mr-2"></i> Create System Account</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" placeholder="e.g. Ramesh Kumar" required>
          </div>
          <div class="form-group">
            <label>Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" placeholder="e.g. manager_mumbai" required>
          </div>
          <div class="form-group">
            <label>Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div class="form-group">
            <label>System Role <span class="text-danger">*</span></label>
            <select name="role" class="form-control" required>
              <option value="nodal" selected>Team Manager (Register Teams & Athletes for Unit)</option>
              <option value="volunteer">Volunteer (Scorer - Match PWA Access)</option>
              <option value="photographer">Photographer (Media Upload Portal)</option>
              <option value="admin">Administrator (Full Access)</option>
            </select>
            <small class="form-text text-muted">Team Managers can log in to register teams, enroll athletes, and track team composition quotas for their assigned HPCL Unit.</small>
          </div>
          <div class="form-group">
            <label>Affiliated HPCL Unit</label>
            <select name="unit_id" class="form-control">
              <option value="">-- Central / All Units (Admin & Central Scorers) --</option>
              <?php foreach ($allUnits as $un): ?>
                <option value="<?= $un['id'] ?>"><?= htmlspecialchars($un['short_code']) ?> - <?= htmlspecialchars($un['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted"><strong>Required for Team Manager:</strong> restricts team and athlete registration to this unit only.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="edit_user">
        <input type="hidden" name="id" id="edit_user_id" value="">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-user-edit mr-2"></i> Edit User & Unit Assignment
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Username</label>
            <input type="text" id="edit_username" class="form-control bg-light font-weight-bold" readonly>
            <small class="form-text text-muted">Usernames cannot be changed.</small>
          </div>
          <div class="form-group">
            <label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" id="edit_full_name" class="form-control font-weight-bold" required>
          </div>
          <div class="form-group">
            <label>System Role <span class="text-danger">*</span></label>
            <select name="role" id="edit_role" class="form-control" required>
              <option value="nodal">Team Manager (Register Teams & Athletes for Unit)</option>
              <option value="volunteer">Volunteer (Scorer - Match PWA Access)</option>
              <option value="photographer">Photographer (Media Upload Portal)</option>
              <option value="admin">Administrator (Full Access)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Affiliated HPCL Unit <span class="text-danger">* for Team Managers</span></label>
            <select name="unit_id" id="edit_unit_id" class="form-control font-weight-bold">
              <option value="">-- Central / All Units (Admin & Central Scorers) --</option>
              <?php foreach ($allUnits as $un): ?>
                <option value="<?= $un['id'] ?>"><?= htmlspecialchars($un['short_code']) ?> - <?= htmlspecialchars($un['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-primary">
              <i class="fas fa-info-circle mr-1"></i> <strong>Crucial for Team Managers:</strong> Assigning a unit (e.g. <code>MR - Mumbai Refinery</code>) restricts their view and roster access exclusively to their unit. If left empty, they will see teams across all 12 units (EZ, MR, etc.).
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-save mr-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="formChangePassword">
        <input type="hidden" name="action" value="change_password">
        <input type="hidden" name="id" id="pwd_user_id" value="">
        <div class="modal-header bg-warning">
          <h5 class="modal-title font-weight-bold text-dark">
            <i class="fas fa-key mr-2"></i> Change User Password
          </h5>
          <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="alert alert-light border mb-3">
            <div class="d-flex align-items-center">
              <i class="fas fa-user-circle fa-2x text-primary mr-3"></i>
              <div>
                <strong id="pwd_modal_fullname" class="d-block text-dark"></strong>
                <small class="text-muted">Username: <code id="pwd_modal_username" class="font-weight-bold"></code></small>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="new_password">New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" minlength="4" required autocomplete="new-password">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary toggle-pwd-visibility" data-target="new_password" title="Show/Hide Password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <small class="form-text text-muted">Minimum 4 characters.</small>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter new password" minlength="4" required autocomplete="new-password">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary toggle-pwd-visibility" data-target="confirm_password" title="Show/Hide Password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <small id="pwd-match-feedback" class="form-text font-weight-bold"></small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="btn-submit-pwd" class="btn btn-warning font-weight-bold text-dark">
            <i class="fas fa-check mr-1"></i> Update Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function() {
  if (typeof jQuery !== 'undefined') {
    // Open Edit User modal
    $(document).on('click', '.btn-edit-user', function() {
      var id = $(this).data('id');
      var username = $(this).data('username');
      var fullname = $(this).data('fullname');
      var role = $(this).data('role');
      var unitId = $(this).data('unit-id');

      $('#edit_user_id').val(id);
      $('#edit_username').val(username);
      $('#edit_full_name').val(fullname);
      $('#edit_role').val(role);
      $('#edit_unit_id').val(unitId || '');

      if (id === 1) {
        $('#edit_role').prop('disabled', true);
      } else {
        $('#edit_role').prop('disabled', false);
      }

      $('#editUserModal').modal('show');
    });

    // Open Change Password modal
    $(document).on('click', '.btn-change-pwd', function() {
      var id = $(this).data('id');
      var username = $(this).data('username');
      var fullname = $(this).data('fullname');

      $('#pwd_user_id').val(id);
      $('#pwd_modal_username').text(username);
      $('#pwd_modal_fullname').text(fullname || username);
      $('#new_password').val('');
      $('#confirm_password').val('');
      $('#pwd-match-feedback').text('').removeClass('text-danger text-success');

      $('#changePasswordModal').modal('show');
    });

    // Toggle password visibility
    $('.toggle-pwd-visibility').on('click', function() {
      var targetId = $(this).data('target');
      var input = $('#' + targetId);
      var icon = $(this).find('i');

      if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
      } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
      }
    });

    // Password matching validation
    $('#confirm_password, #new_password').on('keyup input', function() {
      var p1 = $('#new_password').val();
      var p2 = $('#confirm_password').val();
      var feedback = $('#pwd-match-feedback');
      var submitBtn = $('#btn-submit-pwd');

      if (p2.length === 0) {
        feedback.text('').removeClass('text-danger text-success');
        submitBtn.prop('disabled', false);
        return;
      }

      if (p1 !== p2) {
        feedback.text('Passwords do not match').removeClass('text-success').addClass('text-danger');
        submitBtn.prop('disabled', true);
      } else {
        feedback.text('Passwords match').removeClass('text-danger').addClass('text-success');
        submitBtn.prop('disabled', false);
      }
    });

    $('#formChangePassword').on('submit', function(e) {
      var p1 = $('#new_password').val();
      var p2 = $('#confirm_password').val();
      if (p1 !== p2) {
        e.preventDefault();
        alert('Passwords do not match. Please verify and try again.');
      }
    });
  }
});
</script>
