<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';
$conn = mysqli_connect("localhost", "root", "", "teamup");

// Pagination setup
$allowedLimits = [25, 50, 75, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowedLimits) ? intval($_GET['limit']) : 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$countResult = mysqli_query($conn, "select count(*) as total from users");
$countRow = mysqli_fetch_assoc($countResult);
$count = $countRow['total'];
$totalPages = max(1, ceil($count / $limit));

$sql = "select * from users order by user_id desc limit $offset, $limit";
$qu = mysqli_query($conn, $sql);

function renderUserRows($qu)
{
  ob_start();
  while ($row = mysqli_fetch_array($qu)) {
    echo "<tr>";
    // echo "<td><input type='checkbox' aria-label='Select row'></td>";
    echo "<td>" . $row["user_id"] . "</td>";

    if (!empty($row["profile_photo"])) {
      $imagePath = "/Team-Up/images/User/Profile/" . $row["profile_photo"];

      echo "<td>
                  <img src='$imagePath'
                      alt='Profile'
                      style='width:40px;
                              height:40px;
                              border-radius:50%;
                              object-fit:cover;'>
                </td>";
    } else {
      $initials = htmlspecialchars(get_initials($row["full_name"]), ENT_QUOTES, 'UTF-8');
      echo "<td><div class='avatar-initials' aria-hidden='true'>$initials</div></td>";
    }

    echo "<td>" . $row["full_name"] . "</td>";
    echo "<td>" . $row["username"] . "</td>";
    echo "<td>" . $row["email"] . "</td>";

    $checkedAttr = ($row['status'] == "Active") ? "checked" : "";
    echo "<td>
                <label class='switch'>
                  <input type='checkbox' class='status-switch' data-id='" . $row['user_id'] . "' " . $checkedAttr . ">
                  <div class='slider'></div>
                  <div class='slider-card'>
                    <div class='slider-card-face slider-card-front'></div>
                    <div class='slider-card-face slider-card-back'></div>
                  </div>
                </label>
              </td>";

    $profileData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
    echo '<td style="text-align:center">';
    echo '<button class="row-opt-btn" aria-label="Row actions" data-id="' . $row['user_id'] . '" data-profile="' . $profileData . '">';
    echo '<svg viewBox="0 0 16 16" fill="currentColor"><circle cx="3" cy="8" r="1.4" /><circle cx="8" cy="8" r="1.4" /><circle cx="13" cy="8" r="1.4" /></svg>';
    echo '</button></td>';

    echo "</tr>";
  }
  return ob_get_clean();
}

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


function renderPagination($page, $totalPages, $count, $limit)
{
  $allowedLimits = [25, 50, 75, 100, 250, 500];
  ob_start();
  ?>
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <select id="page-size-select" class="form-control" style="width:auto;height:32px" aria-label="Rows per page">
      <?php foreach ($allowedLimits as $opt): ?>
        <option value="<?php echo $opt; ?>" <?php echo ($opt == $limit) ? 'selected' : ''; ?>><?php echo $opt; ?> Records</option>
      <?php endforeach; ?>
    </select>
    <div class="text-muted">
      Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $count; ?> users)
    </div>
  </div>
  <div style="display:flex;gap:4px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" class="btn btn-sm page-link" data-page="<?php echo $page - 1; ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $window = 1; // pages shown on each side of the current page
    $start = max(1, $page - $window);
    $end = min($totalPages, $page + $window);

    if ($start > 1) {
      echo '<a href="?page=1&limit=' . $limit . '" class="btn btn-sm page-link page-num" data-page="1">1</a>';
      if ($start > 2) echo '<span style="padding:0 4px">…</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
      $isActive = ($i == $page);
      $activeClass = $isActive ? 'active' : '';
      $ariaCurrent = $isActive ? 'aria-current="page"' : '';
      echo '<a href="?page=' . $i . '&limit=' . $limit . '" class="btn btn-sm page-link page-num ' . $activeClass . '" data-page="' . $i . '" ' . $ariaCurrent . '>' . $i . '</a>';
    }

    if ($end < $totalPages) {
      if ($end < $totalPages - 1) echo '<span style="padding:0 4px">…</span>';
      echo '<a href="?page=' . $totalPages . '&limit=' . $limit . '" class="btn btn-sm page-link page-num" data-page="' . $totalPages . '">' . $totalPages . '</a>';
    }
    ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" class="btn btn-sm page-link" data-page="<?php echo $page + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
}

if (isset($_GET['uid'])) {
  $uid = $_GET['uid'];
  $photoQuery = mysqli_query($conn, "select profile_photo from users where user_id = " . $uid);
  $photoRow = mysqli_fetch_array($photoQuery);
  $photoToDelete = $photoRow['profile_photo'];

  $del = "delete from users where user_id = " . $uid;
  $dqu = mysqli_query($conn, $del);

  if ($dqu) {
    if (!empty($photoToDelete)) {
      $filePath = dirname(__DIR__, 4) . "/images/User/Profile/" . $photoToDelete;
      if (file_exists($filePath)) {
        unlink($filePath);
      }
    }
    header("Location: /Team-Up/PHP/AdminPanel/Pages/User/user_data.php");
    exit;
  }
}

// Handle AJAX status toggle
if (isset($_GET['ajax_status']) && isset($_GET['status'])) {
  $ajaxUid = intval($_GET['ajax_status']);
  $newStatus = ($_GET['status'] === 'Active') ? 'Active' : 'Inactive';

  $updateSql = "update users set status = ? where user_id = ?";
  $stmt = mysqli_prepare($conn, $updateSql);
  mysqli_stmt_bind_param($stmt, "si", $newStatus, $ajaxUid);

  if (mysqli_stmt_execute($stmt)) {
    echo "success";
  } else {
    echo "error";
  }
  exit;
}

// Handle AJAX pagination
if (isset($_GET['ajax_page'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'rows' => renderUserRows($qu),
    'pagination' => renderPagination($page, $totalPages, $count, $limit),
    'page' => $page,
    'totalPages' => $totalPages,
    'limit' => $limit,
  ]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Data | TeamUp 2026 v4</title>
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
  <meta name="description" content="User management — Admin > Users. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="User management">
  <meta property="og:description" content="User management — Admin > Users. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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
  <!-- <script type="module" crossorigin src="/admin/js/rolldown-runtime-DEgBLETi.js"></script> -->
  <!-- <script type="module" crossorigin src="/admin/js/toast-DgCSlJPv.js"></script> -->
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js"></script>
  <?php include_once dirname(__DIR__) . '/Components/Tailwind/head.php'; ?>
  <style>
    .table th,
    .table td {
      white-space: nowrap;
    }

    .switch {
      --circle-dim: 1.4em;
      font-size: 10px;
      position: relative;
      display: inline-block;
      width: 3.5em;
      height: 2em;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #f5aeae;
      transition: .4s;
      border-radius: 30px;
    }

    .slider-card {
      position: absolute;
      content: "";
      height: var(--circle-dim);
      width: var(--circle-dim);
      border-radius: 20px;
      left: 0.3em;
      bottom: 0.3em;
      transition: .4s;
      pointer-events: none;
    }

    .slider-card-face {
      position: absolute;
      inset: 0;
      backface-visibility: hidden;
      perspective: 1000px;
      border-radius: 50%;
      transition: .4s transform;
    }

    .slider-card-front {
      background-color: #DC3535;
    }

    .slider-card-back {
      background-color: #379237;
      transform: rotateY(180deg);
    }

    input:checked~.slider-card .slider-card-back {
      transform: rotateY(0);
    }

    input:checked~.slider-card .slider-card-front {
      transform: rotateY(-180deg);
    }

    input:checked~.slider-card {
      transform: translateX(1.5em);
    }

    input:checked~.slider {
      background-color: #9ed99c;
    }

    .row-opt-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      padding: 0;
      border: none;
      border-radius: 6px;
      background: transparent;
      color: var(--text-muted, #9aa0a6);
      cursor: pointer;
      transition: background .15s, color .15s;
    }

    .row-opt-btn:hover {
      background: var(--bg-surface-secondary);
      color: var(--text);
    }

    .row-opt-btn svg {
      width: 16px;
      height: 16px;
    }

    .page-link {
      transition: background .15s, color .15s, box-shadow .15s;
    }

    .page-link.active {
      background: #1ABB9C !important;
      border-color: #1ABB9C !important;
      color: #fff !important;
      font-weight: 600;
      pointer-events: none;
    }

    .page-link:focus-visible {
      outline: 2px solid #1ABB9C;
      outline-offset: 2px;
    }

    .avatar-initials {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #1ABB9C;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
    }
  </style>
</head>

<body data-shell="admin" data-page="user_data" data-breadcrumb="Home > Admin > User Data">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">User Data</h1>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php" style="color: var(--text-secondary);">Dashboard</a>
            <span>></span>
            <a style="color: var(--text-secondary);">User</a>
          </div>
          <div class="page-actions">
            <a href="add_user.php" class="btn btn-primary" id="Add-btn">+ Add user</a>
          </div>
        </div>
      </div>

      <div class="row col-3" style="margin-bottom:16px">
        <div class="card">
          <div class="stat">
            <div class="stat-icon teal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="8" r="4" />
                <path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7" />
              </svg></div>
            <div class="stat-content">
              <div class="stat-label">Total users</div>
              <div class="stat-value-row"><span class="stat-value"><?php echo $count; ?></span></div>
              
              <div id="page-row-count" style="font-size:14px;color:#888;"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="gap:12px;flex-wrap:wrap">
          <div class="users-filters">
            <div class="search-box" style="width:240px">
              <svg class="s-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="7" cy="7" r="5" />
                <path d="M11 11l3.5 3.5" />
              </svg>
              <input type="text" id="users-search" placeholder="Search by name or email…" aria-label="Search users">
            </div>
            <select class="form-control" id="filter-status" style="width:140px;height:32px" aria-label="Filter by status">
              <option value="">All statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <!-- <th style="width:40px"><input type="checkbox" id="select-all" aria-label="Select all rows"></th> -->
                  <th>User Id</th>
                  <th>Profile Photo</th>
                  <th>Full Name</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th style="width:60px; text-align:center">Actions</th>
                </tr>
              </thead>
              <tbody id="users-rows">
                <?php echo renderUserRows($qu); ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer" id="pagination-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
          <?php echo renderPagination($page, $totalPages, $count, $limit); ?>
        </div>
      </div>

    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>

  </main>
  <script type="module">
    import {
      r as showModal
    } from '/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js';

    function getInitials(fullName) {
      const parts = (fullName || '').trim().split(/\s+/).filter(Boolean);
      if (parts.length === 0) return '?';
      const first = parts[0].charAt(0);
      const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';
      return (first + last).toUpperCase();
    }

    function initUserData() {
      const searchInput = document.getElementById('users-search');
      const statusFilter = document.getElementById('filter-status');
      const selectAllCb = document.getElementById('select-all');
      const tbody = document.getElementById('users-rows');
      const paginationFooter = document.getElementById('pagination-footer');

      let currentLimit = <?php echo (int)$limit; ?>;

      function filterRows() {
        const query = searchInput ? searchInput.value.toLowerCase() : '';
        const status = statusFilter ? statusFilter.value.toLowerCase() : '';
        const rows = tbody.querySelectorAll('tr');

        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchesSearch = text.includes(query);

            const statusToggle = row.querySelector('.status-switch');
            const rowStatus = statusToggle ? (statusToggle.checked ? 'active' : 'inactive') : '';
            const matchesStatus = status === '' || rowStatus === status;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const pageRowText = document.getElementById("page-row-count");
        if (status === "active" || status === "inactive") {
            pageRowText.innerHTML = "Rows in this page = " + visibleCount;
        } else {
            pageRowText.innerHTML = "Rows in this page = " + rows.length;
        }
      }

      function bindStatusSwitches() {
        tbody.querySelectorAll('.status-switch').forEach(toggle => {
          toggle.addEventListener('change', function() {
            const userId = this.dataset.id;
            const newStatus = this.checked ? 'Active' : 'Inactive';
            const switchEl = this;

            fetch('user_data.php?ajax_status=' + userId + '&status=' + newStatus)
              .then(res => res.text())
              .then(response => {
                if (response.trim() !== 'success') {
                  alert('Failed to update status');
                  switchEl.checked = !switchEl.checked;
                }
              })
              .catch(() => {
                alert('Error updating status');
                switchEl.checked = !switchEl.checked;
              });
          });
        });
      }

      function bindPageSizeSelect() {
        const sel = document.getElementById('page-size-select');
        if (!sel) return;
        sel.addEventListener('change', () => {
          currentLimit = sel.value;
          loadPage(1); // rows-per-page changed, so start back at page 1
        });
      }

      async function loadPage(page, pushState = true) {
        paginationFooter.style.opacity = '0.5';
        try {
          const res = await fetch('user_data.php?ajax_page=1&page=' + page + '&limit=' + currentLimit);
          const data = await res.json();

          tbody.innerHTML = data.rows;
          paginationFooter.innerHTML = data.pagination;
          bindStatusSwitches();
          bindPageSizeSelect();
          currentLimit = data.limit;

          // keep the active search/status filter and reapply it
          // to the newly loaded rows instead of resetting it
          filterRows();

          if (pushState) {
            const url = new URL(window.location);
            url.searchParams.set('page', data.page);
            url.searchParams.set('limit', data.limit);
            history.pushState({
              page: data.page,
              limit: data.limit
            }, '', url);
          }

          // Make sure the active page is visibly marked and keyboard-focusable
          const activeLink = paginationFooter.querySelector('.page-link.active');
          if (activeLink) activeLink.focus({
            preventScroll: true
          });

          tbody.closest('.card').scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
          });
        } catch (e) {
          alert('Failed to load page');
        } finally {
          paginationFooter.style.opacity = '1';
        }
      }

      if (searchInput) {
        searchInput.addEventListener('input', filterRows);
      }

      if (statusFilter) {
        statusFilter.addEventListener('change', filterRows);
      }

      if (selectAllCb) {
        selectAllCb.addEventListener('change', (e) => {
          const isChecked = e.target.checked;
          const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.style.display !== 'none');
          visibleRows.forEach(row => {
            const cb = row.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = isChecked;
          });
        });
      }

      bindStatusSwitches();
      bindPageSizeSelect();

      if (paginationFooter) {
        paginationFooter.addEventListener('click', (e) => {
          const link = e.target.closest('.page-link');
          if (!link) return;
          e.preventDefault();
          const page = link.dataset.page;
          if (!page) return;

          // Instant feedback: mark this link as active right away, don't wait for the fetch
          if (link.classList.contains('page-num')) {
            paginationFooter.querySelectorAll('.page-link').forEach(l => {
              l.classList.remove('active');
              l.removeAttribute('aria-current');
            });
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
          }

          loadPage(page);
        });
      }

      window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 1;
        const limit = params.get('limit');
        if (limit) currentLimit = limit;
        loadPage(page, false);
      });




      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.row-opt-btn');
        const existing = document.querySelector('.row-menu-popover');
        const wasOpenForThisBtn = existing && existing._triggerBtn === btn;

        document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());

        if (!btn || wasOpenForThisBtn) return;
        e.preventDefault();
        e.stopPropagation();

        const uid = btn.dataset.id;
        const menu = document.createElement('div');
        menu._triggerBtn = btn;
        menu.className = 'menu-popover row-menu-popover'; // reuse existing framework styling
        menu.setAttribute('role', 'menu');
        menu.innerHTML = `
        <button type="button" class="menu-item row-view-opt" data-id="${uid}" role="menuitem">View Profile</button>
        <a href="add_user.php?uid=${uid}" class="menu-item" role="menuitem">Edit</a>
        <button type="button" class="menu-item row-delete-opt" data-id="${uid}" role="menuitem">Delete</button>
      `;

        document.body.appendChild(menu); // append to body, NOT inside the table cell

        const rect = btn.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 6) + 'px';
        menu.style.left = Math.max(8, rect.right - menu.offsetWidth) + 'px';
      });

      document.addEventListener('click', (e) => {
        const delBtn = e.target.closest('.row-delete-opt');
        if (!delBtn) return;
        if (confirm('Are you sure you want to delete this user??')) {
          window.location.href = 'user_data.php?uid=' + delBtn.dataset.id;
        }
      });

      document.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.row-view-opt');
        if (!viewBtn) return;

        const uid = viewBtn.dataset.id;
        const sourceBtn = document.querySelector(`.row-opt-btn[data-id="${uid}"]`);
        if (!sourceBtn) return;

        let p = {};
        try {
          p = JSON.parse(sourceBtn.dataset.profile || '{}');
        } catch (err) {}

        const v = (val) => (val && val !== 'null' && val !== 'undefined') ? val : '—';
        const statusBadge = (p.status === 'Active' || p.status === 'active') ?
          '<span class="status status-green">Active</span>' :
          '<span class="status status-red">' + (p.status || 'Inactive') + '</span>';

        const avatarHtml = p.profile_photo ?
          `<img src="/Team-Up/images/User/Profile/${p.profile_photo}" alt="Profile" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">` :
          `<div style="width:64px;height:64px;border-radius:50%;background:#1ABB9C;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:600;">${getInitials(p.full_name)}</div>`;

        const body = `
        <div style="display:flex;gap:16px;align-items:center;padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid var(--border)">
          ${avatarHtml}
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:18px;color:var(--text);margin-bottom:4px">${v(p.full_name)}</div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="color:var(--text-secondary);font-size:13px">@${v(p.username)}</span>
              ${statusBadge}
            </div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;font-size:13px">
          <div><div class="stat-label">Email</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.email)}</div></div>
          <div><div class="stat-label">Mobile</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.mobile)}</div></div>
          <div><div class="stat-label">Gender</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.gender)}</div></div>
          <div><div class="stat-label">Date of Birth</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.dob)}</div></div>
          <div><div class="stat-label">Area</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.area)}</div></div>
          <div><div class="stat-label">City</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.city)}</div></div>
          <div><div class="stat-label">State</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.state)}</div></div>
          <div><div class="stat-label">Country</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.country)}</div></div>
          <div><div class="stat-label">Created At</div><div style="color:var(--text-secondary);font-size:12px;margin-top:2px">${v(p.created_at)}</div></div>
          <div><div class="stat-label">Updated At</div><div style="color:var(--text-secondary);font-size:12px;margin-top:2px">${v(p.updated_at)}</div></div>
        </div>
      `;

        showModal({
          title: 'User Profile',
          body: body,
          size: 'md'
        });

        document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());
      });

      // close menu when clicking anywhere outside it
      document.addEventListener('click', (e) => {
        if (!e.target.closest('.row-menu-popover') && !e.target.closest('.row-opt-btn')) {
          document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());
        }
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initUserData);
    } else {
      initUserData();
    }
  </script>
</body>

</html>