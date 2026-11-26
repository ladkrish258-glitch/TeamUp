<?php
  require_once dirname(__DIR__) . '/Components/Auth/auth.php';

  $conn=mysqli_connect("localhost", "root", "", "teamup") or die("Not connected");

  // Fetch the logged-in admin's own record for the sidebar/topbar avatar
  $stmt_admin = mysqli_prepare($conn, "SELECT * FROM admins WHERE admin_id = ?");
  mysqli_stmt_bind_param($stmt_admin, "i", $_SESSION['admin_id']);
  mysqli_stmt_execute($stmt_admin);
  $result_admin = mysqli_stmt_get_result($stmt_admin);
  $admin = mysqli_fetch_assoc($result_admin);
  mysqli_stmt_close($stmt_admin);

  if (!$admin) {
    // Session pointed at an admin that no longer exists — force re-login
    session_destroy();
    header("Location: /Team-Up/PHP/AdminPanel/Pages/Components/Login/login.php");
    exit();
  }

  if (!empty($admin['profile_photo']) && !preg_match('#^https?://#i', $admin['profile_photo']) && strpos($admin['profile_photo'], '/') !== 0) {
    $admin['profile_photo'] = '/Team-Up/images/Admin/' . basename($admin['profile_photo']);
  }

  $sql_users="SELECT COUNT(*) AS total FROM users";
  $count_users=mysqli_query($conn, $sql_users);
  $users_count=mysqli_fetch_assoc($count_users);
  $users_count = $users_count['total'];

  $sql_user_post="SELECT COUNT(*) AS total FROM user_posts";
  $count_user_post=mysqli_query($conn, $sql_user_post);
  $user_post_count=mysqli_fetch_assoc($count_user_post);
  $user_post_count = $user_post_count['total'];

  $sql_sports="SELECT COUNT(*) AS total FROM sports";
  $count_sports=mysqli_query($conn, $sql_sports);
  $users_sports=mysqli_fetch_assoc($count_sports);
  $users_sports = $users_sports['total'];

  $sql_match="SELECT COUNT(*) AS total FROM matches";
  $count_match=mysqli_query($conn, $sql_match);
  $match_count=mysqli_fetch_assoc($count_match);
  $match_count = $match_count['total'];

  $sql_match_participant="SELECT COUNT(*) AS total FROM match_participants";
  $count_match_participant=mysqli_query($conn, $sql_match_participant);
  $match_participant_count=mysqli_fetch_assoc($count_match_participant);
  $match_participant_count = $match_participant_count['total'];

  $sql_gorund="SELECT COUNT(*) AS total FROM grounds";
  $count_ground=mysqli_query($conn, $sql_gorund);
  $gorund_count=mysqli_fetch_assoc($count_ground);
  $gorund_count = $gorund_count['total'];

  $sql_rating="SELECT COUNT(*) AS total FROM ratings";
  $count_rating=mysqli_query($conn, $sql_rating);
  $rating_count=mysqli_fetch_assoc($count_rating);
  $rating_count = $rating_count['total'];

  // Detailed breakdowns for stat cards
  $new_users_30d = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")))[0] ?? 0);
  $active_users_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE status = 'Active'")))[0] ?? 0);
  $inactive_users_count = max(0, $users_count - $active_users_count);
  $active_users_pct = $users_count > 0 ? round(($active_users_count / $users_count) * 100) : 0;

  $media_posts_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM user_posts WHERE photo_url IS NOT NULL AND TRIM(photo_url) != ''")))[0] ?? 0);
  $new_posts_30d = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM user_posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")))[0] ?? 0);
  $text_posts_count = max(0, $user_post_count - $media_posts_count);
  $media_posts_pct = $user_post_count > 0 ? round(($media_posts_count / $user_post_count) * 100) : 0;

  $active_sports_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT sports_id) FROM matches")))[0] ?? 0);
  $top_sport_row = mysqli_fetch_row(mysqli_query($conn, "SELECT s.sport_name FROM sports s JOIN matches m ON s.sport_id = m.sports_id GROUP BY s.sport_id ORDER BY COUNT(*) DESC LIMIT 1"));
  $top_sport_name = $top_sport_row[0] ?? 'N/A';
  $active_sports_pct = $users_sports > 0 ? round(($active_sports_count / $users_sports) * 100) : 0;

  $match_open_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM matches WHERE match_status = 'Open'")))[0] ?? 0);
  $match_full_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM matches WHERE match_status = 'Full'")))[0] ?? 0);
  $match_completed_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM matches WHERE match_status = 'Completed'")))[0] ?? 0);
  $match_cancelled_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM matches WHERE match_status = 'Cancelled'")))[0] ?? 0);
  $match_open_pct = $match_count > 0 ? round(($match_open_count / $match_count) * 100) : 0;

  $unique_players_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) FROM match_participants")))[0] ?? 0);
  $avg_players_per_match = $match_count > 0 ? round($match_participant_count / $match_count, 1) : 0;
  $unique_players_pct = $match_participant_count > 0 ? round(($unique_players_count / $match_participant_count) * 100) : 0;

  $grounds_avail_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM grounds WHERE status = 'Available'")))[0] ?? 0);
  $grounds_unavail_count = max(0, $gorund_count - $grounds_avail_count);
  $grounds_cities_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT city) FROM grounds WHERE city IS NOT NULL AND city != ''")))[0] ?? 0);
  $grounds_avail_pct = $gorund_count > 0 ? round(($grounds_avail_count / $gorund_count) * 100) : 0;

  $rating_avg = floatval((mysqli_fetch_row(mysqli_query($conn, "SELECT ROUND(AVG(rating), 1) FROM ratings")))[0] ?? 0.0);
  $high_ratings_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ratings WHERE rating >= 4.0")))[0] ?? 0);
  $public_ratings_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ratings WHERE visibility = 'Public'")))[0] ?? 0);
  $rating_pct = round(($rating_avg / 5.0) * 100);

  $admins_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admins")))[0] ?? 0);
  $active_admins_count = intval((mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admins WHERE status = 'Active'")))[0] ?? 0);
  $inactive_admins_count = max(0, $admins_count - $active_admins_count);
  $admins_pct = $admins_count > 0 ? round(($active_admins_count / $admins_count) * 100) : 0;


  $sql_recent_user="SELECT * FROM users ORDER BY created_at DESC LIMIT 8";
  $result_recent_user=mysqli_query($conn, $sql_recent_user);
  $recent_user=mysqli_fetch_all($result_recent_user, MYSQLI_ASSOC);

  // Matches by status (for the donut chart)
  $sql_match_status = "SELECT match_status, COUNT(*) AS total FROM matches GROUP BY match_status";
  $result_match_status = mysqli_query($conn, $sql_match_status);
  $match_status_rows = mysqli_fetch_all($result_match_status, MYSQLI_ASSOC);

  // New user signups over the last 7 days (for the line chart)
  $sql_signups = "
    SELECT DATE(created_at) AS d, COUNT(*) AS total
    FROM users
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
  ";
  $result_signups = mysqli_query($conn, $sql_signups);
  $signup_rows = mysqli_fetch_all($result_signups, MYSQLI_ASSOC);
  $signups_by_day = [];
  foreach ($signup_rows as $r) {
    $signups_by_day[$r['d']] = (int) $r['total'];
  }
  $signup_labels = [];
  $signup_values = [];
  for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i day"));
    $signup_labels[] = date('D', strtotime($day));
    $signup_values[] = $signups_by_day[$day] ?? 0;
  }

  // Combined real activity feed: matches created, matches joined, ratings given, friend requests sent
  $sql_activity = "
    (SELECT 'match_created' AS type, u.full_name AS actor, m.title AS detail, m.created_at AS event_time
       FROM matches m JOIN users u ON m.created_by = u.user_id)
    UNION ALL
    (SELECT 'joined_match' AS type, u.full_name AS actor, mt.title AS detail, mp.joined_at AS event_time
       FROM match_participants mp
       JOIN users u ON mp.user_id = u.user_id
       JOIN matches mt ON mp.match_id = mt.match_id)
    UNION ALL
    (SELECT 'rated' AS type, u.full_name AS actor, CONCAT(r.rating, ' star rating') AS detail, r.created_at AS event_time
       FROM ratings r JOIN users u ON r.user_id = u.user_id)
    UNION ALL
    (SELECT 'friend_request' AS type, su.full_name AS actor, ru.full_name AS detail, fr.created_at AS event_time
       FROM friend_requests fr
       JOIN users su ON fr.sender_id = su.user_id
       JOIN users ru ON fr.receiver_id = ru.user_id)
    ORDER BY event_time DESC
    LIMIT 8
  ";
  $result_activity = mysqli_query($conn, $sql_activity);
  $activity_rows = $result_activity ? mysqli_fetch_all($result_activity, MYSQLI_ASSOC) : [];

  function get_initials($full_name) {
      $full_name = trim($full_name);
      if ($full_name === '') {
          return '?';
      }
      $parts = preg_split('/\s+/', $full_name, -1, PREG_SPLIT_NO_EMPTY);
      if (count($parts) === 1) {
          return mb_strtoupper(mb_substr($parts[0], 0, 1));
      }
      $first = mb_substr($parts[0], 0, 1);
      $last  = mb_substr($parts[count($parts) - 1], 0, 1);
      return mb_strtoupper($first . $last);
  }

  function time_ago($datetime) {
      $diff = time() - strtotime($datetime);
      if ($diff < 60) return 'just now';
      if ($diff < 3600) return floor($diff / 60) . ' min ago';
      if ($diff < 86400) return floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
      if ($diff < 604800) return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
      return date('M j, Y', strtotime($datetime));
  }

  // Human-readable label + avatar color per activity type
  $activity_meta = [
    'match_created'   => ['label' => ' created a match',        'gradient' => 'linear-gradient(135deg,var(--primary),var(--primary-dk))'],
    'joined_match'    => ['label' => ' joined a match',         'gradient' => 'linear-gradient(135deg,var(--blue),#0550a0)'],
    'rated'           => ['label' => ' left a',                 'gradient' => 'linear-gradient(135deg,var(--yellow),#c97f00)'],
    'friend_request'  => ['label' => ' sent a friend request to', 'gradient' => 'linear-gradient(135deg,var(--purple),#8a2da0)'],
  ];

  // Top sports by number of matches played
  $sql_top_sports = "
    SELECT s.sport_name, COUNT(m.match_id) AS total
    FROM matches m
    JOIN sports s ON m.sports_id = s.sport_id
    GROUP BY s.sport_id, s.sport_name
    ORDER BY total DESC
    LIMIT 6
  ";
  $result_top_sports = mysqli_query($conn, $sql_top_sports);
  $top_sports_rows = $result_top_sports ? mysqli_fetch_all($result_top_sports, MYSQLI_ASSOC) : [];

  // Ratings distribution (1–5 stars)
  $sql_rating_dist = "SELECT ROUND(rating) AS star, COUNT(*) AS total FROM ratings GROUP BY ROUND(rating)";
  $result_rating_dist = mysqli_query($conn, $sql_rating_dist);
  $rating_dist_rows = $result_rating_dist ? mysqli_fetch_all($result_rating_dist, MYSQLI_ASSOC) : [];
  $rating_dist_by_star = [];
  foreach ($rating_dist_rows as $r) {
    $rating_dist_by_star[(int) $r['star']] = (int) $r['total'];
  }
  $rating_dist_values = [];
  for ($s = 1; $s <= 5; $s++) {
    $rating_dist_values[] = $rating_dist_by_star[$s] ?? 0;
  }

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | TeamUp 2026 v4</title>
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
  <meta name="description" content="TeamUp admin dashboard — overview of users, matches, grounds, sports, and ratings.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="TeamUp Dashboard">
  <meta property="og:description" content="TeamUp admin dashboard — overview of users, matches, grounds, sports, and ratings.">
  <meta property="og:image" content="/Team-Up/images/android-chrome-512x512.svg">
  <meta property="og:site_name" content="TeamUp v4">
  <meta name="twitter:card" content="summary_large_image">
  <script>
    window.CURRENT_USER = {
      name: <?= json_encode($admin['full_name']) ?>,
      role: <?= json_encode($admin['role'] ?? 'Admin') ?>,
      photo: <?= !empty($admin['profile_photo']) ? json_encode($admin['profile_photo']) : 'null' ?>
    };
  </script>
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
  <!-- <script type="module" crossorigin src="/admin/js/rolldown-runtime-DEgBLETi.js"></script> -->
  <!-- <script type="module" crossorigin src="/admin/js/toast-DgCSlJPv.js"></script> -->
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js"></script>
  <?php include_once dirname(__DIR__) . '/Components/Tailwind/head.php'; ?>
  <style>
    .table th,
    .table td {
      white-space: nowrap;
    }

    .avatar-initials {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #1ABB9C;
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .chart-container {
      width: 100%;
      height: 260px;
    }
  </style>
</head>

<body data-shell="admin" data-page="dashboard" data-breadcrumb="Home > Dashboard">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include_once dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <div class="page-pretitle">Overview</div>
            <h1 class="page-title">Dashboard</h1>
          </div>
          <div class="page-actions">

          </div>
        </div>
      </div>

      <!-- ── 3x3 STATS GRID (ALL 9 TABLES) ── -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" style="margin-bottom:16px">
        
        <!-- CARD 1: USERS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon teal">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Users</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $users_count; ?></div>
                </div>
              </div>
              <span class="status status-green shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                <?php echo $active_users_count; ?> Active
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-teal-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $active_users_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Active: <strong class="text-[var(--text)]"><?php echo $active_users_count; ?></strong></span>
                <span>&bull;</span>
                <span>Inactive: <strong class="text-[var(--text)]"><?php echo $inactive_users_count; ?></strong></span>
                <span>&bull;</span>
                <span>30d: <strong class="text-emerald-500">+<?php echo $new_users_30d; ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/User/user_data.php" class="text-teal-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Manage &rarr;
              </a>
            </div>
          </div>
        </div>

        <!-- CARD 2: POSTS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon blue">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <polyline points="21 15 16 10 5 21" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Posts</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $user_post_count; ?></div>
                </div>
              </div>
              <span class="status status-blue shrink-0">
                <?php echo $media_posts_count; ?> with Media
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-sky-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $media_posts_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Photos: <strong class="text-[var(--text)]"><?php echo $media_posts_count; ?></strong></span>
                <span>&bull;</span>
                <span>Text: <strong class="text-[var(--text)]"><?php echo $text_posts_count; ?></strong></span>
                <span>&bull;</span>
                <span>30d: <strong class="text-sky-400">+<?php echo $new_posts_30d; ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/User/user_posts.php" class="text-sky-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Feed &rarr;
              </a>
            </div>
          </div>
        </div>

        <!-- CARD 3: SPORTS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon yellow">
                  <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M7 4h10v5a5 5 0 01-10 0V4z" />
                    <path d="M7 5H4a2 2 0 002 4M17 5h3a2 2 0 01-2 4" />
                    <path d="M12 14v3M8 21h8" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Sports</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $users_sports; ?></div>
                </div>
              </div>
              <span class="status status-yellow shrink-0">
                <?php echo $active_sports_count; ?> in Matches
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $active_sports_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Active: <strong class="text-[var(--text)]"><?php echo $active_sports_count; ?></strong></span>
                <span>&bull;</span>
                <span>Top: <strong class="text-[var(--text)]"><?php echo htmlspecialchars($top_sport_name); ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/Grounds/sports_data.php" class="text-amber-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Explore &rarr;
              </a>
            </div>
          </div>
        </div>

        <!-- CARD 4: MATCHES -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon green">
                  <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4l7 7M20 4l-7 7" />
                    <path d="M4 4l0 3M4 4l3 0M20 4l0 3M20 4l-3 0" />
                    <path d="M13 13l7 7M13 13l-7 7" />
                    <path d="M4 20l0-3M4 20l3 0M20 20l0-3M20 20l-3 0" />
                    <circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Matches</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $match_count; ?></div>
                </div>
              </div>
              <span class="status status-green shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                <?php echo $match_open_count; ?> Open
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $match_open_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Open: <strong class="text-emerald-400"><?php echo $match_open_count; ?></strong></span>
                <span>&bull;</span>
                <span>Full: <strong class="text-amber-400"><?php echo $match_full_count; ?></strong></span>
                <span>&bull;</span>
                <span>Done: <strong class="text-sky-400"><?php echo $match_completed_count; ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/Match/match_data.php" class="text-emerald-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Manage &rarr;
              </a>
            </div>
          </div>
        </div>

        <!-- CARD 5: MATCH PARTICIPANTS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon purple">
                  <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Participants</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $match_participant_count; ?></div>
                </div>
              </div>
              <span class="status status-blue shrink-0">
                <?php echo $unique_players_count; ?> Players
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-purple-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $unique_players_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Unique: <strong class="text-[var(--text)]"><?php echo $unique_players_count; ?></strong></span>
                <span>&bull;</span>
                <span>Avg/Match: <strong class="text-[var(--text)]"><?php echo $avg_players_per_match; ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/Match/match_participant.php" class="text-purple-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Roster &rarr;
              </a>
            </div>
          </div>
        </div>

        <!-- CARD 6: GROUNDS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon red">
                  <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 21s-7-6.5-7-11a7 7 0 0114 0c0 4.5-7 11-7 11z" />
                    <circle cx="12" cy="10" r="2.5" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Grounds</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $gorund_count; ?></div>
                </div>
              </div>
              <span class="status status-green shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                <?php echo $grounds_avail_count; ?> Available
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-rose-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $grounds_avail_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Avail: <strong class="text-emerald-400"><?php echo $grounds_avail_count; ?></strong></span>
                <span>&bull;</span>
                <span>Unavail: <strong class="text-red-400"><?php echo $grounds_unavail_count; ?></strong></span>
                <span>&bull;</span>
                <span>Cities: <strong class="text-[var(--text)]"><?php echo $grounds_cities_count; ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/Grounds/grounds_data.php" class="text-rose-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Manage &rarr;
              </a>
            </div>
          </div>
        </div>

        <!-- CARD 7: RATINGS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon yellow">
                  <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                    <path d="M12 2.5l2.9 6 6.6.6-5 4.4 1.5 6.5L12 16.8 6 20l1.5-6.5-5-4.4 6.6-.6z" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Total Ratings</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $rating_count; ?></div>
                </div>
              </div>
              <span class="status status-yellow shrink-0">
                &#9733; <?php echo number_format($rating_avg, 1); ?> / 5.0
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-amber-400 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $rating_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Avg: <strong class="text-amber-400"><?php echo number_format($rating_avg, 1); ?> ★</strong></span>
                <span>&bull;</span>
                <span>High: <strong class="text-[var(--text)]"><?php echo $high_ratings_count; ?></strong></span>
                <span>&bull;</span>
                <span>Public: <strong class="text-[var(--text)]"><?php echo $public_ratings_count; ?></strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/User/user_ratings.php" class="text-amber-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Reviews &rarr;
              </a>
            </div>
          </div>
        </div>


        <!-- CARD 9: ADMINS -->
        <div class="card hover:border-primary/40 transition-colors">
          <div class="p-5 flex flex-col justify-between h-full gap-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3.5">
                <div class="stat-icon blue">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <circle cx="12" cy="11" r="3" />
                  </svg>
                </div>
                <div>
                  <div class="stat-label">Platform Admins</div>
                  <div class="stat-value text-2xl font-bold mt-0.5"><?php echo $admins_count; ?></div>
                </div>
              </div>
              <span class="status status-green shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                <?php echo $active_admins_count; ?> Active
              </span>
            </div>

            <div>
              <div class="w-full bg-[var(--border)] rounded-full h-1.5 overflow-hidden">
                <div class="bg-sky-500 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo max(5, $admins_pct); ?>%"></div>
              </div>
            </div>

            <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-secondary)]">
              <div class="flex items-center gap-2 flex-wrap">
                <span>Active: <strong class="text-emerald-400"><?php echo $active_admins_count; ?></strong></span>
                <span>&bull;</span>
                <span>Role: <strong class="text-sky-400">Admin</strong></span>
              </div>
              <a href="/Team-Up/PHP/AdminPanel/Pages/Profile/profile.php" class="text-sky-400 hover:underline font-medium inline-flex items-center gap-0.5 shrink-0">
                Profile &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>
      
      <!-- ── CHART ROW (real data) ── -->
      <div class="row col-2" style="margin-bottom:16px">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Matches by Status</div>
          </div>
          <div class="card-body">
            <div id="chart-match-status" class="chart-container"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-title">New Signups — Last 7 Days</div>
          </div>
          <div class="card-body">
            <div id="chart-signups" class="chart-container"></div>
          </div>
        </div>
      </div>

      <div class="row col-2" style="margin-bottom:16px">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Top Sports by Matches Played</div>
          </div>
          <div class="card-body">
            <div id="chart-top-sports" class="chart-container"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-title">Ratings Distribution</div>
          </div>
          <div class="card-body">
            <div id="chart-rating-dist" class="chart-container"></div>
          </div>
        </div>
      </div>

      <!-- ── USERS + ACTIVITY ROW ── -->

      <!-- Recent users (real data from `users` table) -->
      <div class="row col-6-2">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Recent Users</div>
            <div class="card-options">
              <a href="/Team-Up/PHP/AdminPanel/Pages/User/user_data.php" class="btn btn-sm">View all</a>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>User Id</th>
                    <th>Profile Photo</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_user as $row) { ?>
                    <tr>
                      <td class="cell-mono"><?php echo $row['user_id']; ?></td>
                      <td>
                        <?php if (!empty($row['profile_photo'])) { ?>
                          <img src="/Team-Up/images/User/Profile/<?php echo htmlspecialchars(basename($row['profile_photo']), ENT_QUOTES, 'UTF-8'); ?>"
                               alt="Profile"
                               style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                        <?php } else { ?>
                          <div class="avatar-initials" aria-hidden="true"><?php echo htmlspecialchars(get_initials($row['full_name']), ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php } ?>
                      </td>
                      <td class="cell-strong"><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                      <td>
                        <?php
                          $statusClass = ($row['status'] === 'Active') ? 'status-green' : 'status-red';
                        ?>
                        <span class="status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status'] ?? 'Unknown'); ?></span>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">Recent Activity</div>
            <div class="card-options">
              <button class="card-opt-btn" aria-label="Card options"><svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M3 7h8" />
                </svg></button>
            </div>
          </div>
          <div class="card-body" style="padding:8px 16px">
            <ul class="activity-list">
              <?php if (empty($activity_rows)) { ?>
                <li class="activity-item">
                  <div class="activity-body" style="color:var(--text-muted)">No recent activity yet.</div>
                </li>
              <?php } ?>
              <?php foreach ($activity_rows as $item) {
                $meta = $activity_meta[$item['type']] ?? ['label' => '', 'gradient' => 'linear-gradient(135deg,var(--primary),var(--primary-dk))'];
                $actor = htmlspecialchars($item['actor'] ?? 'Someone', ENT_QUOTES, 'UTF-8');
                $detail = htmlspecialchars($item['detail'] ?? '', ENT_QUOTES, 'UTF-8');
                $initials = htmlspecialchars(get_initials($item['actor'] ?? ''), ENT_QUOTES, 'UTF-8');

                switch ($item['type']) {
                  case 'match_created':
                    $body = "<strong>$actor</strong> created a match &mdash; <strong>$detail</strong>";
                    break;
                  case 'joined_match':
                    $body = "<strong>$actor</strong> joined <strong>$detail</strong>";
                    break;
                  case 'rated':
                    $body = "<strong>$actor</strong> left a $detail";
                    break;
                  case 'friend_request':
                    $body = "<strong>$actor</strong> sent a friend request to <strong>$detail</strong>";
                    break;
                  default:
                    $body = "<strong>$actor</strong> $detail";
                }
              ?>
                <li class="activity-item">
                  <div class="activity-avatar" style="background:<?php echo $meta['gradient']; ?>"><?php echo $initials; ?></div>
                  <div>
                    <div class="activity-body"><?php echo $body; ?></div>
                    <div class="activity-time"><?php echo time_ago($item['event_time']); ?></div>
                  </div>
                </li>
              <?php } ?>
            </ul>
          </div>
        </div>

      </div>
    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
  <script>
    (function() {
      function isDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
      }

      function colors() {
        var dark = isDark();
        return {
          text: dark ? '#94a3b8' : '#64748b',
          textHeading: dark ? '#f1f5f9' : '#0f172a',
          grid: dark ? '#334155' : '#e2e8f0',
          tooltipBg: dark ? '#1e293b' : '#ffffff',
          tooltipBorder: dark ? '#334155' : '#e2e8f0',
          tooltipText: dark ? '#f8fafc' : '#0f172a',
          primary: '#1ABB9C',
          primaryAreaTop: 'rgba(26, 187, 156, 0.28)',
          primaryAreaBottom: 'rgba(26, 187, 156, 0.00)'
        };
      }

      function getCommonTooltip() {
        var c = colors();
        var dark = isDark();
        return {
          backgroundColor: c.tooltipBg,
          borderColor: c.tooltipBorder,
          borderWidth: 1,
          padding: [8, 12],
          textStyle: {
            color: c.tooltipText,
            fontSize: 12,
            fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
          },
          extraCssText: 'box-shadow: 0 10px 15px -3px rgba(0,0,0,' + (dark ? '0.45' : '0.1') + '), 0 4px 6px -4px rgba(0,0,0,' + (dark ? '0.3' : '0.05') + '); border-radius: 8px;'
        };
      }

      var matchStatusData = <?php echo json_encode($match_status_rows); ?>;
      var signupLabels = <?php echo json_encode($signup_labels); ?>;
      var signupValues = <?php echo json_encode($signup_values); ?>;
      var topSportsData = <?php echo json_encode($top_sports_rows); ?>;
      var ratingDistValues = <?php echo json_encode($rating_dist_values); ?>;

      var statusColorMap = {
        'Open': '#1ABB9C',
        'Full': '#3B82F6',
        'Completed': '#22C55E',
        'Cancelled': '#EF4444'
      };

      var chartStatusEl = document.getElementById('chart-match-status');
      var chartSignupsEl = document.getElementById('chart-signups');
      var chartTopSportsEl = document.getElementById('chart-top-sports');
      var chartRatingDistEl = document.getElementById('chart-rating-dist');
      var chartStatus, chartSignups, chartTopSports, chartRatingDist;

      function renderCharts() {
        var c = colors();
        var commonTip = getCommonTooltip();

        if (chartStatusEl) {
          chartStatus && chartStatus.dispose();
          chartStatus = echarts.init(chartStatusEl);
          chartStatus.setOption({
            tooltip: Object.assign({}, commonTip, {
              trigger: 'item',
              formatter: '{b}: <strong>{c}</strong> ({d}%)'
            }),
            legend: { bottom: 0, textStyle: { color: c.text, fontSize: 11 } },
            series: [{
              type: 'pie',
              radius: ['52%', '78%'],
              avoidLabelOverlap: false,
              label: { show: false },
              data: matchStatusData.length ? matchStatusData.map(function(r) {
                return {
                  name: r.match_status || 'Unknown',
                  value: parseInt(r.total, 10),
                  itemStyle: { color: statusColorMap[r.match_status] || '#999' }
                };
              }) : [{ name: 'No matches yet', value: 1, itemStyle: { color: '#999' } }]
            }]
          });
        }

        if (chartSignupsEl) {
          chartSignups && chartSignups.dispose();
          chartSignups = echarts.init(chartSignupsEl);
          chartSignups.setOption({
            tooltip: Object.assign({}, commonTip, {
              trigger: 'axis',
              axisPointer: {
                type: 'line',
                lineStyle: { color: c.grid, width: 1, type: 'dashed' }
              },
              formatter: function(params) {
                if (!params || !params.length) return '';
                var p = params[0];
                var val = p.value !== undefined ? p.value : 0;
                return '<div style="font-size:11px;color:' + c.text + ';margin-bottom:4px">' + p.name + '</div>' +
                       '<div style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px">' +
                       '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + c.primary + '"></span>' +
                       '<span>' + val + ' <span style="font-weight:400;font-size:11px;color:' + c.text + '">signups</span></span>' +
                       '</div>';
              }
            }),
            grid: { left: 32, right: 16, top: 20, bottom: 24, containLabel: true },
            xAxis: {
              type: 'category',
              data: signupLabels,
              axisLine: { lineStyle: { color: c.grid } },
              axisTick: { show: false },
              axisLabel: { color: c.text, fontSize: 11 }
            },
            yAxis: {
              type: 'value',
              minInterval: 1,
              min: 0,
              max: function(v) { return Math.max(v.max + 1, 4); },
              splitLine: { lineStyle: { color: c.grid, type: [4, 3] } },
              axisLabel: { color: c.text, fontSize: 11 },
              axisLine: { show: false },
              axisTick: { show: false }
            },
            series: [{
              type: 'line',
              smooth: 0.35,
              showSymbol: false,
              symbol: 'circle',
              symbolSize: 7,
              data: signupValues,
              lineStyle: { color: c.primary, width: 2.5 },
              itemStyle: { color: c.primary, borderColor: isDark() ? '#1e293b' : '#ffffff', borderWidth: 2 },
              areaStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                  { offset: 0, color: c.primaryAreaTop },
                  { offset: 1, color: c.primaryAreaBottom }
                ])
              }
            }]
          });
        }

        if (chartTopSportsEl) {
          chartTopSports && chartTopSports.dispose();
          chartTopSports = echarts.init(chartTopSportsEl);
          var sportNames = topSportsData.length ? topSportsData.map(function(r) { return r.sport_name; }) : ['No matches yet'];
          var sportTotals = topSportsData.length ? topSportsData.map(function(r) { return parseInt(r.total, 10); }) : [0];
          chartTopSports.setOption({
            tooltip: Object.assign({}, commonTip, {
              trigger: 'axis',
              axisPointer: {
                type: 'shadow',
                shadowStyle: { color: isDark() ? 'rgba(255,255,255,0.03)' : 'rgba(0,0,0,0.03)' }
              },
              formatter: function(params) {
                if (!params || !params.length) return '';
                var p = params[0];
                return '<div style="font-size:11px;color:' + c.text + ';margin-bottom:4px">' + p.name + '</div>' +
                       '<div style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px">' +
                       '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + c.primary + '"></span>' +
                       '<span>' + p.value + ' <span style="font-weight:400;font-size:11px;color:' + c.text + '">matches</span></span>' +
                       '</div>';
              }
            }),
            grid: { left: 90, right: 16, top: 10, bottom: 10, containLabel: false },
            xAxis: {
              type: 'value',
              minInterval: 1,
              splitLine: { lineStyle: { color: c.grid, type: [4, 3] } },
              axisLabel: { color: c.text, fontSize: 11 },
              axisLine: { show: false },
              axisTick: { show: false }
            },
            yAxis: {
              type: 'category',
              data: sportNames,
              inverse: true,
              axisLine: { lineStyle: { color: c.grid } },
              axisTick: { show: false },
              axisLabel: { color: c.text, fontSize: 11 }
            },
            series: [{
              type: 'bar',
              data: sportTotals,
              barWidth: '45%',
              itemStyle: { color: c.primary, borderRadius: [0, 4, 4, 0] }
            }]
          });
        }

        if (chartRatingDistEl) {
          chartRatingDist && chartRatingDist.dispose();
          chartRatingDist = echarts.init(chartRatingDistEl);
          chartRatingDist.setOption({
            tooltip: Object.assign({}, commonTip, {
              trigger: 'axis',
              axisPointer: {
                type: 'shadow',
                shadowStyle: { color: isDark() ? 'rgba(255,255,255,0.03)' : 'rgba(0,0,0,0.03)' }
              },
              formatter: function(params) {
                if (!params || !params.length) return '';
                var p = params[0];
                return '<div style="font-size:11px;color:' + c.text + ';margin-bottom:4px">' + p.name + '</div>' +
                       '<div style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px">' +
                       '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#F59E0B"></span>' +
                       '<span>' + p.value + ' <span style="font-weight:400;font-size:11px;color:' + c.text + '">reviews</span></span>' +
                       '</div>';
              }
            }),
            grid: { left: 32, right: 16, top: 20, bottom: 24, containLabel: true },
            xAxis: {
              type: 'category',
              data: ['1 ★', '2 ★', '3 ★', '4 ★', '5 ★'],
              axisLine: { lineStyle: { color: c.grid } },
              axisTick: { show: false },
              axisLabel: { color: c.text, fontSize: 11 }
            },
            yAxis: {
              type: 'value',
              minInterval: 1,
              splitLine: { lineStyle: { color: c.grid, type: [4, 3] } },
              axisLabel: { color: c.text, fontSize: 11 },
              axisLine: { show: false },
              axisTick: { show: false }
            },
            series: [{
              type: 'bar',
              data: ratingDistValues,
              barWidth: '42%',
              itemStyle: { color: '#F59E0B', borderRadius: [4, 4, 0, 0] }
            }]
          });
        }
      }

      renderCharts();

      window.addEventListener('resize', function() {
        chartStatus && chartStatus.resize();
        chartSignups && chartSignups.resize();
        chartTopSports && chartTopSports.resize();
        chartRatingDist && chartRatingDist.resize();
      });

      new MutationObserver(function(mutations) {
        if (mutations.some(function(m) { return m.attributeName === 'data-theme'; })) {
          renderCharts();
        }
      }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    })();
  </script>

</body>

</html>