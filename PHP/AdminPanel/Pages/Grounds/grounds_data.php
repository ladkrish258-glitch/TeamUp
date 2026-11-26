<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';
$conn = mysqli_connect("localhost", "root", "", "teamup");

// Pagination setup
$allowedLimits = [25, 50, 75, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowedLimits) ? intval($_GET['limit']) : 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$countResult = mysqli_query($conn, "select count(*) as total from grounds");
$countRow = mysqli_fetch_assoc($countResult);
$count = $countRow['total'];
$totalPages = max(1, ceil($count / $limit));

$sql = "select * from grounds order by ground_id desc limit $offset, $limit";
$qu = mysqli_query($conn, $sql);

function renderGroundRows($qu)
{
  ob_start();
  while ($row = mysqli_fetch_array($qu)) {
    echo '<tr data-status="' . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . '">';
    // echo "<td><input type='checkbox' aria-label='Select row'></td>";
    echo "<td>" . $row["ground_id"] . "</td>";
    echo "<td>" . $row["ground_name"] . "</td>";
    echo "<td>" . $row["city"] . "</td>";
    echo "<td>" . $row["description"] . "</td>";
    echo "<td>" . $row["status"] . "</td>";

    $groundData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
    echo '<td style="text-align:center">';
    echo '<button class="row-opt-btn" aria-label="Row actions" data-id="' . $row['ground_id'] . '" data-profile="' . $groundData . '">';
    echo '<svg viewBox="0 0 16 16" fill="currentColor"><circle cx="3" cy="8" r="1.4" /><circle cx="8" cy="8" r="1.4" /><circle cx="13" cy="8" r="1.4" /></svg>';
    echo '</button></td>';

    echo "</tr>";
  }
  return ob_get_clean();
}

function renderPagination($page, $totalPages, $count, $limit, $label = 'grounds')
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
      Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $count; ?> <?php echo $label; ?>)
    </div>
  </div>
  <div style="display:flex;gap:4px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" class="btn btn-sm page-link" data-page="<?php echo $page - 1; ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $window = 1;
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

// Handle delete
if (isset($_GET['gid'])) {
  $gid = intval($_GET['gid']);
  $del = "delete from grounds where ground_id = " . $gid;
  $dqu = mysqli_query($conn, $del);
  if ($dqu) {
    header("Location: grounds_data.php");
    exit;
  }
}

// Handle AJAX pagination
if (isset($_GET['ajax_page'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'rows' => renderGroundRows($qu),
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
  <title>Ground Data | TeamUp 2026 v4</title>
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
  <meta name="description" content="Ground management — Admin > Grounds. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Ground management">
  <meta property="og:description" content="Ground management — Admin > Grounds. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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

<body data-shell="admin" data-page="grounds_data" data-breadcrumb="Home > Admin > Ground Data">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">Ground Data</h1>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php" style="color: var(--text-secondary);">Dashboard</a>
            <span>></span>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Grounds/grounds_data.php" style="color: var(--text-secondary);">Grounds</a>
          </div>
        </div>
      </div>

      <div class="row col-3" style="margin-bottom:16px">
        <div class="card">
          <div class="stat">
            <div class="stat-icon teal">
              <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 21s-7-6.5-7-11a7 7 0 0114 0c0 4.5-7 11-7 11z" />
                <circle cx="12" cy="10" r="2.5" />
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total grounds</div>
              <div class="stat-value-row"><span class="stat-value"><?php echo $count; ?></span></div>
              <span id="filter-count" class="text-muted" style="white-space:nowrap"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="justify-content: space-between; align-items: center; gap:12px; flex-wrap:wrap;">
          <div class="users-filters">
            <div class="search-box" style="width:240px">
              <svg class="s-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="7" cy="7" r="5" />
                <path d="M11 11l3.5 3.5" />
              </svg>
              <input type="text" id="grounds-search" placeholder="Search by name or city…" aria-label="Search grounds">
            </div>
            <select class="form-control" id="filter-status" style="width:140px;height:32px" aria-label="Filter by status">
              <option value="">All statuses</option>
              <option value="Available">Available</option>
              <option value="Unavailable">Unavailable</option>
            </select>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <!-- <th style="width:40px"><input type="checkbox" id="select-all" aria-label="Select all rows"></th> -->
                  <th>Ground Id</th>
                  <th>Ground Name</th>
                  <th>City</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th style="width: 60px; text-align:center">Actions</th>
                </tr>
              </thead>
              <tbody id="grounds-rows">
                <?php echo renderGroundRows($qu); ?>
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

  <!-- Modal overlay -->
  <!-- <div id="form-modal" class="modal-backdrop" style="display: none; position: fixed; top:0; left:0; width:100vw; height:100vh; background: rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div class="card" style="width: 480px; max-width: 90%; background: var(--bg-surface); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color);">
      <div class="card-header" style="padding: 0 0 12px 0; margin-bottom: 16px; border-bottom: 1px solid var(--border-color-light); display:flex; justify-content:space-between; align-items:center;">
        <h3 id="modal-title" class="card-title">Add Ground</h3>
        <button onclick="closeModal()" style="background: none; border:none; color: var(--text-muted); cursor:pointer; font-size:20px;">&times;</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" id="form-action" value="add">
        <input type="hidden" name="ground_id" id="form-ground-id">

        <div class="form-group" style="margin-bottom: 12px;">
          <label class="form-label">Ground Name</label>
          <input type="text" name="ground_name" id="form-ground-name" class="form-control" required placeholder="Green Valley Ground">
        </div>

        <div class="form-group" style="margin-bottom: 12px;">
          <label class="form-label">Sport</label>
          <select name="sport_id" id="form-sport-id" class="form-control" required></select>
        </div>

        <div class="form-group" style="margin-bottom: 12px;">
          <label class="form-label">Address</label>
          <textarea name="address" id="form-address" class="form-control" rows="2" placeholder="Street address"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" name="city" id="form-city" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Pincode</label>
            <input type="text" name="pincode" id="form-pincode" class="form-control">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 12px;">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" id="form-contact-number" class="form-control">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div class="form-group">
            <label class="form-label">Opening Time</label>
            <input type="time" name="opening_time" id="form-opening-time" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Closing Time</label>
            <input type="time" name="closing_time" id="form-closing-time" class="form-control">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 12px;">
          <label class="form-label">Description</label>
          <textarea name="description" id="form-description" class="form-control" rows="3" placeholder="Facilities, surface type, etc."></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label">Status</label>
          <select name="status" id="form-status" class="form-control">
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
          </select>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:8px;">
          <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div> -->

  <script type="module">
    import {
      r as showModal
    } from '/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js';

    function closeModal() {
      document.getElementById('form-modal').style.display = 'none';
    }
    window.closeModal = closeModal;

    function openEditModal(groundId, profile) {
      document.getElementById('form-modal').style.display = 'flex';
      document.getElementById('modal-title').textContent = 'Edit Ground';
      document.getElementById('form-action').value = 'edit';
      document.getElementById('form-ground-id').value = groundId;

      if (profile) {
        document.getElementById('form-ground-name').value = profile.ground_name || '';
        document.getElementById('form-address').value = profile.address || '';
        document.getElementById('form-city').value = profile.city || '';
        document.getElementById('form-pincode').value = profile.pincode || '';
        document.getElementById('form-contact-number').value = profile.contact_number || '';
        document.getElementById('form-opening-time').value = profile.opening_time || '';
        document.getElementById('form-closing-time').value = profile.closing_time || '';
        document.getElementById('form-description').value = profile.description || '';
        document.getElementById('form-status').value = profile.status || 'Available';
        const sportSel = document.getElementById('form-sport-id');
        if (sportSel) sportSel.value = profile.sport_id || '';
      }
    }
    window.openEditModal = openEditModal;

    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('grounds-search');
      const statusFilter = document.getElementById('filter-status');
      const tbody = document.getElementById('grounds-rows');
      const paginationFooter = document.getElementById('pagination-footer');
      const selectAllCheckbox = document.getElementById('select-all');

      let currentLimit = <?php echo (int)$limit; ?>;

      function getRowCheckboxes() {
        return tbody.querySelectorAll('tr input[type="checkbox"]');
      }

      function filterRows() {
        const query = searchInput ? searchInput.value.toLowerCase() : '';
        const status = statusFilter ? statusFilter.value.toLowerCase() : '';
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          const rowStatus = (row.dataset.status || '').toLowerCase();
          const matchesSearch = text.includes(query);
          const matchesStatus = status === '' || rowStatus === status;
          const isVisible = matchesSearch && matchesStatus;
          row.style.display = isVisible ? '' : 'none';
          if (isVisible) visibleCount++;
        });

        const countEl = document.getElementById('filter-count');
        if (countEl) {
          countEl.textContent = visibleCount + (visibleCount === 1 ? ' row found' : ' rows found');
        }
      }

      function bindPageSizeSelect() {
        const sel = document.getElementById('page-size-select');
        if (!sel) return;
        sel.addEventListener('change', () => {
          currentLimit = sel.value;
          loadPage(1);
        });
      }

      async function loadPage(page, pushState = true) {
        paginationFooter.style.opacity = '0.5';
        try {
          const res = await fetch('grounds_data.php?ajax_page=1&page=' + page + '&limit=' + currentLimit);
          const data = await res.json();

          tbody.innerHTML = data.rows;
          paginationFooter.innerHTML = data.pagination;
          bindPageSizeSelect();
          currentLimit = data.limit;

          // Reset select-all state since the new rows are unchecked
          if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
          }

          if (searchInput) searchInput.value = '';
          if (statusFilter) statusFilter.value = '';

          if (pushState) {
            const url = new URL(window.location);
            url.searchParams.set('page', data.page);
            url.searchParams.set('limit', data.limit);
            history.pushState({
              page: data.page,
              limit: data.limit
            }, '', url);
          }

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

      if (searchInput) searchInput.addEventListener('input', filterRows);
      if (statusFilter) statusFilter.addEventListener('change', filterRows);

      bindPageSizeSelect();

      // --- Select-all checkbox wiring ---

      // Header checkbox toggles every row checkbox
      if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
          getRowCheckboxes().forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
          });
        });
      }

      // Delegated listener: keeps working even after AJAX pagination replaces tbody's innerHTML
      tbody.addEventListener('change', (e) => {
        if (e.target.matches('input[type="checkbox"]')) {
          const rowCheckboxes = getRowCheckboxes();
          const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;

          if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedCount === rowCheckboxes.length && rowCheckboxes.length > 0;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
          }
        }
      });

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

        const gid = btn.dataset.id;
        const menu = document.createElement('div');
        menu._triggerBtn = btn;
        menu.className = 'menu-popover row-menu-popover';
        menu.setAttribute('role', 'menu');
        menu.innerHTML = `
          <button type="button" class="menu-item row-view-opt" data-id="${gid}" role="menuitem">View Details</button>
        `;

        document.body.appendChild(menu);

        const rect = btn.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 6) + 'px';
        menu.style.left = Math.max(8, rect.right - menu.offsetWidth) + 'px';
      });

      document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.row-edit-opt');
        if (!editBtn) return;
        const gid = editBtn.dataset.id;
        const sourceBtn = document.querySelector(`.row-opt-btn[data-id="${gid}"]`);
        const profile = sourceBtn ? JSON.parse(sourceBtn.dataset.profile) : null;
        openEditModal(gid, profile);
        document.querySelectorAll('.row-menu-popover').forEach(m => m.remove());
      });

      document.addEventListener('click', (e) => {
        const delBtn = e.target.closest('.row-delete-opt');
        if (!delBtn) return;
        if (confirm('Are you sure you want to delete this ground?')) {
          window.location.href = 'grounds_data.php?gid=' + delBtn.dataset.id;
        }
      });

      document.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.row-view-opt');
        if (!viewBtn) return;

        const gid = viewBtn.dataset.id;
        const sourceBtn = document.querySelector(`.row-opt-btn[data-id="${gid}"]`);
        if (!sourceBtn) return;

        const p = JSON.parse(sourceBtn.dataset.profile);

        const v = (val) => (val && val !== 'null' && val !== 'undefined') ? val : '—';
        const body = `
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;font-size:13px">
            <div><div class="stat-label">Ground Name</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.ground_name)}</div></div>
            <div><div class="stat-label">Sport Id</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.sport_id)}</div></div>
            <div style="grid-column:1 / -1"><div class="stat-label">Address</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.address)}</div></div>
            <div><div class="stat-label">City</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.city)}</div></div>
            <div><div class="stat-label">Pincode</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.pincode)}</div></div>
            <div><div class="stat-label">Contact</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.contact_number)}</div></div>
            <div><div class="stat-label">Timing</div><div style="color:var(--text);font-weight:500;margin-top:2px">${v(p.opening_time)} &mdash; ${v(p.closing_time)}</div></div>
            <div style="grid-column:1 / -1"><div class="stat-label">Description</div><div style="color:var(--text);margin-top:2px">${v(p.description)}</div></div>
            <div><div class="stat-label">Status</div><div style="margin-top:2px"><span class="status ${p.status === 'Available' ? 'status-green' : 'status-red'}">${v(p.status)}</span></div></div>
            <div><div class="stat-label">Created At</div><div style="color:var(--text-secondary);font-size:12px;margin-top:2px">${v(p.created_at)}</div></div>
          </div>
        `;

        showModal({
          title: p.ground_name || 'Ground Details',
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