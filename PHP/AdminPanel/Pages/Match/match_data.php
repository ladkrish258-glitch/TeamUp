<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';
$conn = mysqli_connect("localhost", "root", "", "teamup");

// Pagination setup
$allowedLimits = [25, 50, 75, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowedLimits) ? intval($_GET['limit']) : 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Status filter setup (server-side, so it applies across the whole
// table and not just the rows already loaded on the current page)
$allowedStatuses = ['Open', 'Full', 'Completed', 'Cancelled'];
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], $allowedStatuses) ? $_GET['status'] : '';

$whereParts = [];
if ($statusFilter !== '') {
  $statusEscaped = mysqli_real_escape_string($conn, $statusFilter);
  $whereParts[] = "match_status = '$statusEscaped'";
}
$whereClause = count($whereParts) > 0 ? 'where ' . implode(' and ', $whereParts) : '';

$countResult = mysqli_query($conn, "select count(*) as total from matches $whereClause");
$countRow = mysqli_fetch_assoc($countResult);
$count = $countRow['total'];
$totalPages = max(1, ceil($count / $limit));

$sql = "select matches.*, users.username as creator_username from matches left join users on matches.created_by = users.user_id $whereClause order by matches.match_id desc limit $offset, $limit";
$qu = mysqli_query($conn, $sql);

function renderMatchRows($qu)
{
  ob_start();
  while ($row = mysqli_fetch_array($qu)) {
    $statusAttr = htmlspecialchars($row['match_status'], ENT_QUOTES, 'UTF-8');
    $feeText = (float)($row['entry_fee'] ?? 0) > 0 ? '₹' . number_format($row['entry_fee'], 2) : 'Free';
    $minP = (int)($row['min_players'] ?? 0);
    $maxP = (int)($row['max_players'] ?? 0);
    $playersText = $minP > 0 ? "$minP - $maxP" : "$maxP max";

    echo "<tr data-status=\"" . $statusAttr . "\">";
    // echo "<td><input type='checkbox' class='row-checkbox' aria-label='Select row'></td>";
    echo "<td>" . $row["match_id"] . "</td>";
    echo "<td>" . htmlspecialchars($row["creator_username"] ?? 'Unknown', ENT_QUOTES, 'UTF-8') . " (" . $row["created_by"] . ")</td>";
    echo "<td>" . htmlspecialchars($row["title"] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . htmlspecialchars($row["match_date"] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . htmlspecialchars($playersText, ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . htmlspecialchars($feeText, ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . htmlspecialchars($row["match_status"] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";

    $matchData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
    echo '<td style="text-align:center">';
    echo '<button class="row-opt-btn" aria-label="Row actions" data-id="' . $row['match_id'] . '" data-profile="' . $matchData . '">';
    echo '<svg viewBox="0 0 16 16" fill="currentColor"><circle cx="3" cy="8" r="1.4" /><circle cx="8" cy="8" r="1.4" /><circle cx="13" cy="8" r="1.4" /></svg>';
    echo '</button></td>';

    echo "</tr>";
  }
  return ob_get_clean();
}

function renderPagination($page, $totalPages, $count, $limit, $label = 'matches', $statusFilter = '')
{
  $allowedLimits = [25, 50, 75, 100, 250, 500];
  $statusQs = $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '';
  $extraQs = $statusQs;
  ob_start();
  ?>
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <select id="page-size-select" class="form-control" style="width:auto;height:32px" aria-label="Rows per page">
      <?php foreach ($allowedLimits as $opt): ?>
        <option value="<?php echo $opt; ?>" <?php echo ($opt == $limit) ? 'selected' : ''; ?>><?php echo $opt; ?> Records</option>
      <?php endforeach; ?>
    </select>
    <div class="text-muted">
      Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $count; ?> <?php echo $label; ?>)
    </div>
  </div>
  <div style="display:flex;gap:4px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit . $extraQs; ?>" class="btn btn-sm page-link" data-page="<?php echo $page - 1; ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $window = 1;
    $start = max(1, $page - $window);
    $end = min($totalPages, $page + $window);

    if ($start > 1) {
      echo '<a href="?page=1&limit=' . $limit . $extraQs . '" class="btn btn-sm page-link page-num" data-page="1">1</a>';
      if ($start > 2) echo '<span style="padding:0 4px">…</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
      $isActive = ($i == $page);
      $activeClass = $isActive ? 'active' : '';
      $ariaCurrent = $isActive ? 'aria-current="page"' : '';
      echo '<a href="?page=' . $i . '&limit=' . $limit . $extraQs . '" class="btn btn-sm page-link page-num ' . $activeClass . '" data-page="' . $i . '" ' . $ariaCurrent . '>' . $i . '</a>';
    }

    if ($end < $totalPages) {
      if ($end < $totalPages - 1) echo '<span style="padding:0 4px">…</span>';
      echo '<a href="?page=' . $totalPages . '&limit=' . $limit . $extraQs . '" class="btn btn-sm page-link page-num" data-page="' . $totalPages . '">' . $totalPages . '</a>';
    }
    ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit . $extraQs; ?>" class="btn btn-sm page-link" data-page="<?php echo $page + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
}

// Handle delete
if (isset($_GET['mid'])) {
  $mid = intval($_GET['mid']);
  $del = "delete from matches where match_id = " . $mid;
  $dqu = mysqli_query($conn, $del);
  if ($dqu) {
    header("Location: match_data.php");
    exit;
  }
}

// Handle AJAX pagination
if (isset($_GET['ajax_page'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'rows' => renderMatchRows($qu),
    'pagination' => renderPagination($page, $totalPages, $count, $limit, 'matches', $statusFilter, $visibilityFilter),
    'page' => $page,
    'totalPages' => $totalPages,
    'limit' => $limit,
    'status' => $statusFilter,
    'visibility' => $visibilityFilter,
  ]);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Match Data | TeamUp 2026 v4</title>
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
  <meta name="description" content="Match management — Admin > Matches. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Match management">
  <meta property="og:description" content="Match management — Admin > Matches. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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
    .table th, .table td {
      white-space: nowrap;
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
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-primary, #fff);
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
  </style>
</head>

<body data-shell="admin" data-page="match_data" data-breadcrumb="Home > Admin > Match Data">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">Match Data</h1>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php" style="color: var(--text-secondary);">Dashboard</a>
            <span>></span>
            <a style="color: var(--text-secondary);">Matches</a>
          </div>
        </div>
      </div>

      <div class="row col-3" style="margin-bottom:16px">
        <div class="card">
          <div class="stat">
            <div class="stat-icon teal">
              <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4l7 7M20 4l-7 7" />
                <path d="M4 4l0 3M4 4l3 0M20 4l0 3M20 4l-3 0" />
                <path d="M13 13l7 7M13 13l-7 7" />
                <path d="M4 20l0-3M4 20l3 0M20 20l0-3M20 20l-3 0" />
                <circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none" />
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total matches</div>
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
              <input type="text" id="matches-search" placeholder="Search by title…" aria-label="Search matches">
            </div>
            <select class="form-control" id="filter-status" style="width:140px;height:32px" aria-label="Filter by status">
              <option value="" <?php echo ($statusFilter === '') ? 'selected' : ''; ?>>All statuses</option>
              <option value="Open" <?php echo ($statusFilter === 'Open') ? 'selected' : ''; ?>>Open</option>
              <option value="Full" <?php echo ($statusFilter === 'Full') ? 'selected' : ''; ?>>Full</option>
              <option value="Completed" <?php echo ($statusFilter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
              <option value="Cancelled" <?php echo ($statusFilter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <!-- <th style="width:40px"><input type="checkbox" id="select-all" aria-label="Select all rows"></th> -->
                  <th>Match Id</th>
                  <th>Created By (User Id)</th>
                  <th>Title</th>
                  <th>Match Date</th>
                  <th>Players (Min - Max)</th>
                  <th>Entry Fee</th>
                  <th>Match Status</th>
                  <th style="width: 60px; text-align:center">Actions</th>
                </tr>
              </thead>
              <tbody id="matches-rows">
                <?php echo renderMatchRows($qu); ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer" id="pagination-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
          <?php echo renderPagination($page, $totalPages, $count, $limit, 'matches', $statusFilter); ?>
        </div>
      </div>

    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>

  </main>

  <script type="module">
    import {
      r as showModal
    } from '/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js';

    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('matches-search');
      const statusFilter = document.getElementById('filter-status');
      const tbody = document.getElementById('matches-rows');
      const paginationFooter = document.getElementById('pagination-footer');
      const selectAllCheckbox = document.getElementById('select-all');

      let currentLimit = <?php echo (int)$limit; ?>;
      let currentStatus = <?php echo json_encode($statusFilter); ?>;
      let rowCountVisible = false;

      // Search box still filters within the current page only.
      function filterRows() {
        const query = searchInput ? searchInput.value.toLowerCase() : '';
        const rows = tbody.querySelectorAll('tr');

        let visibleCount = 0;

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          const matchesSearch = text.includes(query);

          if (matchesSearch) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        const pageRowText = document.getElementById('page-row-count');
        if (pageRowText && rowCountVisible) {
          pageRowText.innerHTML = "Rows in this page = " + visibleCount;
        }
      }

      // Select-all checkbox: check/uncheck every row checkbox that is
      // currently visible (respects any active search/status filter).
      function bindSelectAll() {
        if (!selectAllCheckbox) return;
        selectAllCheckbox.checked = false;
        selectAllCheckbox.addEventListener('change', () => {
          const rowCheckboxes = tbody.querySelectorAll('tr:not([style*="display: none"]) .row-checkbox');
          rowCheckboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
          });
        });
      }

      // If a row checkbox is unchecked manually, the header checkbox
      // should no longer show as fully checked.
      tbody.addEventListener('change', (e) => {
        if (!e.target.classList.contains('row-checkbox')) return;
        if (!selectAllCheckbox) return;
        const rowCheckboxes = tbody.querySelectorAll('.row-checkbox');
        const checkedCount = tbody.querySelectorAll('.row-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount > 0 && checkedCount === rowCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
      });

      function bindPageSizeSelect() {
        const sel = document.getElementById('page-size-select');
        if (!sel) return;
        sel.addEventListener('change', () => {
          currentLimit = sel.value;
          loadPage(1);
        });
      }

      async function loadPage(page, pushState = true, scrollOnLoad = true) {
        paginationFooter.style.opacity = '0.5';
        try {
          const statusQs = currentStatus ? '&status=' + encodeURIComponent(currentStatus) : '';
          const res = await fetch('match_data.php?ajax_page=1&page=' + page + '&limit=' + currentLimit + statusQs);
          const data = await res.json();

          tbody.innerHTML = data.rows;
          paginationFooter.innerHTML = data.pagination;
          bindPageSizeSelect();
          currentLimit = data.limit;
          currentStatus = data.status || '';
          if (statusFilter) statusFilter.value = currentStatus;

          if (searchInput) searchInput.value = '';
          if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
          }

          // keep the row counter in sync with the freshly loaded page
          filterRows();

          if (pushState) {
            const url = new URL(window.location);
            url.searchParams.set('page', data.page);
            url.searchParams.set('limit', data.limit);
            if (currentStatus) {
              url.searchParams.set('status', currentStatus);
            } else {
              url.searchParams.delete('status');
            }
            if (currentVisibility) {
              url.searchParams.set('visibility', currentVisibility);
            } else {
              url.searchParams.delete('visibility');
            }
            history.pushState({
              page: data.page,
              limit: data.limit,
              status: currentStatus,
              visibility: currentVisibility
            }, '', url);
          }

          const activeLink = paginationFooter.querySelector('.page-link.active');
          if (activeLink) activeLink.focus({
            preventScroll: true
          });

          if (scrollOnLoad) {
            tbody.closest('.card').scrollIntoView({
              behavior: 'smooth',
              block: 'nearest'
            });
          }
        } catch (e) {
          alert('Failed to load page');
        } finally {
          paginationFooter.style.opacity = '1';
        }
      }

      if (searchInput) searchInput.addEventListener('input', filterRows);

      // Status/visibility changes re-query the server (like changing page
      // size does) instead of just hiding/showing already-loaded rows,
      // so matches on other pages are picked up too. Unlike page
      // navigation, this shouldn't scroll the page down to the table.
      if (statusFilter) {
        statusFilter.addEventListener('change', () => {
          currentStatus = statusFilter.value;
          rowCountVisible = true;
          loadPage(1, true, false);
        });
      }

      bindPageSizeSelect();
      bindSelectAll();

      if (paginationFooter) {
        paginationFooter.addEventListener('click', (e) => {
          const link = e.target.closest('.page-link');
          if (!link) return;
          e.preventDefault();
          const page = link.dataset.page;
          if (!page) return;

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
        currentStatus = params.get('status') || '';
        if (statusFilter) statusFilter.value = currentStatus;
        currentVisibility = params.get('visibility') || '';
        if (visibilityFilter) visibilityFilter.value = currentVisibility;
        loadPage(page, false);
      });

      // Three-dot row action popover
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.row-opt-btn');
        const existing = document.querySelector('.row-menu-popover');
        const wasOpenForThisBtn = existing && existing._triggerBtn === btn;

        document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());
        if (!btn || wasOpenForThisBtn) return;
        e.preventDefault();
        e.stopPropagation();

        const mid = btn.dataset.id;
        const menu = document.createElement('div');
        menu._triggerBtn = btn;
        menu.className = 'menu-popover row-menu-popover';
        menu.setAttribute('role', 'menu');
        menu.innerHTML = `
          <button type="button" class="menu-item row-view-opt" data-id="${mid}" role="menuitem">View Details</button>
        `;

        document.body.appendChild(menu);

        const rect = btn.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 6) + 'px';
        menu.style.left = Math.max(8, rect.right - menu.offsetWidth) + 'px';
      });

      document.addEventListener('click', (e) => {
        const delBtn = e.target.closest('.row-delete-opt');
        if (!delBtn) return;
        if (confirm('Are you sure you want to delete this match?')) {
          window.location.href = 'match_data.php?mid=' + delBtn.dataset.id;
        }
      });

      document.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.row-view-opt');
        if (!viewBtn) return;

        const mid = viewBtn.dataset.id;
        const sourceBtn = document.querySelector(`.row-opt-btn[data-id="${mid}"]`);
        if (!sourceBtn) return;

        const p = JSON.parse(sourceBtn.dataset.profile);

        const v = (val) => (val && val !== 'null' && val !== 'undefined') ? val : '—';
        const body = `
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;font-size:13px">
            <div><div class="stat-label">Match Title</div><div style="color:var(--text);font-weight:600;margin-top:2px">${v(p.title)}</div></div>
            <div><div class="stat-label">Sports Id</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.sports_id)}</div></div>
            <div><div class="stat-label">Created By</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.creator_username)}</div></div>
            <div><div class="stat-label">Ground Id</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.ground_id)}</div></div>
            <div><div class="stat-label">Date & Time</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.match_date)} (${v(p.start_time)} &mdash; ${v(p.end_time)})</div></div>
            <div><div class="stat-label">Players</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.min_players)} &mdash; ${v(p.max_players)}</div></div>
            <div><div class="stat-label">Entry Fee</div><div style="color:var(--text);font-weight:500;margin-top:2px">&#8377;${v(p.entry_fee)}</div></div>
            <div><div class="stat-label">Post ID</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.post_id)}</div></div>
            <div><div class="stat-label">Status</div><div style="margin-top:2px"><span class="status ${p.match_status === 'Open' ? 'status-green' : 'status-blue'}">${v(p.match_status)}</span></div></div>
            <div style="grid-column:1 / -1"><div class="stat-label">Description</div><div style="color:var(--text);margin-top:2px">${v(p.description)}</div></div>
          </div>
        `;

        showModal({
          title: p.title || 'Match Details',
          body: body,
          size: 'md'
        });

        document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());
      });

      document.addEventListener('click', (e) => {
        if (!e.target.closest('.row-menu-popover') && !e.target.closest('.row-opt-btn')) {
          document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());
        }
      });
    });
  </script>
</body>

</html>