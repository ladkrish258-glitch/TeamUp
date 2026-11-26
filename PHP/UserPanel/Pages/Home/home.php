<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (($_SERVER['REQUEST_METHOD'] ?? '') == 'POST' && isset($_POST['post_content'])) {
    require_once dirname(__DIR__) . '/Components/Connection/db.php';
    $content = trim($_POST['post_content']);
    $userId = $_SESSION['user_id'];
    $photoUrl = null;

    if (!empty($_FILES['post_photo']['name']) && $_FILES['post_photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = dirname(__DIR__, 4) . '/images/User/Post/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
            chmod($targetDir, 0777);
        }
        $fileName = 'post_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower(pathinfo($_FILES["post_photo"]["name"], PATHINFO_EXTENSION));
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["post_photo"]["tmp_name"], $targetFilePath)) {
            chmod($targetFilePath, 0666);
            $photoUrl = '../../../../images/User/Post/' . $fileName;
        } else {
            error_log("Failed to move uploaded file to: " . $targetFilePath);
        }
    }

    if (!empty($content) || $photoUrl) {
        $stmt = $pdo->prepare("INSERT INTO user_posts (user_id, content, photo_url) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $content, $photoUrl]);
    }
    header("Location: ../Home/home.php");
    exit;
}

$page = 'home';
$pageTitle = 'Home Feed';
$feedMaxWidth = 'max-w-[1140px]';
require_once dirname(__DIR__) . '/Components/Header/header.php';

// Fetch upcoming match posts (exclude past matches and non-match posts)
$stmt = $pdo->query("
    SELECT
        p.post_id as id, p.content as description, p.photo_url, p.created_at,
        m.match_id, m.title, m.match_date, m.start_time, m.end_time, m.skill_level,
        m.match_status, m.max_players, m.current_players,
        s.sport_name, g.ground_name, g.city,
        u.user_id, u.full_name as creator_name, u.profile_photo
    FROM user_posts p
    INNER JOIN matches m ON m.post_id = p.post_id
    LEFT JOIN sports s ON m.sports_id = s.sport_id
    LEFT JOIN grounds g ON m.ground_id = g.ground_id
    LEFT JOIN users u ON p.user_id = u.user_id
    WHERE m.match_date IS NOT NULL
      AND TIMESTAMP(m.match_date, COALESCE(m.end_time, m.start_time, '23:59:59')) >= NOW()
    ORDER BY m.match_date ASC, m.start_time ASC, p.created_at DESC
");
$feedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check which matches current user has joined
$userJoinedMatchIds = [];
if (!empty($_SESSION['user_id'])) {
    $joinedStmt = $pdo->prepare("SELECT match_id FROM match_participants WHERE user_id = ?");
    $joinedStmt->execute([(int)$_SESSION['user_id']]);
    $userJoinedMatchIds = $joinedStmt->fetchAll(PDO::FETCH_COLUMN);
}

function getSportIcon($sportName) {
    $s = strtolower((string)$sportName);
    if (strpos($s, 'cricket') !== false) return '🏏';
    if (strpos($s, 'football') !== false || strpos($s, 'soccer') !== false) return '⚽';
    if (strpos($s, 'basket') !== false) return '🏀';
    if (strpos($s, 'badminton') !== false) return '🏸';
    if (strpos($s, 'tennis') !== false) return '🎾';
    if (strpos($s, 'volley') !== false) return '🏐';
    return '🏆';
}
?>

<!-- Feed Header -->
<div class="mb-2.5 flex justify-between items-end flex-wrap gap-3">
    <div>
        <h2 class="m-0 mb-1 text-2xl font-bold text-gray-900 dark:text-gray-50">Home Feed</h2>
        <p class="m-0 text-sm text-gray-500 dark:text-gray-400">Recent match invites and updates from neighbors nearby.</p>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
        <div class="text-[13px] text-gray-500 dark:text-gray-400 font-medium">
            Showing <span id="feed-showing-range">0</span> of <span id="feed-total-count"><?= count($feedItems) ?></span>
        </div>
    </div>
</div>

<!-- Feed Posts 2-Column Grid (10 per page) -->
<div id="feed-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">
<?php foreach ($feedItems as $item): ?>

    <article class="nd-feed-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-[22px] shadow-sm dark:shadow-lg transition-all duration-200 hover:border-nd-green/50 flex flex-col h-full">

        <!-- Card Header: User Info & Status Badge -->
        <div class="flex justify-between items-start mb-3 gap-3">
            <div class="flex items-center gap-3">
                <?php if (!empty($item['profile_photo'])):
                    $creatorAvatar = trim($item['profile_photo']);
                    if (strpos($creatorAvatar, '/Team-Up/') === 0) {
                        $creatorAvatar = '../../../../' . substr($creatorAvatar, 9);
                    } elseif (strpos($creatorAvatar, 'http') !== 0 && strpos($creatorAvatar, '/') !== 0 && strpos($creatorAvatar, '../') !== 0) {
                        $creatorAvatar = '../../../../images/User/Profile/' . ltrim($creatorAvatar, '/');
                    }
                ?>
                    <img src="<?= htmlspecialchars($creatorAvatar) ?>" 
                         alt="<?= htmlspecialchars($item['creator_name'] ?? 'User') ?>" 
                         class="w-11 h-11 rounded-full object-cover shrink-0"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-11 h-11 rounded-full hidden items-center justify-center text-white font-semibold text-[17px] shrink-0" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <?= strtoupper(substr($item['creator_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php else: ?>
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-semibold text-[17px] shrink-0" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <?= strtoupper(substr($item['creator_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <div class="font-semibold text-[15px] text-gray-900 dark:text-gray-50 leading-snug">
                        <?= htmlspecialchars($item['creator_name'] ?? 'Community Member') ?>
                    </div>
                    <div class="text-[12.5px] text-gray-500 dark:text-gray-400 mt-0.5">
                        <?= htmlspecialchars(date('M d, Y', strtotime($item['created_at']))) ?> &bull; <?= htmlspecialchars(date('g:i A', strtotime($item['created_at']))) ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($item['match_id'])): ?>
                <?php if (($item['match_status'] ?? 'Open') === 'Open'): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide bg-emerald-500/15 text-emerald-500 border border-emerald-500/30 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        <?= htmlspecialchars($item['match_status'] ?? 'Open') ?>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide bg-gray-400/15 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        <?= htmlspecialchars($item['match_status'] ?? 'Open') ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Post Text Description -->
        <?php if (!empty($item['description'])): ?>
            <div class="text-[15px] leading-relaxed mb-3.5 text-gray-900 dark:text-gray-100 break-words">
                <?= nl2br(htmlspecialchars($item['description'])) ?>
            </div>
        <?php endif; ?>

        <!-- Post Image -->
        <?php if (!empty($item['photo_url'])): 
            $photoPath = trim($item['photo_url']);
            if (strpos($photoPath, '/Team-Up/') === 0) {
                $photoPath = '../../../../' . substr($photoPath, 9);
            } elseif (strpos($photoPath, 'http') === 0 || strpos($photoPath, '/') === 0 || strpos($photoPath, '../') === 0) {
                // full or relative path
            } elseif (strpos($photoPath, 'uploads/posts/') === 0) {
                $photoPath = '../../../../images/User/Post/' . basename($photoPath);
            } else {
                $photoPath = '../../../../images/User/Post/' . ltrim($photoPath, '/');
            }
        ?>
            <div class="mb-3.5 rounded-xl overflow-hidden bg-black/20 max-h-[440px]">
                <img src="<?= htmlspecialchars($photoPath) ?>" 
                     class="w-full max-h-[440px] object-contain block" 
                     alt="Post photo" 
                     onerror="this.parentElement.style.display='none';">
            </div>
        <?php endif; ?>

        <!-- Match Details Card Box -->
        <?php if (!empty($item['match_id'])): ?>
            <div class="nd-match-box mt-auto bg-black/[0.025] dark:bg-gray-900/70 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                <div class="flex items-center justify-between flex-wrap gap-2.5 mb-3.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 bg-nd-green/15 text-emerald-500 border border-nd-green/30 px-3 py-1 rounded-lg text-[13px] font-semibold">
                            <?= getSportIcon($item['sport_name'] ?? '') ?> <?= htmlspecialchars($item['sport_name'] ?? 'Custom Sport') ?>
                        </span>
                        <?php if (!empty($item['skill_level'])): ?>
                            <span class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 px-2.5 py-1 rounded-lg text-xs font-medium">
                                <?= htmlspecialchars($item['skill_level']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="nd-players-pill inline-flex items-center gap-1.5 text-[13px] font-semibold text-gray-500 dark:text-gray-400">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        <span><?= (int)$item['current_players'] ?> / <?= (int)$item['max_players'] ?> Players</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="flex items-start gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2.5 rounded-lg">
                        <div class="text-base leading-none mt-0.5">📅</div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Date</div>
                            <div class="text-[13.5px] font-semibold text-gray-900 dark:text-gray-50 mt-0.5"><?= date('d M Y', strtotime($item['match_date'])) ?></div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2.5 rounded-lg">
                        <div class="text-base leading-none mt-0.5">⏰</div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Time</div>
                            <div class="text-[13.5px] font-semibold text-gray-900 dark:text-gray-50 mt-0.5">
                                <?= date('g:i A', strtotime($item['start_time'])) ?> &ndash; <?= date('g:i A', strtotime($item['end_time'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 flex items-start gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2.5 rounded-lg">
                        <div class="text-base leading-none mt-0.5">📍</div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Ground / Venue</div>
                            <div class="text-[13.5px] font-semibold text-gray-900 dark:text-gray-50 mt-0.5">
                                <?= !empty($item['ground_name']) ? htmlspecialchars($item['ground_name']) : 'Venue to be determined' ?>
                                <?= !empty($item['city']) ? '&bull; ' . htmlspecialchars($item['city']) : '' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                    $isJoined = in_array((int)$item['match_id'], $userJoinedMatchIds);
                ?>
                <div class="mt-3.5 flex items-center justify-between flex-wrap gap-2.5">
                    <div class="match-actions-wrap flex items-center gap-2.5 flex-wrap" data-match-id="<?= (int)$item['match_id'] ?>">
                        <?php if ($isJoined): ?>
                            <button type="button" class="nd-btn-joined inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm border border-emerald-500 text-emerald-500 hover:border-red-500 hover:text-red-500 hover:bg-red-500/10 cursor-pointer transition-colors group" title="Click to leave match" onclick="leaveMatch(<?= (int)$item['match_id'] ?>, this)">
                                <span class="group-hover:hidden">Joined ✓</span>
                                <span class="hidden group-hover:inline">Leave Match &times;</span>
                            </button>
                            <button type="button" class="nd-btn-players inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 px-4 py-2 rounded-full font-semibold text-[13.5px] cursor-pointer transition-colors" onclick="openJoinedPlayersModal(<?= (int)$item['match_id'] ?>)">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                <span>Joined Players</span>
                            </button>
                        <?php elseif ($item['match_status'] === 'Open'): ?>
                            <button type="button" class="nd-btn-join inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors" onclick="joinMatch(<?= (int)$item['match_id'] ?>, this)">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" class="mr-1"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                <span>Interested to Play</span>
                            </button>
                            <button type="button" class="nd-btn-players hidden items-center gap-1.5 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 px-4 py-2 rounded-full font-semibold text-[13.5px] cursor-pointer transition-colors" onclick="openJoinedPlayersModal(<?= (int)$item['match_id'] ?>)">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                <span>Joined Players</span>
                            </button>
                        <?php else: ?>
                            <button type="button" class="inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm border border-gray-300 dark:border-gray-600 text-gray-500 bg-transparent opacity-60 cursor-not-allowed" disabled>
                                <?= htmlspecialchars($item['match_status']) ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <span class="slots-remaining-text text-[13px] text-gray-500 dark:text-gray-400">
                        <?= max(0, (int)$item['max_players'] - (int)$item['current_players']) ?> slots remaining
                    </span>
                </div>
            </div>
        <?php endif; ?>

    </article>

<?php endforeach; ?>
</div>

<!-- Pagination Navigation -->
<div id="feed-pagination-controls" class="flex items-center justify-center gap-1.5 my-6 flex-wrap"></div>

<?php if (empty($feedItems)): ?>
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center px-6 py-12 rounded-2xl">
        <div class="text-4xl mb-3">🏟️</div>
        <h3 class="text-gray-900 dark:text-gray-50 m-0 mb-2 text-lg font-bold">No upcoming matches in your area</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm m-0 mb-5">Only upcoming match posts are shown here. Be the first neighbor to post a new game!</p>
        <a href="../Post/post.php" class="inline-flex items-center px-5 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white no-underline transition-colors">Create a Post</a>
    </div>
<?php endif; ?>

<!-- Joined Players Modal -->
<div id="joined-players-modal" class="hidden fixed inset-0 bg-black/65 backdrop-blur-sm z-[2000] items-center justify-center p-4" onclick="if(event.target === this) closeJoinedPlayersModal()">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl w-full max-w-[460px] max-h-[82vh] flex flex-col shadow-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="m-0 text-base font-bold text-gray-900 dark:text-gray-50 flex items-center gap-2">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                <span id="modal-match-title">Joined Players</span>
            </h3>
            <button type="button" class="bg-transparent border-0 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white text-2xl leading-none cursor-pointer px-2 py-1 rounded-lg transition-colors" onclick="closeJoinedPlayersModal()">&times;</button>
        </div>
        <div class="p-4 px-5 overflow-y-auto flex-1" id="modal-players-list">
            <!-- Dynamically populated via AJAX -->
        </div>
    </div>
</div>

<script>
function joinMatch(matchId, btn) {
    if (!btn) return;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>Joining...</span>';

    const formData = new FormData();
    formData.append('action', 'join');
    formData.append('match_id', matchId);

    fetch('../Match/join_match.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.className = 'nd-btn-joined inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm border border-emerald-500 text-emerald-500 hover:border-red-500 hover:text-red-500 hover:bg-red-500/10 cursor-pointer transition-colors group';
            btn.title = 'Click to leave match';
            btn.innerHTML = '<span class="group-hover:hidden">Joined ✓</span><span class="hidden group-hover:inline">Leave Match &times;</span>';
            btn.disabled = false;
            btn.onclick = function() { leaveMatch(matchId, this); };

            const parent = btn.closest('.match-actions-wrap');
            if (parent) {
                const playersBtn = parent.querySelector('.nd-btn-players');
                if (playersBtn) {
                    playersBtn.classList.remove('hidden');
                    playersBtn.classList.add('inline-flex');
                }
                const slotsEl = parent.parentElement.querySelector('.slots-remaining-text');
                if (slotsEl && typeof data.slots_remaining !== 'undefined') {
                    slotsEl.textContent = data.slots_remaining + ' slots remaining';
                }
            }

            const card = btn.closest('.nd-match-box');
            if (card && typeof data.current_players !== 'undefined') {
                const countPill = card.querySelector('.nd-players-pill span');
                if (countPill) {
                    countPill.textContent = data.current_players + ' / ' + data.max_players + ' Players';
                }
            }

            const modal = document.getElementById('joined-players-modal');
            if (modal && !modal.classList.contains('hidden')) {
                openJoinedPlayersModal(matchId);
            }
        } else {
            alert(data.error || 'Failed to join match');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function leaveMatch(matchId, btn) {
    if (!btn) return;

    if (!confirm('Are you sure you want to leave this match?')) {
        return;
    }

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span>Leaving...</span>';

    const formData = new FormData();
    formData.append('action', 'leave');
    formData.append('match_id', matchId);

    fetch('../Match/join_match.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.className = 'nd-btn-join inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors';
            btn.title = '';
            btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" class="mr-1"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg><span>Interested to Play</span>';
            btn.disabled = false;
            btn.onclick = function() { joinMatch(matchId, this); };

            const parent = btn.closest('.match-actions-wrap');
            if (parent) {
                const slotsEl = parent.parentElement.querySelector('.slots-remaining-text');
                if (slotsEl && typeof data.slots_remaining !== 'undefined') {
                    slotsEl.textContent = data.slots_remaining + ' slots remaining';
                }
            }

            const card = btn.closest('.nd-match-box');
            if (card && typeof data.current_players !== 'undefined') {
                const countPill = card.querySelector('.nd-players-pill span');
                if (countPill) {
                    countPill.textContent = data.current_players + ' / ' + data.max_players + ' Players';
                }
            }

            const modal = document.getElementById('joined-players-modal');
            if (modal && !modal.classList.contains('hidden')) {
                openJoinedPlayersModal(matchId);
            }
        } else {
            alert(data.error || 'Failed to leave match');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred while leaving the match.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function openJoinedPlayersModal(matchId) {
    const modal = document.getElementById('joined-players-modal');
    const titleEl = document.getElementById('modal-match-title');
    const listEl = document.getElementById('modal-players-list');
    
    titleEl.textContent = 'Joined Players';
    listEl.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">Loading players...</div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    fetch('../Match/join_match.php?action=get_players&match_id=' + matchId)
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            listEl.innerHTML = '<div class="text-center py-5 text-red-500">' + (data.error || 'Could not load players') + '</div>';
            return;
        }

        if (data.title) {
            titleEl.textContent = data.title + ' (' + data.current_players + '/' + data.max_players + ')';
        }

        if (!data.players || data.players.length === 0) {
            listEl.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No players have joined this match yet.</div>';
            return;
        }

        let html = '';
        data.players.forEach(p => {
            const initial = (p.full_name || 'U').charAt(0).toUpperCase();
            let photoUrl = p.profile_photo ? String(p.profile_photo).trim() : '';
            if (photoUrl.startsWith('/Team-Up/')) {
                photoUrl = '../../../' + photoUrl.slice(9);
            } else if (photoUrl && !photoUrl.startsWith('http') && !photoUrl.startsWith('/') && !photoUrl.startsWith('../')) {
                photoUrl = '../../../../images/User/Profile/' + photoUrl.replace(/^\/+/, '');
            }
            const avatarHtml = photoUrl 
                ? '<img src="' + escapeHtml(photoUrl) + '" class="w-10 h-10 rounded-full object-cover shrink-0" onerror="this.outerHTML=\'<div class=\\\'w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-base shrink-0\\\' style=\\\'background:linear-gradient(135deg,#10b981,#059669);\\\'>' + initial + '</div>\'">'
                : '<div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-base shrink-0" style="background:linear-gradient(135deg,#10b981,#059669);">' + initial + '</div>';

            const badgeHtml = p.is_host 
                ? '<span class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-amber-500/15 text-amber-500 border border-amber-500/30">Host / Organizer</span>'
                : '<span class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">Joined ✓</span>';

            html += `
                <div class="flex items-center justify-between py-2.5 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                    <div class="flex items-center gap-3">
                        ${avatarHtml}
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-gray-50">${escapeHtml(p.full_name)}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">${p.city ? escapeHtml(p.city) + ' &bull; ' : ''}${escapeHtml(p.joined_at)}</div>
                        </div>
                    </div>
                    <div>${badgeHtml}</div>
                </div>
            `;
        });

        listEl.innerHTML = html;
    })
    .catch(err => {
        listEl.innerHTML = '<div class="text-center py-5 text-red-500">Failed to load player list.</div>';
    });
}

function closeJoinedPlayersModal() {
    const modal = document.getElementById('joined-players-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeJoinedPlayersModal();
    }
});

// Grid Pagination (10 cards per page)
(function() {
    const PAGE_SIZE = 10;
    let currentPage = 1;

    const cards = Array.from(document.querySelectorAll('#feed-grid .nd-feed-card'));
    const paginationEl = document.getElementById('feed-pagination-controls');
    const rangeEl = document.getElementById('feed-showing-range');
    const totalEl = document.getElementById('feed-total-count');
    const gridEl = document.getElementById('feed-grid');

    const btnBase = 'min-w-[38px] h-[38px] px-3 rounded-[10px] border font-semibold text-[13.5px] inline-flex items-center justify-center transition-all select-none';
    const btnIdle = 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer';
    const btnActive = 'border-nd-green bg-nd-green text-white shadow-md cursor-pointer';
    const btnDisabled = 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 opacity-40 cursor-not-allowed pointer-events-none';

    function renderFeedPage() {
        const total = cards.length;
        const totalPages = Math.ceil(total / PAGE_SIZE) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * PAGE_SIZE;
        const end = Math.min(start + PAGE_SIZE, total);

        cards.forEach((c, idx) => {
            if (idx >= start && idx < end) {
                c.classList.remove('hidden');
                c.classList.add('flex');
            } else {
                c.classList.add('hidden');
                c.classList.remove('flex');
            }
        });

        if (total === 0) {
            rangeEl.textContent = '0';
            totalEl.textContent = '0';
            paginationEl.style.display = 'none';
            return;
        }

        rangeEl.textContent = `${start + 1}–${end}`;
        totalEl.textContent = total;
        paginationEl.style.display = 'flex';
        renderFeedPagination(totalPages);
    }

    function renderFeedPagination(totalPages) {
        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }

        let html = `<button class="nd-page-btn ${btnBase} ${currentPage === 1 ? btnDisabled : btnIdle}" data-page="${currentPage - 1}">&laquo; Prev</button>`;

        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            html += `<button class="nd-page-btn ${btnBase} ${btnIdle}" data-page="1">1</button>`;
            if (startPage > 2) html += `<span class="text-gray-500 dark:text-gray-400 px-1.5 font-semibold">&hellip;</span>`;
        }

        for (let p = startPage; p <= endPage; p++) {
            html += `<button class="nd-page-btn ${btnBase} ${p === currentPage ? btnActive : btnIdle}" data-page="${p}">${p}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="text-gray-500 dark:text-gray-400 px-1.5 font-semibold">&hellip;</span>`;
            html += `<button class="nd-page-btn ${btnBase} ${btnIdle}" data-page="${totalPages}">${totalPages}</button>`;
        }

        html += `<button class="nd-page-btn ${btnBase} ${currentPage === totalPages ? btnDisabled : btnIdle}" data-page="${currentPage + 1}">Next &raquo;</button>`;
        paginationEl.innerHTML = html;

        paginationEl.querySelectorAll('.nd-page-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetPage = parseInt(this.getAttribute('data-page'), 10);
                if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
                    currentPage = targetPage;
                    renderFeedPage();
                    gridEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    renderFeedPage();
})();
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
