<?php
require_once dirname(__DIR__) . '/Connection/db.php';
require_once dirname(__DIR__) . '/Auth/user_remember_me.php';

ensure_user_session_started();

// Prevent browser caching so users cannot see authenticated pages via the back button after logging out
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Protect user panel pages - check session, and if missing, check remember-me cookie
if (empty($_SESSION['user_id'])) {
  if (!attempt_user_remember_login($pdo)) {
    header("Location: ../../../LandingPanel/Pages/Login/login.php");
    exit;
  }
}

// Fetch user data for the layout
$stmt = $pdo->prepare("SELECT full_name, username FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
  clear_user_remember_token($pdo);
  session_unset();
  session_destroy();
  header("Location: ../../../LandingPanel/Pages/Login/login.php");
  exit;
}
$userInitials = strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1));
$feedWidthClass = $feedMaxWidth ?? 'max-w-[680px]';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - TeamUp' : 'TeamUp'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            'nd-green': '#1f8a55',
            'nd-green-hover': '#176840',
          },
          fontFamily: {
            sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
          }
        }
      }
    };

    (function() {
      const savedTheme = localStorage.getItem('theme') || 'default';
      if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
      }
    })();

    function setTheme(theme) {
      if (theme === 'dark') {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
      } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'default');
      }
      updateThemeIcon(theme);
    }

    function toggleTheme() {
      const current = localStorage.getItem('theme') || 'default';
      setTheme(current === 'dark' ? 'default' : 'dark');
    }

    function updateThemeIcon(theme) {
      const sunIcon = document.getElementById('theme-icon-sun');
      const moonIcon = document.getElementById('theme-icon-moon');
      if (!sunIcon || !moonIcon) return;
      if (theme === 'dark') {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
      } else {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
      }
    }

    window.addEventListener('DOMContentLoaded', function() {
      const saved = localStorage.getItem('theme') || 'default';
      updateThemeIcon(saved);
    });
  </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 font-sans m-0 p-0 antialiased">

  <!-- TOP NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 h-[60px] bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-[1000]">
    <a href="../Home/home.php" class="flex items-center gap-2 font-bold text-xl text-nd-green no-underline">
      <svg class="w-8 h-8" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32" cy="14" r="7" fill="#F4B400" />
        <circle cx="18" cy="26" r="5" fill="#1E88E5" />
        <path d="M18 31 L12 46 L18 42 L24 46 L18 31Z" fill="#1E88E5" />
        <circle cx="46" cy="26" r="5" fill="#43A047" />
        <path d="M46 31 L40 46 L46 42 L52 46 L46 31Z" fill="#43A047" />
        <path d="M23 28 Q32 36 41 28" stroke="#F4B400" stroke-width="3" fill="none" />
      </svg>
      <span>TeamUp</span>
    </a>

    <div class="hidden sm:block flex-1 max-w-[400px] mx-6 relative">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 fill-gray-500 dark:fill-gray-400" viewBox="0 0 24 24">
        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
      </svg>
      <input type="text" placeholder="Search neighbors, sports, or matches..."
             class="w-full py-2.5 pr-4 pl-10 rounded-full border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 text-sm outline-none focus:border-nd-green focus:bg-white dark:focus:bg-gray-800 transition-colors">
    </div>

    <div class="flex items-center gap-4">
      <button type="button" id="theme-btn" onclick="toggleTheme()" title="Toggle theme"
              class="w-10 h-10 rounded-full flex items-center justify-center cursor-pointer bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
        <svg id="theme-icon-sun" class="w-5 h-5 fill-gray-900 dark:fill-gray-50" viewBox="0 0 24 24">
          <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06z" />
        </svg>
        <svg id="theme-icon-moon" class="w-5 h-5 fill-gray-900 dark:fill-gray-50 hidden" viewBox="0 0 24 24">
          <path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z" />
        </svg>
      </button>
      <a href="../Profile/profile.php" class="w-10 h-10 rounded-full bg-nd-green text-white flex items-center justify-center font-semibold text-base no-underline hover:bg-nd-green-hover transition-colors">
        <?= $userInitials ?>
      </a>
    </div>
  </nav>

  <!-- LEFT SIDEBAR (Fixed Left-Docked) -->
  <aside class="hidden md:flex flex-col fixed top-[60px] left-0 w-[240px] h-[calc(100vh-60px)] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 px-3.5 py-5 overflow-y-auto z-[900] box-border">
    <?php
      $navBase = 'flex items-center gap-3 px-4 py-3 rounded-lg font-medium mb-1 no-underline transition-colors';
      $navActive = 'bg-[#e8f3ec] dark:bg-emerald-950/60 text-nd-green dark:text-emerald-400';
      $navIdle = 'text-gray-900 dark:text-gray-100 hover:bg-[#e8f3ec] dark:hover:bg-gray-700/60 hover:text-nd-green dark:hover:text-emerald-400';
    ?>
    <a href="../Home/home.php" class="<?= $navBase ?> <?= (($page ?? '') === 'home') ? $navActive : $navIdle ?>">
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" /></svg>
      <span>Home</span>
    </a>
    <a href="../Discover/directory.php" class="<?= $navBase ?> <?= (($page ?? '') === 'directory') ? $navActive : $navIdle ?>">
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" /></svg>
      <span>Neighbors</span>
    </a>
    <a href="../Post/post.php" class="<?= $navBase ?> <?= (($page ?? '') === 'post') ? $navActive : $navIdle ?>">
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 11h-5v5h-2v-5H6v-2h5V7h2v5h5v2z" /></svg>
      <span>Post</span>
    </a>
    <a href="../Profile/profile.php" class="<?= $navBase ?> <?= (($page ?? '') === 'profile') ? $navActive : $navIdle ?>">
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
      <span>Profile</span>
    </a>
    <a href="../Settings/settings.php" class="<?= $navBase ?> <?= (($page ?? '') === 'settings') ? $navActive : $navIdle ?>">
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .43-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.49-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" /></svg>
      <span>Settings</span>
    </a>

    <div class="mt-auto">
      <a href="../Login/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 no-underline transition-colors">
        <svg class="w-6 h-6 fill-red-500" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" /></svg>
        <span>Log Out</span>
      </a>
    </div>
  </aside>

  <!-- MAIN CONTAINER -->
  <div class="md:ml-[240px] mt-[80px] mb-8 px-4 md:px-7">
    <div class="w-full <?= htmlspecialchars($feedWidthClass) ?> mx-auto flex flex-col gap-4">
