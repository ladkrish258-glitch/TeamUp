<?php
$page = 'login';
$pageTitle = 'Log in';

require_once dirname(__DIR__) . '/Components/Connection/db.php';
require_once dirname(__DIR__, 3) . '/UserPanel/Pages/Components/Auth/user_remember_me.php';

ensure_user_session_started();

// Already logged in or has a valid remember-me cookie? Send them straight to UserPanel!
if (!empty($_SESSION['user_id']) || attempt_user_remember_login($pdo)) {
    header('Location: ../../../UserPanel/Pages/Home/home.php');
    exit;
}

$errors = [];
$savedIdentifier = $_COOKIE['remember_user_identifier'] ?? '';
$identifier = $savedIdentifier;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    $remember   = !empty($_POST['remember']);

    if ($identifier === '') { $errors[] = 'Please enter your username or email.'; }
    if ($password === '')   { $errors[] = 'Please enter your password.'; }

    if (empty($errors)) {
        if (is_numeric($identifier)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR user_id = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier, $identifier]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $isValid = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $isValid = true;
            } elseif (hash('sha256', $password) === $user['password']) {
                $isValid = true;
            } elseif ($password === $user['password']) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            $errors[] = 'That username/email or password is incorrect.';
        } elseif (isset($user['status']) && $user['status'] !== '' && in_array(strtolower($user['status']), ['inactive', 'unactive', 'blocked'])) {
            $errors[] = 'This account is inactive. Please contact support.';
        } else {
            // Success — regenerate session id and log in.
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'] ?? $user['email'];

            // Handle Remember Me cookie & persistent session
            if ($remember) {
                issue_user_remember_token($pdo, (int)$user['user_id']);
                setcookie('remember_user_identifier', $identifier, time() + (86400 * 30), '/', '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
            } else {
                clear_user_remember_token($pdo);
                setcookie('remember_user_identifier', '', time() - 3600, '/');
            }

            header('Location: ../../../UserPanel/Pages/Home/home.php');
            exit;
        }
    }
}

require_once dirname(__DIR__) . '/Components/Header/header.php';
?>

<section class="py-12 pb-6">
  <div class="max-w-[1160px] mx-auto px-6">
    <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Welcome back</span>
    <h1 class="font-serif font-semibold text-4xl sm:text-5xl text-pine-dark mt-2 mb-3">Log in.</h1>
    <p class="text-ink-soft text-lg m-0">Pick up where you left off — find matches, grounds, and neighbors near you.</p>
  </div>
</section>

<section class="pt-6 pb-20">
  <div class="max-w-[560px] mx-auto px-6">

    <div class="bg-white border border-line rounded-[18px] shadow-xl px-8 pt-9 pb-7">

      <div class="text-center mb-6">
        <div class="w-[60px] h-[60px] mx-auto mb-4 flex items-center justify-center rounded-full bg-[#FBEAE3] text-brick" aria-hidden="true">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="10.5" width="14" height="10" rx="2.2" stroke="currentColor" stroke-width="1.6"/>
            <path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <circle cx="12" cy="15" r="1.4" fill="currentColor"/>
            <path d="M12 16.4v1.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <h2 class="font-serif text-[28px] font-bold tracking-tight text-pine-dark m-0 mb-1.5">Log in</h2>
        <p class="m-0 text-sm text-[#7a7a7a]">Enter your username or email to continue</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="bg-[#FBEAE3] border border-brick text-brick px-4 py-3 rounded-[10px] mb-5 text-sm">
          <?php foreach ($errors as $err): ?>
            <div>&bull; <?php echo htmlspecialchars($err); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="form-grid flex flex-col" method="post" action="../Login/login.php" novalidate>
        <div class="field mb-[18px]">
          <label for="identifier" class="block font-semibold text-[13px] text-pine-dark mb-1.5">Username or email</label>
          <input type="text" id="identifier" name="identifier" placeholder="jamie or you@example.com"
                 value="<?php echo htmlspecialchars($identifier); ?>" required autofocus
                 class="w-full box-border px-3.5 py-3 border-[1.5px] border-line rounded-[10px] text-[15px] outline-none focus:border-brick focus:ring-4 focus:ring-brick/10 [&.invalid]:border-brick transition-all">
          <div class="field-error hidden [&.show]:block text-brick text-xs mt-1" id="identifier-error"></div>
        </div>

        <div class="field mb-[18px]">
          <label for="password" class="block font-semibold text-[13px] text-pine-dark mb-1.5">Password</label>
          <input type="password" id="password" name="password" placeholder="Your password" required
                 class="w-full box-border px-3.5 py-3 border-[1.5px] border-line rounded-[10px] text-[15px] outline-none focus:border-brick focus:ring-4 focus:ring-brick/10 [&.invalid]:border-brick transition-all">
          <div class="field-error hidden [&.show]:block text-brick text-xs mt-1" id="password-error"></div>
        </div>

        <div class="flex items-center justify-between mb-[18px]">
          <label class="flex items-center gap-2 text-sm text-[#555] cursor-pointer select-none">
            <input type="checkbox" name="remember" id="rememberMe" checked class="accent-pine w-4 h-4 rounded cursor-pointer">
            <span>Remember me</span>
          </label>
          <a href="#" class="text-xs text-brick font-semibold no-underline hover:underline">Forgot password?</a>
        </div>

        <button type="submit" class="w-full py-3.5 px-4 rounded-[10px] font-bold text-[15px] mt-1.5 bg-pine hover:bg-pine-dark text-paper border-0 cursor-pointer hover:-translate-y-0.5 hover:shadow-lg transition-all">Log in</button>
        <p class="text-center text-[13px] mt-4 text-[#7a7a7a] mb-0">Don't have an account? <a href="../Login/register.php" class="text-brick font-semibold no-underline hover:underline">Create one</a>.</p>
      </form>

    </div>

  </div>
</section>

<script>
  const loginForm = document.querySelector('form.form-grid');
  const identifierInput = document.getElementById('identifier');
  const passwordInput = document.getElementById('password');

  function clearFieldError(input, errorEl) {
    input.classList.remove('invalid');
    errorEl.classList.remove('show');
    errorEl.textContent = '';
  }

  function setFieldError(input, errorEl, message) {
    input.classList.add('invalid');
    errorEl.textContent = message;
    errorEl.classList.add('show');
  }

  [ [identifierInput, 'identifier-error'], [passwordInput, 'password-error'] ].forEach(function(pair) {
    const input = pair[0];
    const errorEl = document.getElementById(pair[1]);
    input.addEventListener('input', function() {
      clearFieldError(input, errorEl);
    });
  });

  loginForm.addEventListener('submit', function(e) {
    let valid = true;

    if (!identifierInput.value.trim()) {
      setFieldError(identifierInput, document.getElementById('identifier-error'), 'Please enter your username or email.');
      valid = false;
    }
    if (!passwordInput.value) {
      setFieldError(passwordInput, document.getElementById('password-error'), 'Please enter your password.');
      valid = false;
    }

    if (!valid) e.preventDefault();
  });
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>