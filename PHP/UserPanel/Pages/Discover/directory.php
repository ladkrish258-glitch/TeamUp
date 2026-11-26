<?php
$page = 'directory';
$pageTitle = 'Neighbors Directory';
$feedMaxWidth = 'max-w-[1140px]';
require_once dirname(__DIR__) . '/Components/Header/header.php';

$currentUserId = (int)($_SESSION['user_id'] ?? 0);

// Fetch neighbors along with their sports list
$stmt = $pdo->query("
    SELECT 
        u.user_id, u.full_name, u.username, u.city, u.area, u.profile_photo, u.created_at,
        GROUP_CONCAT(DISTINCT s.sport_name SEPARATOR ', ') as sports_list
    FROM users u
    LEFT JOIN user_sports us ON u.user_id = us.user_id
    LEFT JOIN sports s ON us.sport_id = s.sport_id
    WHERE u.user_id != $currentUserId
    GROUP BY u.user_id
    ORDER BY u.full_name ASC
");
$neighbors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vibrant gradient palette for initial avatars
$gradients = [
    'linear-gradient(135deg, #10b981, #059669)',
    'linear-gradient(135deg, #3b82f6, #1d4ed8)',
    'linear-gradient(135deg, #8b5cf6, #6d28d9)',
    'linear-gradient(135deg, #f59e0b, #d97706)',
    'linear-gradient(135deg, #ec4899, #be185d)',
    'linear-gradient(135deg, #06b6d4, #0891b2)'
];
?>

<!-- Header & Title -->
<div class="mb-5 flex justify-between items-end flex-wrap gap-4">
    <div>
        <h2 class="m-0 mb-1.5 text-[26px] font-bold text-gray-900 dark:text-gray-50">Neighbors Directory</h2>
        <p class="m-0 text-gray-500 dark:text-gray-400 text-[14.5px]">Connect with other sports enthusiasts and players in your area.</p>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        <div class="text-[13.5px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-1.5 rounded-full">
            <span id="neighbors-count"><?= count($neighbors) ?></span> Neighbors in your network
        </div>
        <div class="text-[13px] font-semibold text-nd-green bg-nd-green/10 border border-nd-green/25 px-3.5 py-1.5 rounded-full">
            Grid View 3&times;4 (12 / page)
        </div>
    </div>
</div>

<!-- Search & Controls Bar -->
<div class="mb-6 flex justify-between items-center flex-wrap gap-3.5">
    <div class="relative flex-1 max-w-[520px] min-w-[280px]">
        <input type="text" id="neighbor-search-input" 
               placeholder="Search neighbors by name, handle, area, or sport..." 
               class="w-full py-3 pr-4 pl-10 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 text-[14.5px] outline-none shadow-sm focus:border-nd-green transition-colors">
        <svg viewBox="0 0 24 24" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] fill-gray-500 dark:fill-gray-400">
            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
        </svg>
    </div>

    <div id="page-summary" class="text-[13.5px] text-gray-500 dark:text-gray-400 font-medium">
        Showing <span id="showing-range">1–12</span> of <span id="total-visible"><?= count($neighbors) ?></span>
    </div>
</div>

<!-- 3x4 Neighbors Grid -->
<div id="neighbors-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-7">
    <?php foreach ($neighbors as $index => $neighbor): 
        $initial = strtoupper(substr($neighbor['full_name'] ?? 'U', 0, 1));
        $grad = $gradients[$index % count($gradients)];
        
        $avatar = null;
        if (!empty($neighbor['profile_photo'])) {
            $raw = trim($neighbor['profile_photo']);
            if (strpos($raw, 'http') === 0 || strpos($raw, '/') === 0 || strpos($raw, '../') === 0) {
                $avatar = strpos($raw, '/Team-Up/') === 0 ? '../../../../' . substr($raw, 9) : $raw;
            } else {
                $avatar = '../../../../images/User/Profile/' . ltrim($raw, '/');
            }
        }

        $locationText = ($neighbor['area'] ?? 'Local') . (!empty($neighbor['city']) ? ' • ' . $neighbor['city'] : '');
        $searchTerms = strtolower($neighbor['full_name'] . ' ' . $neighbor['username'] . ' ' . ($neighbor['city'] ?? '') . ' ' . ($neighbor['area'] ?? '') . ' ' . ($neighbor['sports_list'] ?? ''));
    ?>
        <div class="nd-neighbor-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl pt-6 px-4 pb-5 text-center flex flex-col transition-all duration-200 hover:-translate-y-1 hover:border-nd-green/45 hover:shadow-xl shadow-sm" data-search="<?= htmlspecialchars($searchTerms) ?>">
            
            <!-- Avatar -->
            <div class="relative w-[72px] h-[72px] mx-auto mb-3">
                <?php if (!empty($avatar)): ?>
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($neighbor['full_name']) ?>" 
                         class="w-[72px] h-[72px] rounded-full object-cover shadow-md"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="hidden w-[72px] h-[72px] rounded-full items-center justify-center text-[26px] text-white font-bold shadow-md" style="background: <?= $grad ?>;">
                        <?= $initial ?>
                    </div>
                <?php else: ?>
                    <div class="w-[72px] h-[72px] rounded-full flex items-center justify-center text-[26px] text-white font-bold shadow-md" style="background: <?= $grad ?>;">
                        <?= $initial ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Name & Handle -->
            <div class="font-bold text-base text-gray-900 dark:text-gray-50 mb-0.5">
                <?= htmlspecialchars($neighbor['full_name']) ?>
            </div>
            <div class="text-[13px] text-gray-500 dark:text-gray-400 mb-2.5">
                @<?= htmlspecialchars($neighbor['username']) ?>
            </div>

            <!-- Location Pill -->
            <div class="inline-flex items-center justify-center gap-1.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs text-gray-500 dark:text-gray-400 mb-3">
                <svg class="w-3.5 h-3.5 fill-nd-green shrink-0" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                <span><?= htmlspecialchars($neighbor['area'] ?? 'Local') ?><?= !empty($neighbor['city']) ? ' &bull; ' . htmlspecialchars($neighbor['city']) : '' ?></span>
            </div>

            <!-- Sports Tags -->
            <div class="flex flex-wrap justify-center gap-1.5 mb-4 min-h-[26px]">
                <?php if (!empty($neighbor['sports_list'])): 
                    $sportsArr = explode(',', $neighbor['sports_list']);
                    foreach (array_slice($sportsArr, 0, 2) as $sp):
                ?>
                    <span class="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400"><?= htmlspecialchars(trim($sp)) ?></span>
                <?php endforeach; 
                    if (count($sportsArr) > 2):
                ?>
                    <span class="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">+<?= count($sportsArr) - 2 ?></span>
                <?php endif; else: ?>
                    <span class="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 opacity-75">Active Neighbor</span>
                <?php endif; ?>
            </div>

            <!-- View Profile Button (Opens Side Panel) -->
            <button type="button"
                    class="nd-view-profile-btn w-full mt-auto inline-flex items-center justify-center gap-1.5 bg-nd-green hover:bg-nd-green-hover text-white border-0 py-2 px-4 rounded-full font-semibold text-sm cursor-pointer transition-colors" 
                    data-user-id="<?= (int)$neighbor['user_id'] ?>"
                    data-name="<?= htmlspecialchars($neighbor['full_name']) ?>"
                    data-username="<?= htmlspecialchars($neighbor['username']) ?>"
                    data-location="<?= htmlspecialchars($locationText) ?>"
                    data-avatar="<?= htmlspecialchars($avatar ?? '') ?>"
                    data-initial="<?= $initial ?>"
                    data-gradient="<?= $grad ?>"
                    data-sports="<?= htmlspecialchars($neighbor['sports_list'] ?? '') ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>View Profile</span>
            </button>

        </div>
    <?php endforeach; ?>
</div>

<!-- Empty state for search -->
<div id="no-neighbors-found" class="hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center py-12 px-6 rounded-2xl mt-4">
    <div class="text-[38px] mb-2.5">🔍</div>
    <h3 class="m-0 mb-1.5 text-gray-900 dark:text-gray-50 font-bold text-lg">No neighbors found</h3>
    <p class="text-gray-500 dark:text-gray-400 text-sm m-0">Try searching with a different name, location, or sport.</p>
</div>

<!-- 3x4 Pagination Navigation -->
<div id="pagination-controls" class="flex items-center justify-center gap-1.5 my-8 flex-wrap"></div>

<!-- SIDE PROFILE DRAWER & BACKDROP -->
<div id="neighbor-side-backdrop" class="fixed inset-0 bg-black/55 backdrop-blur-[3px] z-[9998] opacity-0 pointer-events-none transition-opacity duration-300"></div>
<div id="neighbor-side-panel" class="fixed top-0 right-0 w-[440px] max-w-[92vw] h-screen bg-white dark:bg-gray-800 z-[9999] shadow-2xl translate-x-full transition-transform duration-300 ease-out flex flex-col overflow-hidden">
    <!-- Drawer Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" width="18" height="18" class="fill-nd-green"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span class="font-bold text-[15px] text-gray-900 dark:text-gray-50">Neighbor Profile</span>
        </div>
        <button type="button" id="neighbor-side-close-btn" title="Close Profile"
                class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-50 w-[34px] h-[34px] rounded-full flex items-center justify-center cursor-pointer text-lg hover:bg-gray-200 dark:hover:bg-gray-700 hover:rotate-90 transition-all">&times;</button>
    </div>

    <!-- Drawer Body -->
    <div class="flex-1 overflow-y-auto pb-8">
        <!-- Cover Banner -->
        <div class="h-[110px] w-full relative" style="background: linear-gradient(135deg, #1f8a55 0%, #176840 100%);"></div>

        <!-- Avatar & User Info Header -->
        <div class="px-5 pb-4 relative">
            <div id="sp-avatar-container" class="relative w-[84px] h-[84px] -mt-[42px] mb-3">
                <img id="sp-avatar-img" src="" alt="" class="w-[84px] h-[84px] rounded-full object-cover border-[3px] border-white dark:border-gray-800 shadow-lg hidden">
                <div id="sp-avatar-letter" class="w-[84px] h-[84px] rounded-full border-[3px] border-white dark:border-gray-800 flex items-center justify-center text-[32px] font-bold text-white shadow-lg" style="background: linear-gradient(135deg, #10b981, #059669);">
                    U
                </div>
            </div>

            <div class="flex justify-between items-start gap-2.5 flex-wrap">
                <div>
                    <h2 id="sp-full-name" class="m-0 text-[22px] text-gray-900 dark:text-gray-50 font-bold">Neighbor Name</h2>
                    <div id="sp-username" class="text-[13.5px] text-gray-500 dark:text-gray-400 mt-0.5">@username</div>
                </div>
                <span id="sp-badge" class="text-[11.5px] font-semibold px-2.5 py-1 rounded-xl bg-nd-green/10 text-nd-green border border-nd-green/25">
                    Active Player
                </span>
            </div>

            <!-- Location Pill -->
            <div class="flex items-center gap-1.5 mt-2.5 text-[13px] text-gray-500 dark:text-gray-400">
                <svg viewBox="0 0 24 24" width="15" height="15" class="fill-nd-green"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                <span id="sp-location">Local Area</span>
            </div>
        </div>

        <div class="h-px bg-gray-200 dark:bg-gray-700 mx-5 mt-1.5 mb-4"></div>

        <!-- Quick Stats -->
        <div class="px-5 pb-4">
            <div class="grid grid-cols-2 gap-2.5">
                <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded-xl text-center">
                    <div id="sp-stat-matches" class="text-xl font-bold text-gray-900 dark:text-gray-50">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Matches Hosted</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded-xl text-center">
                    <div id="sp-stat-joined" class="text-xl font-bold text-gray-900 dark:text-gray-50">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Member Since</div>
                </div>
            </div>
        </div>

        <!-- Sports & Skills Section -->
        <div class="px-5">
            <div class="font-bold text-[14.5px] text-gray-900 dark:text-gray-50 mb-3 flex items-center justify-between">
                <span>Sports &amp; Experience</span>
                <span id="sp-sports-count" class="text-xs text-gray-500 dark:text-gray-400 font-normal"></span>
            </div>

            <div id="sp-sports-container" class="flex flex-col gap-2.5">
                <!-- Populated via JS -->
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // --- 3x4 GRID & PAGINATION LOGIC ---
    const PAGE_SIZE = 12; // 3 columns x 4 rows = 12 per page
    let currentPage = 1;

    const allCards = Array.from(document.querySelectorAll('.nd-neighbor-card'));
    let filteredCards = [...allCards];

    const searchInput = document.getElementById('neighbor-search-input');
    const paginationEl = document.getElementById('pagination-controls');
    const emptyEl = document.getElementById('no-neighbors-found');
    const showingRangeEl = document.getElementById('showing-range');
    const totalVisibleEl = document.getElementById('total-visible');
    const countBadge = document.getElementById('neighbors-count');
    const gridEl = document.getElementById('neighbors-grid');

    const btnBase = 'min-w-[38px] h-[38px] px-3 rounded-[10px] border font-semibold text-[13.5px] inline-flex items-center justify-center transition-all select-none';
    const btnIdle = 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer';
    const btnActive = 'border-nd-green bg-nd-green text-white shadow-md cursor-pointer';
    const btnDisabled = 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 opacity-40 cursor-not-allowed pointer-events-none';

    function renderPage() {
        const total = filteredCards.length;
        const totalPages = Math.ceil(total / PAGE_SIZE) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * PAGE_SIZE;
        const endIndex = Math.min(startIndex + PAGE_SIZE, total);

        allCards.forEach(c => {
            c.classList.add('hidden');
            c.classList.remove('flex');
        });

        for (let i = startIndex; i < endIndex; i++) {
            if (filteredCards[i]) {
                filteredCards[i].classList.remove('hidden');
                filteredCards[i].classList.add('flex');
            }
        }

        if (total === 0) {
            showingRangeEl.textContent = '0';
            totalVisibleEl.textContent = '0';
            emptyEl.classList.remove('hidden');
            paginationEl.style.display = 'none';
        } else {
            showingRangeEl.textContent = `${startIndex + 1}–${endIndex}`;
            totalVisibleEl.textContent = total;
            emptyEl.classList.add('hidden');
            paginationEl.style.display = 'flex';
        }

        countBadge.textContent = total;
        renderPaginationButtons(totalPages);
    }

    function renderPaginationButtons(totalPages) {
        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }

        let html = `<button class="nd-page-btn ${btnBase} ${currentPage === 1 ? btnDisabled : btnIdle}" data-page="${currentPage - 1}">&laquo; Prev</button>`;

        const maxVisibleButtons = 5;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + maxVisibleButtons - 1);

        if (endPage - startPage < maxVisibleButtons - 1) {
            startPage = Math.max(1, endPage - maxVisibleButtons + 1);
        }

        if (startPage > 1) {
            html += `<button class="nd-page-btn ${btnBase} ${btnIdle}" data-page="1">1</button>`;
            if (startPage > 2) {
                html += `<span class="text-gray-500 dark:text-gray-400 px-1.5 font-semibold">&hellip;</span>`;
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            html += `<button class="nd-page-btn ${btnBase} ${p === currentPage ? btnActive : btnIdle}" data-page="${p}">${p}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="text-gray-500 dark:text-gray-400 px-1.5 font-semibold">&hellip;</span>`;
            }
            html += `<button class="nd-page-btn ${btnBase} ${btnIdle}" data-page="${totalPages}">${totalPages}</button>`;
        }

        html += `<button class="nd-page-btn ${btnBase} ${currentPage === totalPages ? btnDisabled : btnIdle}" data-page="${currentPage + 1}">Next &raquo;</button>`;

        paginationEl.innerHTML = html;

        paginationEl.querySelectorAll('.nd-page-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetPage = parseInt(this.getAttribute('data-page'));
                if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
                    currentPage = targetPage;
                    renderPage();
                    gridEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    searchInput.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        filteredCards = allCards.filter(card => {
            const text = card.dataset.search || '';
            return !q || text.includes(q);
        });
        currentPage = 1;
        renderPage();
    });

    renderPage();

    // --- SIDE PANEL PROFILE DRAWER LOGIC ---
    const panel = document.getElementById('neighbor-side-panel');
    const backdrop = document.getElementById('neighbor-side-backdrop');
    const closeBtn = document.getElementById('neighbor-side-close-btn');

    const nameEl = document.getElementById('sp-full-name');
    const usernameEl = document.getElementById('sp-username');
    const locationEl = document.getElementById('sp-location');
    const avatarImg = document.getElementById('sp-avatar-img');
    const avatarLetter = document.getElementById('sp-avatar-letter');
    const sportsContainer = document.getElementById('sp-sports-container');
    const sportsCountEl = document.getElementById('sp-sports-count');
    const matchesStatEl = document.getElementById('sp-stat-matches');
    const joinedStatEl = document.getElementById('sp-stat-joined');

    let currentOpenUserId = null;

    function openSidePanel(cardData) {
        currentOpenUserId = cardData.userId;

        nameEl.textContent = cardData.name;
        usernameEl.textContent = '@' + cardData.username;
        locationEl.textContent = cardData.location || 'Local Neighborhood';

        if (cardData.avatar) {
            avatarImg.src = cardData.avatar;
            avatarImg.classList.remove('hidden');
            avatarLetter.classList.add('hidden');
        } else {
            avatarImg.classList.add('hidden');
            avatarLetter.classList.remove('hidden');
            avatarLetter.textContent = cardData.initial || 'U';
            avatarLetter.style.background = cardData.gradient || 'linear-gradient(135deg, #10b981, #059669)';
        }

        matchesStatEl.textContent = '...';
        joinedStatEl.textContent = '...';
        sportsCountEl.textContent = '';
        sportsContainer.innerHTML = `
            <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-[13.5px]">
                Loading sports &amp; details...
            </div>
        `;

        panel.classList.remove('translate-x-full');
        panel.classList.add('translate-x-0');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = 'hidden';

        fetch(`../Discover/get_neighbor_profile.php?id=${cardData.userId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    sportsContainer.innerHTML = `<div class="text-gray-500 dark:text-gray-400 text-[13px]">No additional details available.</div>`;
                    return;
                }

                if (currentOpenUserId !== cardData.userId) return;

                matchesStatEl.textContent = data.matches_count || '0';
                joinedStatEl.textContent = data.user.member_since || 'Recent';

                if (data.sports && data.sports.length > 0) {
                    sportsCountEl.textContent = `${data.sports.length} sport${data.sports.length > 1 ? 's' : ''}`;
                    let sHtml = '';
                    data.sports.forEach(s => {
                        sHtml += `
                            <div class="flex justify-between items-center px-3.5 py-2.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl">
                                <div>
                                    <div class="font-semibold text-sm text-gray-900 dark:text-gray-50">${s.sport_name}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        ${s.experience_years ? s.experience_years + ' yrs exp' : 'Recreational'}${s.preferred_position ? ' &bull; ' + s.preferred_position : ''}
                                    </div>
                                </div>
                                <span class="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-[10px] bg-nd-green/10 text-nd-green border border-nd-green/25">
                                    ${s.skill_level || 'Active'}
                                </span>
                            </div>
                        `;
                    });
                    sportsContainer.innerHTML = sHtml;
                } else {
                    sportsCountEl.textContent = 'None yet';
                    sportsContainer.innerHTML = `
                        <div class="p-3.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-center text-gray-500 dark:text-gray-400 text-[13px]">
                            No specific sports listed yet.
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error(err);
                sportsContainer.innerHTML = `<div class="text-gray-500 dark:text-gray-400 text-[13px]">Failed to load sports details.</div>`;
            });
    }

    function closeSidePanel() {
        panel.classList.add('translate-x-full');
        panel.classList.remove('translate-x-0');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = '';
        currentOpenUserId = null;
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.nd-view-profile-btn');
        if (btn) {
            e.preventDefault();
            const cardData = {
                userId: parseInt(btn.getAttribute('data-user-id')),
                name: btn.getAttribute('data-name'),
                username: btn.getAttribute('data-username'),
                location: btn.getAttribute('data-location'),
                avatar: btn.getAttribute('data-avatar'),
                initial: btn.getAttribute('data-initial'),
                gradient: btn.getAttribute('data-gradient'),
                sports: btn.getAttribute('data-sports')
            };
            openSidePanel(cardData);
        }
    });

    closeBtn.addEventListener('click', closeSidePanel);
    backdrop.addEventListener('click', closeSidePanel);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !panel.classList.contains('translate-x-full')) {
            closeSidePanel();
        }
    });
})();
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
