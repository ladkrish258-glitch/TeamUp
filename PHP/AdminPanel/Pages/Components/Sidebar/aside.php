<?php
// Current page filename, e.g. "form_upload.php"
$current_page = basename($_SERVER['PHP_SELF']);

// Helper: is this href the active page?
function is_active($href) {
    global $current_page;
    return $href === $current_page;
}

// Helper: does this section (array of child hrefs) contain the active page?
function section_active($children) {
    global $current_page;
    return in_array($current_page, $children, true);
}

// Make sure we have the logged-in admin's record (full_name, profile_photo, ...)
// available here even on pages that don't already fetch it before including
// this file (dashboard.php / profile.php already set $admin themselves).
if (!isset($admin) || !is_array($admin)) {
    $admin = ['full_name' => '', 'profile_photo' => ''];
    if (isset($conn) && !empty($_SESSION['admin_id'])) {
        $stmt_side_admin = mysqli_prepare($conn, "SELECT * FROM admins WHERE admin_id = ?");
        if ($stmt_side_admin) {
            mysqli_stmt_bind_param($stmt_side_admin, "i", $_SESSION['admin_id']);
            mysqli_stmt_execute($stmt_side_admin);
            $result_side_admin = mysqli_stmt_get_result($stmt_side_admin);
            $fetched_admin = mysqli_fetch_assoc($result_side_admin);
            mysqli_stmt_close($stmt_side_admin);
            if ($fetched_admin) {
                $admin = $fetched_admin;
            }
        }
    }
}

if (!function_exists('teamup_admin_initials')) {
    function teamup_admin_initials($fullName) {
        $parts = preg_split('/\s+/', trim((string) $fullName));
        $parts = array_filter($parts);
        if (empty($parts)) return '?';
        $first = mb_substr(reset($parts), 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
        return mb_strtoupper($first . $last);
    }
}

$teamup_admin_name     = trim((string) ($admin['full_name'] ?? '')) !== '' ? $admin['full_name'] : 'Admin';
$teamup_admin_photo    = trim((string) ($admin['profile_photo'] ?? ''));
if ($teamup_admin_photo !== '' && !preg_match('#^https?://#i', $teamup_admin_photo) && strpos($teamup_admin_photo, '/') !== 0) {
    $teamup_admin_photo = '/Team-Up/images/Admin/' . basename($teamup_admin_photo);
}
$teamup_admin_initials = teamup_admin_initials($teamup_admin_name);
?>
<aside class="sidebar" aria-label="Primary navigation">
    <div class="sidebar-brand">
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
      <div class="brand-name">TeamUp</div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-group">
        <div class="nav-label">General</div>

        <div class="nav-tree">

        <a class="nav-link<?= is_active('dashboard.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php">
          <!-- Dashboards: layout-dashboard -->
          <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="3" width="7" height="7" rx="1.5" />
              <rect x="14" y="3" width="7" height="4" rx="1.5" />
              <rect x="3" y="14" width="7" height="7" rx="1.5" />
              <rect x="14" y="10" width="7" height="11" rx="1.5" />
            </svg>
          <span class="nav-text">Dashboards</span>
        </a>

      </div>

        <?php $user_children = ['user_data.php','add_user.php','user_posts.php'];
              $user_open = section_active($user_children); ?>
        <div class="nav-tree">
          <button type="button" class="nav-link nav-toggle<?= $user_open ? ' active' : '' ?>" aria-expanded="<?= $user_open ? 'true' : 'false' ?>">
            <!-- User: user -->
            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="8" r="4" />
              <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
            <span class="nav-text">User</span>
            <svg class="nav-chev" width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M6 4l4 4-4 4" />
            </svg>
          </button>
          <div class="nav-sub<?= $user_open ? ' open' : '' ?>">
            <div class="nav-sub-inner">
              <a class="nav-sublink<?= is_active('user_data.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/User/user_data.php">User's</a>
              <!-- <a class="nav-sublink<?= is_active('add_user.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/User/add_user.php">Add User</a> -->
              <a class="nav-sublink<?= is_active('user_posts.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/User/user_posts.php">Posts</a>
            </div>
          </div>
        </div>

        <?php $sports_children = ['sports_data.php','add_sports.php'];
              $sports_open = section_active($sports_children); ?>
        <div class="nav-tree">
          <button type="button" class="nav-link nav-toggle<?= $sports_open ? ' active' : '' ?>" aria-expanded="<?= $sports_open ? 'true' : 'false' ?>">
            <!-- Sports: trophy -->
            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M7 4h10v5a5 5 0 01-10 0V4z" />
              <path d="M7 5H4a2 2 0 002 4M17 5h3a2 2 0 01-2 4" />
              <path d="M12 14v3M8 21h8" />
            </svg>
            <span class="nav-text">Sports</span>
            <svg class="nav-chev" width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M6 4l4 4-4 4" />
            </svg>
          </button>
          <div class="nav-sub<?= $sports_open ? ' open' : '' ?>">
            <div class="nav-sub-inner">
              <a class="nav-sublink<?= is_active('sports_data.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/Grounds/sports_data.php">Sports Data</a>
            </div>
          </div>
        </div>

        <?php $matches_children = ['match_data.php','add_match.php','match_participant.php'];
              $matches_open = section_active($matches_children); ?>
        <div class="nav-tree">
          <button type="button" class="nav-link nav-toggle<?= $matches_open ? ' active' : '' ?>" aria-expanded="<?= $matches_open ? 'true' : 'false' ?>">
            <!-- Matches: swords -->
            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M4 4l7 7M20 4l-7 7" />
              <path d="M4 4l0 3M4 4l3 0M20 4l0 3M20 4l-3 0" />
              <path d="M13 13l7 7M13 13l-7 7" />
              <path d="M4 20l0-3M4 20l3 0M20 20l0-3M20 20l-3 0" />
              <circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none" />
            </svg>
            <span class="nav-text">Matches</span>
            <svg class="nav-chev" width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M6 4l4 4-4 4" />
            </svg>
          </button>
          <div class="nav-sub<?= $matches_open ? ' open' : '' ?>">
            <div class="nav-sub-inner">
              <a class="nav-sublink<?= is_active('match_data.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/Match/match_data.php">Matches Data</a>
              <!-- <a class="nav-sublink<?= is_active('add_match.php') ? ' active' : '' ?>" href="add_match.php">Add Match</a> -->
              <a class="nav-sublink<?= is_active('match_participant.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/Match/match_participant.php">Match Participants</a>
            </div>
          </div>
        </div>

        <a class="nav-link<?= is_active('grounds_data.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/Grounds/grounds_data.php">
          <!-- Grounds: map-pin -->
          <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 21s-7-6.5-7-11a7 7 0 0114 0c0 4.5-7 11-7 11z" />
            <circle cx="12" cy="10" r="2.5" />
          </svg>
          <span class="nav-text">Grounds</span>
        </a>

        <div class="nav-tree">
            <a class="nav-link<?= is_active('user_ratings.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/User/user_ratings.php">
              <!-- Ratings: star -->
              <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                <path d="M12 2.5l2.9 6 6.6.6-5 4.4 1.5 6.5L12 16.8 6 20l1.5-6.5-5-4.4 6.6-.6z" />
              </svg>
              <span class="nav-text">Ratings</span>
            </a>
          </div>
        </div>
        
      <div class="nav-group">
        <div class="nav-label">Admin</div>
        <a class="nav-link<?= is_active('profile.php') ? ' active' : '' ?>" href="/Team-Up/PHP/AdminPanel/Pages/Profile/profile.php">
          <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          <span class="nav-text">Your profile</span>

        </a>
      </div>

    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar">
          <?php if ($teamup_admin_photo !== ''): ?>
            <img src="<?= htmlspecialchars($teamup_admin_photo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($teamup_admin_name, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block">
          <?php else: ?>
            <?= htmlspecialchars($teamup_admin_initials, ENT_QUOTES, 'UTF-8') ?>
          <?php endif; ?>
          <span class="online"></span>
        </div>
        <div class="sidebar-user-info">
          <div class="name"><?= htmlspecialchars($teamup_admin_name, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="role">Admin</div>
        </div>
        <button class="more-btn" aria-label="More options">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
            <circle cx="8" cy="3" r="1.2" />
            <circle cx="8" cy="8" r="1.2" />
            <circle cx="8" cy="13" r="1.2" />
          </svg>
        </button>
      </div>
    </div>
  </aside>