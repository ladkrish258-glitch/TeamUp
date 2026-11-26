<?php
session_start([
  'cookie_lifetime' => 86400 * 30,
  'cookie_httponly' => true,
  'use_only_cookies' => true,
  'cookie_samesite' => 'Lax',
  'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
]);
// include("connect.php");
$conn = mysqli_connect("localhost", "root", "", "teamup") or die("Not Connected");

require_once dirname(__DIR__) . '/Auth/remember_me.php';

// Already logged in this session? Skip the login form entirely.
if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin'])) {
  header("Location: /Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php");
  exit();
}

// No active session, but maybe a "remember me" cookie from a previous visit
// (e.g. browser was closed and reopened a day later) can log them back in.
if (attempt_remember_login($conn)) {
  header("Location: /Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php");
  exit();
}

$error = null;
if (isset($_POST['btnLogin'])) {
  $email_username = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // Server-side validation
  if (empty($email_username) || empty($password)) {
    $error = "Please fill in all fields.";
  } else {
    // Only authenticate against the admins table
    $stmt_admin = mysqli_prepare($conn, "SELECT * FROM admins WHERE username = ? OR email = ?");
    if ($stmt_admin) {
      mysqli_stmt_bind_param($stmt_admin, "ss", $email_username, $email_username);
      mysqli_stmt_execute($stmt_admin);
      $result_admin = mysqli_stmt_get_result($stmt_admin);

      if (mysqli_num_rows($result_admin) > 0) {
        $row_admin = mysqli_fetch_assoc($result_admin);

        // Support both bcrypt hashes and plain text passwords (fallback) for admins table
        $pwd_match = false;
        if (password_verify($password, $row_admin['password'])) {
          $pwd_match = true;
        } else if ($password === $row_admin['password']) {
          $pwd_match = true;
        }

        if ($pwd_match) {
          if (isset($row_admin['status']) && $row_admin['status'] === 'Inactive') {
            $error = "Your admin account is inactive.";
          } else {
            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION['username'] = $row_admin['email'];
            $_SESSION['admin'] = $row_admin['username'];
            $_SESSION['admin_id'] = $row_admin['admin_id'];

            if (!empty($_POST['remember'])) {
              issue_remember_token($conn, (int)$row_admin['admin_id']);
              setcookie('remember_admin_email', $email_username, time() + (86400 * 30), '/', '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
            } else {
              clear_remember_token($conn);
              setcookie('remember_admin_email', '', time() - 3600, '/');
            }

            header("Location: /Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php");
            exit();
          }
        } else {
          $error = "Invalid username/email or password.";
        }
      } else {
        $error = "Invalid username/email or password.";
      }
      mysqli_stmt_close($stmt_admin);
    } else {
      $error = "Database query preparation failed.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in | TeamUp 2026 </title>
  <link rel="icon" href="/Team-Up/images/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="manifest" href="/Team-Up/PHP/AdminPanel/site.webmanifest">
  <meta name="theme-color" content="#1ABB9C" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#1a2332" media="(prefers-color-scheme: dark)">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="/Team-Up/images/apple-touch-icon.svg">
  <meta name="description" content="TeamUp v4 — free admin dashboard template. 60 pages, 20 chart variants, vanilla JS, no Bootstrap, no jQuery. By Colorlib.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Sign in">
  <meta property="og:description" content="TeamUp v4 — free admin dashboard template. 60 pages, 20 chart variants, vanilla JS, no Bootstrap, no jQuery. By Colorlib.">
  <meta property="og:image" content="/Team-Up/images/android-chrome-512x512.svg">
  <meta property="og:site_name" content="TeamUp v4">
  <meta name="twitter:card" content="summary_large_image">
  <script>
    (function() {
      try {
        var t = localStorage.getItem('theme');
        var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = t || (d ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
      } catch (e) {}
    })();
  </script>
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/rolldown-runtime-DEgBLETi.js"></script>
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/toast-DgCSlJPv.js"></script>
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js"></script>
  <?php include_once dirname(__DIR__) . '/Tailwind/head.php'; ?>
  <style>
    .input-group {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--text-muted, #888);
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .password-toggle:hover,
    .password-toggle.active {
      color: var(--text-primary, #333);
    }

    .form-control.has-toggle {
      padding-right: 34px;
    }
  </style>
</head>

<body>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="brand-icon" style="background: none; border: none; box-shadow: none; padding: 0;">
          <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: 100%;">
            <!-- Ball -->
            <circle cx="32" cy="14" r="7" fill="#F4B400" />
            <!-- Left Player -->
            <circle cx="18" cy="26" r="5" fill="#1E88E5" />
            <path d="M18 31 L12 46 L18 42 L24 46 L18 31Z" fill="#1E88E5" />
            <!-- Right Player -->
            <circle cx="46" cy="26" r="5" fill="#43A047" />
            <path d="M46 31 L40 46 L46 42 L52 46 L46 31Z" fill="#43A047" />
            <!-- Connection -->
            <path d="M23 28 Q32 36 41 28" stroke="#F4B400" stroke-width="3" fill="none" />
          </svg>


        </div>
        <div class="brand-name">TeamUp <small style="font-weight:400;color:var(--text-muted);font-size:13px;margin-left:2px"></small></div>
      </div>
      <?php
      if (isset($error)) {
      ?>
        <div class='alert alert-danger'>
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php
      }
      ?>
      <div class="auth-title">Welcome back</div>
      <div class="auth-subtitle">Sign in to continue to your dashboard.</div>

      <form action="/Team-Up/PHP/AdminPanel/Pages/Components/Login/login.php" method="post" onsubmit="event.stopPropagation();">
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <div class="input-group">
            <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="2" y="3" width="12" height="10" rx="1.5" />
              <path d="M2 5l6 4 6-4" />
            </svg>
            <input type="text" name="email" id="email" class="form-control" placeholder="Enter Username or E-mail" value="<?php echo htmlspecialchars($_POST['email'] ?? ($_COOKIE['remember_admin_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="7" width="10" height="7" rx="1.5" />
              <path d="M5 7V5a3 3 0 016 0v2" />
            </svg>
            <input type="password" name="password" id="password" class="form-control has-toggle" placeholder="••••••••" required>
            <button type="button" id="togglePassword" class="password-toggle" aria-label="Show password" tabindex="-1">
              <svg id="eyeIcon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16">
                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" />
                <circle cx="8" cy="8" r="2" />
              </svg>
            </button>
          </div>
        </div>

        <div class="auth-actions">
          <label class="form-check">
            <input type="checkbox" name="remember" id="rememberMe" checked> Remember me
          </label>
          <a href="/Team-Up/PHP/AdminPanel/Pages/Components/Auth/forgot_password.php">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary" name="btnLogin" style="width:100%;justify-content:center;height:38px">
          Sign in
        </button>
      </form>

      <div class="auth-divider">or continue with</div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
        <button class="btn btn-outline" style="justify-content:center">
          <svg viewBox="0 0 16 16" fill="currentColor" style="color:#4285F4">
            <path d="M14.5 8.16c0-.5-.05-1-.13-1.5H8v2.83h3.65a3.13 3.13 0 01-1.36 2.05v1.7h2.2c1.28-1.18 2.01-2.92 2.01-5.08z" />
            <path d="M8 15c1.83 0 3.36-.6 4.49-1.65l-2.2-1.7c-.6.4-1.38.65-2.29.65-1.76 0-3.25-1.19-3.78-2.78H1.95v1.74A6.74 6.74 0 008 15z" fill="#34A853" />
            <path d="M4.22 9.52a4.07 4.07 0 010-2.59V5.19H1.95a6.74 6.74 0 000 5.62l2.27-1.29z" fill="#FBBC04" />
            <path d="M8 4.46c.99 0 1.88.34 2.59 1l1.94-1.94A6.74 6.74 0 008 1.5a6.74 6.74 0 00-6.05 3.69L4.22 6.93C4.75 5.34 6.24 4.46 8 4.46z" fill="#EA4335" />
          </svg>
          Google
        </button>
        <button class="btn btn-outline" style="justify-content:center">
          <svg viewBox="0 0 16 16" fill="currentColor">
            <path d="M8 0a8 8 0 00-2.53 15.59c.4.07.55-.17.55-.38v-1.34c-2.23.48-2.69-1.07-2.69-1.07-.36-.92-.89-1.16-.89-1.16-.73-.5.05-.49.05-.49.8.06 1.23.83 1.23.83.71 1.23 1.87.87 2.33.67.07-.52.28-.87.5-1.07-1.78-.2-3.64-.89-3.64-3.96 0-.87.31-1.59.83-2.15-.08-.2-.36-1.02.08-2.13 0 0 .67-.21 2.2.82a7.66 7.66 0 014 0c1.53-1.03 2.2-.82 2.2-.82.44 1.11.16 1.93.08 2.13.51.56.83 1.28.83 2.15 0 3.07-1.87 3.76-3.65 3.95.29.25.54.74.54 1.5v2.22c0 .21.14.46.55.38A8 8 0 008 0z" />
          </svg>
          GitHub
        </button>
      </div>

      <div class="auth-footer">
        Don't have an account? <a href="/Team-Up/PHP/LandingPanel/Pages/Login/register.php">Create one</a>
      </div>
    </div>
  </div>

  <script>
    // Password visibility toggle
    (function() {
      var pwdInput = document.getElementById('password');
      var toggleBtn = document.getElementById('togglePassword');
      toggleBtn.addEventListener('click', function() {
        var showing = pwdInput.type === 'text';
        pwdInput.type = showing ? 'password' : 'text';
        toggleBtn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        toggleBtn.classList.toggle('active', !showing);
      });
    })();

    // Remember me: persist only the username/email locally, never the password
    (function() {
      var emailInput = document.getElementById('email');
      var rememberBox = document.getElementById('rememberMe');
      try {
        var savedUser = localStorage.getItem('rememberedUser');
        if (savedUser) {
          emailInput.value = savedUser;
          rememberBox.checked = true;
        }
      } catch (e) {}

      document.querySelector('form').addEventListener('submit', function() {
        try {
          if (rememberBox.checked && emailInput.value.trim() !== '') {
            localStorage.setItem('rememberedUser', emailInput.value.trim());
          } else {
            localStorage.removeItem('rememberedUser');
          }
        } catch (e) {}
      });
    })();
  </script>

</body>

</html>