<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';
$conn = mysqli_connect("localhost", "root", "", "teamup");

// Handle AJAX visibility update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_visibility') {
  header('Content-Type: application/json');
  $rating_id = intval($_POST['rating_id'] ?? 0);
  $visibility = trim($_POST['visibility'] ?? '');
  $allowed = ['Visible', 'Not Visible'];
  if (!in_array($visibility, $allowed)) {
    $visibility = 'Not Visible';
  }

  $stmt = mysqli_prepare($conn, "UPDATE ratings SET visibility = ? WHERE rating_id = ?");
  mysqli_stmt_bind_param($stmt, "si", $visibility, $rating_id);
  $success = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  echo json_encode([
    'success' => $success,
    'rating_id' => $rating_id,
    'visibility' => $visibility
  ]);
  exit;
}

// Pagination setup
$allowedLimits = [25, 50, 75, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowedLimits) ? intval($_GET['limit']) : 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search & visibility setup
$searchQuery = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : null;
$visibilityFilter = isset($_GET['visibility']) && in_array($_GET['visibility'], ['Visible', 'Not Visible']) ? $_GET['visibility'] : null;

$whereClauses = [];
$params = [];
$types = "";

if ($searchQuery !== null) {
  $whereClauses[] = "(u.username LIKE ? OR m.title LIKE ? OR r.review LIKE ?)";
  $like = '%' . $searchQuery . '%';
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $types .= "sss";
}

if ($visibilityFilter !== null) {
  if ($visibilityFilter === 'Visible') {
    $whereClauses[] = "LOWER(TRIM(r.visibility)) IN ('visible', 'public', '1', 'true')";
  } else {
    $whereClauses[] = "(r.visibility IS NULL OR TRIM(r.visibility) = '' OR LOWER(TRIM(r.visibility)) NOT IN ('visible', 'public', '1', 'true'))";
  }
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Count query
$countSql = "SELECT COUNT(*) AS total
  FROM ratings r
  LEFT JOIN matches m ON r.match_id = m.match_id
  LEFT JOIN users u ON r.user_id = u.user_id
  $whereSql";

if (!empty($params)) {
  $countStmt = mysqli_prepare($conn, $countSql);
  mysqli_stmt_bind_param($countStmt, $types, ...$params);
  mysqli_stmt_execute($countStmt);
  $countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
  mysqli_stmt_close($countStmt);
} else {
  $countResult = mysqli_query($conn, $countSql);
  $countRow = mysqli_fetch_assoc($countResult);
}
$count = $countRow['total'];
$totalPages = max(1, ceil($count / $limit));

// Data query
$dataSql = "SELECT r.*, m.title, u.username
  FROM ratings r
  LEFT JOIN matches m ON r.match_id = m.match_id
  LEFT JOIN users u ON r.user_id = u.user_id
  $whereSql
  ORDER BY r.rating_id DESC
  LIMIT ?, ?";

$dataStmt = mysqli_prepare($conn, $dataSql);
$bindParams = $params;
$bindParams[] = $offset;
$bindParams[] = $limit;
$bindTypes = $types . "ii";
mysqli_stmt_bind_param($dataStmt, $bindTypes, ...$bindParams);
mysqli_stmt_execute($dataStmt);
$qu = mysqli_stmt_get_result($dataStmt);

function renderRatingRows($qu)
{
  ob_start();
  while ($row = mysqli_fetch_array($qu)) {
    $isVis = in_array(strtolower(trim($row['visibility'] ?? '')), ['visible', 'public', '1', 'true']);
    $visColor = $isVis ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-slate-500/15 text-slate-400 border-slate-500/30';

    echo "<tr>";
    echo "<td>" . $row["rating_id"] . "</td>";
    echo "<td>" . htmlspecialchars($row["title"] ?? '', ENT_QUOTES, 'UTF-8') . " <span class='text-xs text-[var(--text-muted)]'>(" . $row["match_id"] . ")</span></td>";
    echo "<td>" . htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8') . " <span class='text-xs text-[var(--text-muted)]'>(" . $row["user_id"] . ")</span></td>";
    echo "<td><span class='text-amber-400 font-semibold'>" . htmlspecialchars($row["rating"]) . " ★</span></td>";
    echo "<td style='max-width:280px;white-space:normal;overflow:hidden;text-overflow:ellipsis;'>" . htmlspecialchars($row["review"] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>
      <select class='visibility-select form-control text-xs font-semibold rounded-lg border transition-all cursor-pointer {$visColor}' 
              data-id='" . $row['rating_id'] . "' 
              style='width:120px;height:30px;padding:2px 8px;' aria-label='Toggle Visibility'>
        <option value='Visible' " . ($isVis ? 'selected' : '') . ">Visible</option>
        <option value='Not Visible' " . (!$isVis ? 'selected' : '') . ">Not Visible</option>
      </select>
    </td>";
    echo "<td>" . $row["created_at"] . "</td>";
    echo "</tr>";
  }
  return ob_get_clean();
}

function renderPagination($page, $totalPages, $count, $limit, $searchQuery = null, $visibilityFilter = null)
{
  $allowedLimits = [25, 50, 75, 100, 250, 500];
  $extra = '';
  if ($searchQuery !== null) $extra .= '&search=' . urlencode($searchQuery);
  if ($visibilityFilter !== null) $extra .= '&visibility=' . urlencode($visibilityFilter);
  ob_start();
  ?>
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <select id="page-size-select" class="form-control" style="width:auto;height:32px" aria-label="Rows per page">
      <?php foreach ($allowedLimits as $opt): ?>
        <option value="<?php echo $opt; ?>" <?php echo ($opt == $limit) ? 'selected' : ''; ?>><?php echo $opt; ?> Records</option>
      <?php endforeach; ?>
    </select>
    <div class="text-muted">
      Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $count; ?> ratings)
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

// Handle AJAX pagination
if (isset($_GET['ajax_page'])) {
  header('Content-Type: application/json');
  echo json_encode([
    'rows' => renderRatingRows($qu),
    'pagination' => renderPagination($page, $totalPages, $count, $limit, $searchQuery, $visibilityFilter),
    'page' => $page,
    'totalPages' => $totalPages,
    'limit' => $limit,
    'visibility' => $visibilityFilter
  ]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ratings | TeamUp 2026 v4</title>
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
  <meta name="description" content="Ratings — Database Tables > Ratings. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Ratings">
  <meta property="og:description" content="Ratings — Database Tables > Ratings. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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

<body data-shell="admin" data-page="manage-sports" data-breadcrumb="Home > Database Tables > Ratings">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>

  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">All Ratings</h1>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php" style="color: var(--text-secondary);">Dashboard</a>
            <span>></span>
            <a href="/Team-Up/PHP/AdminPanel/Pages/User/user_ratings.php" style="color: var(--text-secondary);">Ratings</a>
          </div>
        </div>
      </div>

      <div class="row col-3" style="margin-bottom:16px">
        <div class="card">
          <div class="stat">
            <div class="stat-icon teal">
              <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                <path d="M12 2.5l2.9 6 6.6.6-5 4.4 1.5 6.5L12 16.8 6 20l1.5-6.5-5-4.4 6.6-.6z" />
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total ratings</div>
              <div class="stat-value-row"><span class="stat-value"><?php echo $count; ?></span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="justify-content: space-between; align-items: center; gap:12px; flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div class="search-box" style="width: 260px; margin: 0;">
              <svg class="s-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="7" cy="7" r="5" />
                <path d="M11 11l3.5 3.5" />
              </svg>
              <input type="text" id="users-search" placeholder="Search by username, match, review…" aria-label="Search by username or match title" value="<?php echo $searchQuery !== null ? htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <select id="filter-visibility" class="form-control" style="width:auto;height:38px;padding:4px 12px;font-size:13px;border-radius:var(--radius);" aria-label="Filter by visibility">
              <option value="">All Visibility</option>
              <option value="Visible" <?php echo ($visibilityFilter === 'Visible') ? 'selected' : ''; ?>>Visible Only</option>
              <option value="Not Visible" <?php echo ($visibilityFilter === 'Not Visible') ? 'selected' : ''; ?>>Not Visible Only</option>
            </select>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <!-- <th style="width: 40px;"><input type="checkbox" id="select-all" aria-label="Select all rows"></th> -->
                  <th>Rating Id</th>
                  <th>Match</th>
                  <th>User</th>
                  <th>Rating</th>
                  <th>Review</th>
                  <th>Visibility</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody id="users-rows">
                <?php echo renderRatingRows($qu); ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer" id="pagination-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
          <?php echo renderPagination($page, $totalPages, $count, $limit, $searchQuery); ?>
        </div>
      </div>

    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
  </main>

  <!-- Modal overlay -->
  <div id="form-modal" class="modal-backdrop" style="display: none; position: fixed; top:0; left:0; width:100vw; height:100vh; background: rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div class="card" style="width: 480px; max-width: 90%; background: var(--bg-surface); padding: 20px; border-radius: var(--radius); border: 1px solid var(--border-color);">
      <div class="card-header" style="padding: 0 0 12px 0; margin-bottom: 16px; border-bottom: 1px solid var(--border-color-light); display:flex; justify-content:space-between; align-items:center;">
        <h3 id="modal-title" class="card-title">Add Rating</h3>
        <button onclick="closeModal()" style="background: none; border:none; color: var(--text-muted); cursor:pointer; font-size:20px;">&times;</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" id="form-action" value="add">
        <input type="hidden" name="rating_id" id="form-rating-id">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div class="form-group">
            <label class="form-label">Match Id</label>
            <input type="number" name="match_id" id="form-match-id" class="form-control" required min="1">
          </div>
          <div class="form-group">
            <label class="form-label">User Id</label>
            <input type="number" name="user_id" id="form-user-id" class="form-control" required min="1">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 12px;">
          <label class="form-label">Rating</label>
          <input type="number" name="rating" id="form-rating" class="form-control" required min="1" max="5">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label">Review</label>
          <textarea name="review" id="form-review" class="form-control" rows="3" placeholder="Write a review..."></textarea>
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
      const visibilityFilter = document.getElementById('filter-visibility');
      const selectAllCb = document.getElementById('select-all');
      const tbody = document.getElementById('users-rows');
      const paginationFooter = document.getElementById('pagination-footer');

      let currentLimit = <?php echo (int)$limit; ?>;
      let currentSearch = '<?php echo $searchQuery !== null ? addslashes($searchQuery) : ""; ?>';
      let currentVisibility = '<?php echo $visibilityFilter !== null ? addslashes($visibilityFilter) : ""; ?>';
      let searchDebounce;

      function bindPageSizeSelect() {
        const sel = document.getElementById('page-size-select');
        if (!sel) return;
        sel.addEventListener('change', () => {
          currentLimit = sel.value;
          loadPage(1);
        });
      }

      async function loadPage(page, { pushState = true, fromSearch = false } = {}) {
        paginationFooter.style.opacity = '0.5';

        const caretPos = (fromSearch && searchInput) ? searchInput.selectionStart : null;

        try {
          const res = await fetch('user_ratings.php?ajax_page=1&page=' + page + '&limit=' + currentLimit +
            (currentSearch ? '&search=' + encodeURIComponent(currentSearch) : '') +
            (currentVisibility ? '&visibility=' + encodeURIComponent(currentVisibility) : ''));
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
            if (currentVisibility) {
              url.searchParams.set('visibility', currentVisibility);
            } else {
              url.searchParams.delete('visibility');
            }
            history.pushState({
              page: data.page,
              limit: data.limit,
              visibility: currentVisibility
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

      // Handle visibility select change on table rows
      document.addEventListener('change', async (e) => {
        const select = e.target.closest('.visibility-select');
        if (!select) return;

        const ratingId = select.dataset.id;
        const newVisibility = select.value;

        select.disabled = true;
        select.style.opacity = '0.5';

        try {
          const fd = new FormData();
          fd.append('action', 'update_visibility');
          fd.append('rating_id', ratingId);
          fd.append('visibility', newVisibility);

          const res = await fetch('user_ratings.php', {
            method: 'POST',
            body: fd
          });
          const data = await res.json();

          if (data && data.success) {
            if (newVisibility === 'Visible') {
              select.className = 'visibility-select form-control text-xs font-semibold rounded-lg border transition-all cursor-pointer bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
            } else {
              select.className = 'visibility-select form-control text-xs font-semibold rounded-lg border transition-all cursor-pointer bg-slate-500/15 text-slate-400 border-slate-500/30';
            }
          } else {
            alert('Failed to update visibility');
          }
        } catch (err) {
          alert('Error updating visibility');
        } finally {
          select.disabled = false;
          select.style.opacity = '1';
        }
      });

      if (visibilityFilter) {
        visibilityFilter.addEventListener('change', () => {
          currentVisibility = visibilityFilter.value;
          loadPage(1);
        });
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
        currentVisibility = params.get('visibility') || '';
        if (visibilityFilter) visibilityFilter.value = currentVisibility;
        loadPage(page, { pushState: false });
      });
    });
  </script>
</body>

</html>