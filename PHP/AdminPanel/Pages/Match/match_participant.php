<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';
$conn = mysqli_connect("localhost", "root", "", "teamup");

// Pagination setup
$allowedLimits = [25, 50, 75, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowedLimits) ? intval($_GET['limit']) : 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search setup
$searchTerm = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : null;

// Build the shared WHERE clause + params for both the count and data queries
$whereParts = [];
$params = [];
$types = '';

if ($searchTerm !== null) {
  $likeTerm = "%" . $searchTerm . "%";
  $whereParts[] = "(m.title like ? or u.full_name like ?)";
  $params[] = $likeTerm;
  $params[] = $likeTerm;
  $types .= 'ss';
}
$whereClause = count($whereParts) > 0 ? 'where ' . implode(' and ', $whereParts) : '';

$countSql = "select count(*) as total from match_participants mp join matches m on mp.match_id = m.match_id join users u on mp.user_id = u.user_id $whereClause";
if (count($params) > 0) {
  $countStmt = mysqli_prepare($conn, $countSql);
  mysqli_stmt_bind_param($countStmt, $types, ...$params);
  mysqli_stmt_execute($countStmt);
  $countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
} else {
  $countResult = mysqli_query($conn, $countSql);
  $countRow = mysqli_fetch_assoc($countResult);
}
$count = $countRow['total'];
$totalPages = max(1, ceil($count / $limit));

$dataSql = "select mp.*, m.title as match_title, u.full_name from match_participants mp join matches m on mp.match_id = m.match_id join users u on mp.user_id = u.user_id $whereClause order by mp.participant_id desc limit ?, ?";
$dataParams = $params;
$dataTypes = $types . 'ii';
$dataParams[] = $offset;
$dataParams[] = $limit;
$stmt = mysqli_prepare($conn, $dataSql);
mysqli_stmt_bind_param($stmt, $dataTypes, ...$dataParams);
mysqli_stmt_execute($stmt);
$qu = mysqli_stmt_get_result($stmt);

function renderParticipantRows($qu)
{
  ob_start();
  while ($row = mysqli_fetch_array($qu)) {
    echo "<tr>";
    // echo "<td><input type='checkbox' aria-label='Select row'></td>";
    echo "<td>" . $row["participant_id"] . "</td>";
    echo "<td>" . $row["match_title"] . " (" . $row['match_id'] . ")</td>";
    echo "<td>" . $row["full_name"] . " (" . $row['user_id'] . ")</td>";
    echo "<td>" . $row["joined_at"] . "</td>";
    echo "</tr>";
  }
  return ob_get_clean();
}

function renderPagination($page, $totalPages, $count, $limit, $searchTerm = null)
{
  $allowedLimits = [25, 50, 75, 100, 250, 500];
  $extra = $searchTerm !== null ? '&search=' . urlencode($searchTerm) : '';
  ob_start();
  ?>
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <select id="page-size-select" class="form-control" style="width:auto;height:32px" aria-label="Rows per page">
      <?php foreach ($allowedLimits as $opt): ?>
        <option value="<?php echo $opt; ?>" <?php echo ($opt == $limit) ? 'selected' : ''; ?>><?php echo $opt; ?> Records</option>
      <?php endforeach; ?>
    </select>
    <div class="text-muted">
      Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $count; ?> participants)
    </div>
  </div>
  <div style="display:flex;gap:4px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?><?php echo $extra; ?>" class="btn btn-sm page-link" data-page="<?php echo $page - 1; ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $window = 1;
    $start = max(1, $page - $window);
    $end = min($totalPages, $page + $window);

    if ($start > 1) {
      echo '<a href="?page=1&limit=' . $limit . $extra . '" class="btn btn-sm page-link page-num" data-page="1">1</a>';
      if ($start > 2) echo '<span style="padding:0 4px">…</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
      $isActive = ($i == $page);
      $activeClass = $isActive ? 'active' : '';
      $ariaCurrent = $isActive ? 'aria-current="page"' : '';
      echo '<a href="?page=' . $i . '&limit=' . $limit . $extra . '" class="btn btn-sm page-link page-num ' . $activeClass . '" data-page="' . $i . '" ' . $ariaCurrent . '>' . $i . '</a>';
    }

    if ($end < $totalPages) {
      if ($end < $totalPages - 1) echo '<span style="padding:0 4px">…</span>';
      echo '<a href="?page=' . $totalPages . '&limit=' . $limit . $extra . '" class="btn btn-sm page-link page-num" data-page="' . $totalPages . '">' . $totalPages . '</a>';
    }
    ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?><?php echo $extra; ?>" class="btn btn-sm page-link" data-page="<?php echo $page + 1; ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
}

// Handle AJAX add/edit participant (status change)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: application/json');

  $action = $_POST['action'];
  $matchId = intval($_POST['match_id'] ?? 0);
  $userId = intval($_POST['user_id'] ?? 0);

  if ($matchId <= 0 || $userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Valid Match ID and User ID are required']);
    exit;
  }

  if ($action === 'edit' && isset($_POST['participant_id'])) {
    $participantId = intval($_POST['participant_id']);
    $stmt = mysqli_prepare($conn, "update match_participants set match_id = ?, user_id = ? where participant_id = ?");
    mysqli_stmt_bind_param($stmt, 'iii', $matchId, $userId, $participantId);
    $ok = mysqli_stmt_execute($stmt);
  } else {
    $stmt = mysqli_prepare($conn, "insert into match_participants (match_id, user_id, joined_at) values (?, ?, now())");
    mysqli_stmt_bind_param($stmt, 'ii', $matchId, $userId);
    $ok = mysqli_stmt_execute($stmt);
  }

  echo json_encode(['success' => (bool)$ok, 'error' => $ok ? null : mysqli_error($conn)]);
  exit;
}

// Handle AJAX pagination
if (isset($_GET['ajax_page'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'rows' => renderParticipantRows($qu),
    'pagination' => renderPagination($page, $totalPages, $count, $limit, $searchTerm),
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
  <title>Match Participants | TeamUp 2026 v4</title>
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
  <meta name="description" content="Match participants — Database Tables > Match Participants. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Match Participants">
  <meta property="og:description" content="Match participants — Database Tables > Match Participants. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/main-v4-zXfl5ocF.js"></script>
  <?php include_once dirname(__DIR__) . '/Components/Tailwind/head.php'; ?>
  <style>
    .table th,
    .table td {
      white-space: nowrap;
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

<body data-shell="admin" data-page="manage-sports" data-breadcrumb="Home > Database Tables > Match Participants">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>

  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">All Matches Participants</h1>
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
              <div class="stat-label">Total participants</div>
              <div class="stat-value-row"><span class="stat-value"><?php echo $count; ?></span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="justify-content: space-between; align-items: center; gap:12px; flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <div class="search-box" style="width: 240px; margin: 0px;">
              <svg class="s-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="7" cy="7" r="5" />
                <path d="M11 11l3.5 3.5" />
              </svg>
              <input type="text" id="users-search" placeholder="Search by match or user name…" aria-label="Search by match or user name" value="<?php echo $searchTerm !== null ? htmlspecialchars($searchTerm) : ''; ?>">
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <!-- <th style="width: 40px;"><input type="checkbox" id="select-all" aria-label="Select all rows"></th> -->
                  <th>Participant Id</th>
                  <th>Match Title (Match Id)</th>
                  <th>User Name (User Id)</th>
                  <th>Joined At</th>
                </tr>
              </thead>
              <tbody id="users-rows">
                <?php echo renderParticipantRows($qu); ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer" id="pagination-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
          <?php echo renderPagination($page, $totalPages, $count, $limit, $searchTerm); ?>
        </div>
      </div>

    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
  </main>

  <!-- Modal overlay -->
  <div id="form-modal" class="modal-backdrop" style="display: none; position: fixed; top:0; left:0; width:100vw; height:100vh; background: rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div class="card" style="width: 480px; max-width: 90%; background: var(--bg-surface); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color);">
      <div class="card-header" style="padding: 0 0 12px 0; margin-bottom: 16px; border-bottom: 1px solid var(--border-color-light); display:flex; justify-content:space-between; align-items:center;">
        <h3 id="modal-title" class="card-title">Add Participant</h3>
        <button onclick="closeModal()" style="background: none; border:none; color: var(--text-muted); cursor:pointer; font-size:20px;">&times;</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" id="form-action" value="add">
        <input type="hidden" name="participant_id" id="form-participant-id">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
          <div class="form-group">
            <label class="form-label">Match Id</label>
            <input type="number" name="match_id" id="form-match-id" class="form-control" required min="1">
          </div>
          <div class="form-group">
            <label class="form-label">User Id</label>
            <input type="number" name="user_id" id="form-user-id" class="form-control" required min="1">
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:8px;">
          <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script type="module">
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('users-search');
      const selectAllCb = document.getElementById('select-all');
      const tbody = document.getElementById('users-rows');
      const paginationFooter = document.getElementById('pagination-footer');

      let currentLimit = <?php echo (int)$limit; ?>;
      let currentSearch = '<?php echo $searchTerm !== null ? addslashes($searchTerm) : ""; ?>';
      let searchDebounce;

      function bindPageSizeSelect() {
        const sel = document.getElementById('page-size-select');
        if (!sel) return;
        sel.addEventListener('change', () => {
          currentLimit = sel.value;
          loadPage(1);
        });
      }

      async function loadPage(page, { pushState = true, fromSearch = false, scroll = true } = {}) {
        paginationFooter.style.opacity = '0.5';

        const caretPos = (fromSearch && searchInput) ? searchInput.selectionStart : null;

        try {
          const res = await fetch('match_participant.php?ajax_page=1&page=' + page + '&limit=' + currentLimit +
            (currentSearch ? '&search=' + encodeURIComponent(currentSearch) : ''));
          const data = await res.json();

          tbody.innerHTML = data.rows;
          paginationFooter.innerHTML = data.pagination;
          bindPageSizeSelect();
          currentLimit = data.limit;

          if (pushState) {
            const url = new URL(window.location);
            url.searchParams.set('page', data.page);
            url.searchParams.set('limit', data.limit);
            if (currentSearch) {
              url.searchParams.set('search', currentSearch);
            } else {
              url.searchParams.delete('search');
            }
            history.pushState({
              page: data.page,
              limit: data.limit
            }, '', url);
          }

          if (fromSearch) {
            if (searchInput) {
              searchInput.focus();
              if (caretPos !== null) {
                searchInput.setSelectionRange(caretPos, caretPos);
              }
            }
          } else {
            const activeLink = paginationFooter.querySelector('.page-link.active');
            if (activeLink) activeLink.focus({
              preventScroll: true
            });

            if (scroll) {
              tbody.closest('.card').scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
              });
            }
          }
        } catch (e) {
          alert('Failed to load page');
        } finally {
          paginationFooter.style.opacity = '1';
        }
      }

      if (searchInput) {
        searchInput.addEventListener('input', () => {
          clearTimeout(searchDebounce);
          searchDebounce = setTimeout(() => {
            currentSearch = searchInput.value.trim();
            loadPage(1, { fromSearch: true });
          }, 300);
        });

        searchInput.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchDebounce);
            currentSearch = searchInput.value.trim();
            loadPage(1, { fromSearch: true });
          }
        });
      }

      // Join status re-queries the server (like the search box does)
      // rather than hiding/showing already-loaded rows, so matches on
      // other pages are picked up.
      if (joinStatusFilter) {
        joinStatusFilter.addEventListener('change', () => {
          currentJoinStatus = joinStatusFilter.value;
          loadPage(1, { scroll: false });
        });
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

      bindPageSizeSelect();

      const participantForm = document.querySelector('#form-modal form');
      if (participantForm) {
        participantForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          const submitBtn = participantForm.querySelector('button[type="submit"]');
          if (submitBtn) submitBtn.disabled = true;

          try {
            const res = await fetch('match_participant.php', {
              method: 'POST',
              body: new FormData(participantForm)
            });
            const data = await res.json();

            if (!data.success) {
              alert(data.error || 'Failed to save participant');
              return;
            }

            closeModal();
            const params = new URLSearchParams(window.location.search);
            loadPage(params.get('page') || 1, { pushState: false });
          } catch (err) {
            alert('Failed to save participant');
          } finally {
            if (submitBtn) submitBtn.disabled = false;
          }
        });
      }

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
        const search = params.get('search');
        if (limit) currentLimit = limit;
        currentSearch = search || '';
        if (searchInput) searchInput.value = currentSearch;
        loadPage(page, { pushState: false });
      });
    });
  </script>
</body>

</html>