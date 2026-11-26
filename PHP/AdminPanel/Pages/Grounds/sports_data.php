<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';
$conn = mysqli_connect("localhost", "root", "", "teamup");

// Pagination setup
$allowedLimits = [25, 50, 75, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowedLimits) ? intval($_GET['limit']) : 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search setup
$searchName = isset($_GET['sport_name']) && trim($_GET['sport_name']) !== '' ? trim($_GET['sport_name']) : null;

if ($searchName !== null) {
  $likeTerm = "%" . $searchName . "%";
  $countStmt = mysqli_prepare($conn, "select count(*) as total from sports where sport_name like ?");
  mysqli_stmt_bind_param($countStmt, "s", $likeTerm);
  mysqli_stmt_execute($countStmt);
  $countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt));
} else {
  $countResult = mysqli_query($conn, "select count(*) as total from sports");
  $countRow = mysqli_fetch_assoc($countResult);
}
$count = $countRow['total'];
$totalPages = max(1, ceil($count / $limit));

if ($searchName !== null) {
  $likeTerm = "%" . $searchName . "%";
  $stmt = mysqli_prepare($conn, "select * from sports where sport_name like ? order by sport_id desc limit ?, ?");
  mysqli_stmt_bind_param($stmt, "sii", $likeTerm, $offset, $limit);
  mysqli_stmt_execute($stmt);
  $qu = mysqli_stmt_get_result($stmt);
} else {
  $sql = "select * from sports order by sport_id desc limit $offset, $limit";
  $qu = mysqli_query($conn, $sql);
}

function renderSportRows($qu)
{
  ob_start();
  while ($row = mysqli_fetch_array($qu)) {
    echo "<tr>";
    // echo "<td><input type='checkbox' aria-label='Select row'></td>";
    echo "<td>" . $row["sport_id"] . "</td>";
    echo "<td>" . $row["sport_name"] . "</td>";
    echo "<td class='description-col'>" . $row["description"] . "</td>";
    echo "<td>" . $row["created_at"] . "</td>";
    echo "</tr>";
  }
  return ob_get_clean();
}

function renderPagination($page, $totalPages, $count, $limit, $searchName = null)
{
  $allowedLimits = [25, 50, 75, 100, 250, 500];
  $extra = $searchName !== null ? '&sport_name=' . urlencode($searchName) : '';
  ob_start();
  ?>
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <select id="page-size-select" class="form-control" style="width:auto;height:32px" aria-label="Rows per page">
      <?php foreach ($allowedLimits as $opt): ?>
        <option value="<?php echo $opt; ?>" <?php echo ($opt == $limit) ? 'selected' : ''; ?>><?php echo $opt; ?> Records</option>
      <?php endforeach; ?>
    </select>
    <div class="text-muted">
      Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $count; ?> sports)
    </div>
  </div>
  <div style="display:flex;gap:4px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?><?php echo $extra; ?>" class="btn btn-sm page-link" data-page="<?php echo $page - 1; ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php
    $window = 1; // pages shown on each side of the current page
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
    'rows' => renderSportRows($qu),
    'pagination' => renderPagination($page, $totalPages, $count, $limit, $searchName),
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
  <title>Sport Data | TeamUp 2026 v4</title>
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
  <meta name="description" content="Sport management — Admin > Sports. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Sport management">
  <meta property="og:description" content="Sport management — Admin > Sports. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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

    #sportsTable td.description-col,
      .description-col {
        max-width: 300px;
        white-space: normal;
        word-wrap: break-word;
      }
  </style>
</head>

<body data-shell="admin" data-page="sport_data" data-breadcrumb="Home > Admin > Sport Data">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">Sport Data</h1>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php" style="color: var(--text-secondary);">Dashboard</a>
            <span>></span>
            <a style="color: var(--text-secondary);">Sports</a>
          </div>
        </div>
      </div>

      <div class="row col-3" style="margin-bottom:16px">
        <div class="card">
          <div class="stat">
            <div class="stat-icon teal">
              <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M7 4h10v5a5 5 0 01-10 0V4z" />
                <path d="M7 5H4a2 2 0 002 4M17 5h3a2 2 0 01-2 4" />
                <path d="M12 14v3M8 21h8" />
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total sports</div>
              <div class="stat-value-row"><span class="stat-value"><?php echo $count; ?></span></div>
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
              <input type="text" id="users-search" placeholder="Search by name…" aria-label="Search sports" value="<?php echo $searchName !== null ? htmlspecialchars($searchName) : ''; ?>">
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table" id="sportsTable">
              <thead>
                <tr>
                  <!-- <th style="width:40px"><input type="checkbox" id="select-all" aria-label="Select all rows"></th> -->
                  <th>Sport ID</th>
                  <th>Sport Name</th>
                  <th>Description</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody id="users-rows">
                <?php echo renderSportRows($qu); ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer" id="pagination-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
          <?php echo renderPagination($page, $totalPages, $count, $limit, $searchName); ?>
        </div>
      </div>

    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>

  </main>

  <script type="module">
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('users-search');
      const selectAllCb = document.getElementById('select-all');
      const tbody = document.getElementById('users-rows');
      const paginationFooter = document.getElementById('pagination-footer');

      let currentLimit = <?php echo (int)$limit; ?>;
      let currentSearch = '<?php echo $searchName !== null ? addslashes($searchName) : ""; ?>';
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
          const res = await fetch('sports_data.php?ajax_page=1&page=' + page + '&limit=' + currentLimit +
            (currentSearch ? '&sport_name=' + encodeURIComponent(currentSearch) : ''));
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
              url.searchParams.set('sport_name', currentSearch);
            } else {
              url.searchParams.delete('sport_name');
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
        const search = params.get('sport_name');
        if (limit) currentLimit = limit;
        currentSearch = search || '';
        if (searchInput) searchInput.value = currentSearch;
        loadPage(page, { pushState: false });
      });
    });
  </script>
</body>

</html>