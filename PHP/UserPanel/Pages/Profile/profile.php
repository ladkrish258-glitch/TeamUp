<?php
$page = 'profile';
$pageTitle = 'My Profile';
$feedMaxWidth = 'max-w-[1100px]';
require_once dirname(__DIR__) . '/Components/Header/header.php';

// Strictly display the logged-in user's own profile
$userId = (int)($_SESSION['user_id'] ?? 0);

// Fetch current user full details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userProfile) {
    echo '<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-12 text-center rounded-2xl">
            <div class="text-4xl mb-3">👤</div>
            <h3 class="m-0 mb-2 text-gray-900 dark:text-gray-50 font-bold text-lg">Profile Not Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-5">Unable to load your profile details.</p>
          </div>';
    require_once dirname(__DIR__) . '/Components/Footer/footer.php';
    exit;
}

$initial = strtoupper(substr($userProfile['full_name'] ?? 'U', 0, 1));

// Resolve avatar photo
$avatarPhoto = null;
if (!empty($userProfile['profile_photo'])) {
    $raw = trim($userProfile['profile_photo']);
    if (strpos($raw, 'http') === 0 || strpos($raw, '/') === 0 || strpos($raw, '../') === 0) {
        $avatarPhoto = strpos($raw, '/Team-Up/') === 0 ? '../../../../' . substr($raw, 9) : $raw;
    } else {
        $avatarPhoto = '../../../../images/User/Profile/' . ltrim($raw, '/');
    }
}

// Fetch user's sports
$stmtSports = $pdo->prepare("
    SELECT s.sport_name, us.skill_level, us.experience_years, us.preferred_position 
    FROM user_sports us 
    JOIN sports s ON us.sport_id = s.sport_id 
    WHERE us.user_id = ?
    ORDER BY us.experience_years DESC
");
$stmtSports->execute([$userId]);
$userSports = $stmtSports->fetchAll(PDO::FETCH_ASSOC);

// Fetch matches hosted by this user
$stmtHosted = $pdo->prepare("
    SELECT m.*, s.sport_name, g.ground_name 
    FROM matches m 
    LEFT JOIN sports s ON m.sports_id = s.sport_id 
    LEFT JOIN grounds g ON m.ground_id = g.ground_id 
    WHERE m.created_by = ? 
    ORDER BY m.match_date DESC, m.start_time DESC
");
$stmtHosted->execute([$userId]);
$hostedMatches = $stmtHosted->fetchAll(PDO::FETCH_ASSOC);
$matchesCount = count($hostedMatches);

// Top skill calculation
$topSkill = 'Active';
if (!empty($userSports)) {
    $skills = array_column($userSports, 'skill_level');
    if (in_array('Professional', $skills)) $topSkill = 'Professional';
    elseif (in_array('Advanced', $skills)) $topSkill = 'Advanced';
    elseif (in_array('Intermediate', $skills)) $topSkill = 'Intermediate';
    elseif (in_array('Beginner', $skills)) $topSkill = 'Beginner';
}
?>

<!-- PROFILE HERO CARD -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] overflow-hidden mb-6 shadow-md">
    <!-- Cover Banner -->
    <div class="h-[180px] relative flex items-start justify-end px-5 py-4" style="background: linear-gradient(135deg, #064e3b 0%, #047857 45%, #1e3a5f 100%);">
        <div class="bg-black/35 backdrop-blur-md text-slate-200 text-xs font-semibold px-3.5 py-1.5 rounded-full border border-white/15 inline-flex items-center gap-1.5">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="#10b981"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <span>Verified Athlete &bull; TeamUp</span>
        </div>
    </div>

    <!-- Main Identity & Actions -->
    <div class="px-7 pb-6 relative">
        <div class="relative w-[110px] h-[110px] -mt-[55px] mb-4">
            <?php if (!empty($avatarPhoto)): ?>
                <img src="<?= htmlspecialchars($avatarPhoto) ?>" 
                     alt="<?= htmlspecialchars($userProfile['full_name']) ?>" 
                     class="w-[110px] h-[110px] rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-lg"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="hidden w-[110px] h-[110px] rounded-full border-4 border-white dark:border-gray-800 shadow-lg items-center justify-center text-[42px] font-extrabold text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <?= $initial ?>
                </div>
            <?php else: ?>
                <div class="w-[110px] h-[110px] rounded-full border-4 border-white dark:border-gray-800 shadow-lg flex items-center justify-center text-[42px] font-extrabold text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <?= $initial ?>
                </div>
            <?php endif; ?>
            <a href="../Settings/settings.php" title="Change profile picture"
               class="absolute bottom-0.5 right-0.5 w-8 h-8 rounded-full bg-nd-green text-white flex items-center justify-center border-[3px] border-white dark:border-gray-800 shadow hover:scale-110 transition-transform no-underline">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </a>
        </div>

        <div class="flex justify-between items-start flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="m-0 text-[26px] font-extrabold text-gray-900 dark:text-gray-50 tracking-tight">
                        <?= htmlspecialchars($userProfile['full_name']) ?>
                    </h1>
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-nd-green text-white" title="Verified Profile">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </span>
                </div>

                <div class="flex items-center gap-3 mt-1.5 text-sm text-gray-500 dark:text-gray-400 flex-wrap">
                    <span class="font-semibold text-gray-900 dark:text-gray-100">@<?= htmlspecialchars($userProfile['username']) ?></span>
                    <span>&bull;</span>
                    <span class="inline-flex items-center gap-1">
                        <svg viewBox="0 0 24 24" width="14" height="14" class="fill-nd-green"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        <?= htmlspecialchars($userProfile['area'] ?? 'Local') ?><?= !empty($userProfile['city']) ? ' (' . htmlspecialchars($userProfile['city']) . ')' : '' ?>
                    </span>
                    <span>&bull;</span>
                    <span>Member since <?= !empty($userProfile['created_at']) ? date('M Y', strtotime($userProfile['created_at'])) : 'Recent' ?></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2.5 flex-wrap">
                <a href="../Settings/settings.php" class="inline-flex items-center gap-1.5 text-[13.5px] px-[18px] py-2 rounded-full font-semibold bg-nd-green hover:bg-nd-green-hover text-white no-underline transition-colors">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    <span>Edit Profile</span>
                </a>
                <a href="../Post/post.php" class="inline-flex items-center gap-1.5 text-[13.5px] px-[18px] py-2 rounded-full font-semibold bg-transparent text-nd-green border border-nd-green hover:bg-nd-green hover:text-white no-underline transition-colors">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    <span>Host a Match</span>
                </a>
            </div>
        </div>

        <!-- Quick Stats Strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-gray-700">
            <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center hover:-translate-y-0.5 transition-transform">
                <div class="text-xl font-extrabold text-gray-900 dark:text-gray-50"><?= (int)$matchesCount ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Matches Hosted</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center hover:-translate-y-0.5 transition-transform">
                <div class="text-xl font-extrabold text-gray-900 dark:text-gray-50"><?= count($userSports) ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Active Sports</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center hover:-translate-y-0.5 transition-transform">
                <div class="text-xl font-extrabold text-nd-green"><?= htmlspecialchars($topSkill) ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Top Skill Level</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center hover:-translate-y-0.5 transition-transform">
                <div class="text-base font-extrabold text-gray-900 dark:text-gray-50"><?= htmlspecialchars($userProfile['area'] ?? 'Surat') ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Home Neighborhood</div>
            </div>
        </div>
    </div>
</div>

<!-- TWO COLUMN MAIN CONTENT -->
<div class="grid grid-cols-1 lg:grid-cols-[1.6fr_1.1fr] gap-6 items-start">

    <!-- LEFT COLUMN: SPORTS & ACTIVITY -->
    <div class="flex flex-col gap-6">

        <!-- Sports & Skills Card -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] px-6 py-5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🏆</span>
                    <h3 class="m-0 text-gray-900 dark:text-gray-50 text-lg font-bold">My Sports &amp; Skills</h3>
                </div>
                <a href="../Settings/settings.php" class="inline-flex items-center gap-1 px-3.5 py-1.5 text-[13px] rounded-full font-semibold border border-nd-green text-nd-green hover:bg-nd-green hover:text-white no-underline transition-colors">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    <span>Manage Sports</span>
                </a>
            </div>

            <?php if(!empty($userSports)): ?>
                <div class="flex flex-col gap-3">
                    <?php foreach($userSports as $us): ?>
                        <div class="flex items-center justify-between px-4 py-3.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[14px] hover:border-nd-green/40 hover:shadow-sm transition-all">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-nd-green/10 text-nd-green flex items-center justify-center text-xl">
                                    ⚽
                                </div>
                                <div>
                                    <div class="font-bold text-[15px] text-gray-900 dark:text-gray-50">
                                        <?= htmlspecialchars($us['sport_name']) ?>
                                    </div>
                                    <div class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        <?= htmlspecialchars($us['experience_years']) ?> years experience
                                        <?= !empty($us['preferred_position']) ? ' &bull; ' . htmlspecialchars($us['preferred_position']) : '' ?>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="bg-nd-green/15 text-nd-green px-3.5 py-1 rounded-full text-[12.5px] font-bold border border-nd-green/30">
                                    <?= htmlspecialchars($us['skill_level']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 px-4 bg-gray-100 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-[14px]">
                    <div class="text-[32px] mb-2">🎯</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-50 mb-1">No sports added yet</div>
                    <p class="text-gray-500 dark:text-gray-400 text-[13.5px] m-0 mb-4">Add your favorite sports and skill levels so neighbors can invite you to play.</p>
                    <a href="../Settings/settings.php" class="inline-flex items-center px-4 py-2 rounded-full font-semibold text-[13px] bg-nd-green hover:bg-nd-green-hover text-white no-underline transition-colors">Add a Sport</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Hosted Matches Card -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] px-6 py-5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📅</span>
                    <h3 class="m-0 text-gray-900 dark:text-gray-50 text-lg font-bold">My Hosted Matches</h3>
                </div>
                <a href="../Post/post.php" class="inline-flex items-center gap-1 px-3.5 py-1.5 text-[13px] rounded-full font-semibold border border-nd-green text-nd-green hover:bg-nd-green hover:text-white no-underline transition-colors">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    <span>Create Match</span>
                </a>
            </div>

            <?php if(!empty($hostedMatches)): ?>
                <div class="flex flex-col gap-3">
                    <?php foreach($hostedMatches as $match): 
                        $matchDateStr = date('D, d M Y', strtotime($match['match_date']));
                        $timeStr = date('g:i A', strtotime($match['start_time'])) . ' – ' . date('g:i A', strtotime($match['end_time']));
                    ?>
                        <div class="p-4 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[14px] hover:border-nd-green/40 hover:shadow-sm transition-all">
                            <div class="flex justify-between items-start gap-3 mb-2.5">
                                <div>
                                    <div class="font-bold text-base text-gray-900 dark:text-gray-50">
                                        <?= htmlspecialchars($match['title'] ?? ($match['sport_name'] . ' Match')) ?>
                                    </div>
                                    <div class="text-[13px] text-nd-green font-semibold mt-0.5">
                                        <?= htmlspecialchars($match['sport_name'] ?? 'Custom Sport') ?> &bull; <?= htmlspecialchars($match['skill_level'] ?? 'All levels') ?>
                                    </div>
                                </div>
                                <span class="bg-nd-green/15 text-nd-green px-3 py-1 rounded-full text-xs font-bold">
                                    <?= htmlspecialchars($match['match_status'] ?? 'Open') ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[13px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-3.5 py-2.5 rounded-[10px] border border-gray-200 dark:border-gray-700">
                                <div><strong class="text-gray-900 dark:text-gray-200">📅 Date:</strong> <?= $matchDateStr ?></div>
                                <div><strong class="text-gray-900 dark:text-gray-200">⏰ Time:</strong> <?= $timeStr ?></div>
                                <div><strong class="text-gray-900 dark:text-gray-200">👥 Players:</strong> <?= (int)$match['current_players'] ?> / <?= (int)$match['max_players'] ?></div>
                                <div><strong class="text-gray-900 dark:text-gray-200">📍 Venue:</strong> <?= htmlspecialchars($match['ground_name'] ?? 'Local Ground') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-7 px-4 bg-gray-100 dark:bg-gray-900 rounded-[14px] border border-gray-200 dark:border-gray-700">
                    <div class="text-[30px] mb-1.5">📢</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-50 mb-1">You haven't hosted any matches yet</div>
                    <p class="text-gray-500 dark:text-gray-400 text-[13px] m-0 mb-3.5">Post a match to invite neighbors to join your upcoming game.</p>
                    <a href="../Post/post.php" class="inline-flex items-center px-4 py-2 rounded-full font-semibold text-[13px] bg-nd-green hover:bg-nd-green-hover text-white no-underline transition-colors">Host a Match</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT COLUMN: COMMUNITY DETAILS & HIGHLIGHTS -->
    <div class="flex flex-col gap-6">

        <!-- About Me Card -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] px-6 py-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">👤</span>
                <h3 class="m-0 text-gray-900 dark:text-gray-50 text-lg font-bold">About Me</h3>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed m-0 mb-4">
                Passionate sports enthusiast playing actively in <strong class="text-gray-900 dark:text-gray-50"><?= htmlspecialchars($userProfile['area'] ?? 'Surat') ?></strong>. Looking forward to connecting with fellow players, scheduling weekly games, and building local sports teams.
            </p>

            <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[14px] px-4 py-3.5">
                <div class="flex justify-between py-2.5 border-b border-gray-200 dark:border-gray-700 text-[13.5px]">
                    <span class="text-gray-500 dark:text-gray-400">Neighborhood</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-50"><?= htmlspecialchars($userProfile['area'] ?? 'Pal') ?></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-gray-200 dark:border-gray-700 text-[13.5px]">
                    <span class="text-gray-500 dark:text-gray-400">City &bull; State</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-50"><?= htmlspecialchars($userProfile['city'] ?? 'Surat') ?>, <?= htmlspecialchars($userProfile['state'] ?? 'Gujarat') ?></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-gray-200 dark:border-gray-700 text-[13.5px]">
                    <span class="text-gray-500 dark:text-gray-400">Country</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-50"><?= htmlspecialchars($userProfile['country'] ?? 'India') ?></span>
                </div>
                <div class="flex justify-between py-2.5 border-b border-gray-200 dark:border-gray-700 text-[13.5px]">
                    <span class="text-gray-500 dark:text-gray-400">Member Since</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-50"><?= !empty($userProfile['created_at']) ? date('F Y', strtotime($userProfile['created_at'])) : 'July 2026' ?></span>
                </div>
                <div class="flex justify-between pt-2.5 text-[13.5px]">
                    <span class="text-gray-500 dark:text-gray-400">Profile Visibility</span>
                    <span class="font-semibold text-nd-green">Public to Neighbors</span>
                </div>
            </div>
        </div>

        <!-- Player Badges & Achievements -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] px-6 py-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">🎖️</span>
                <h3 class="m-0 text-gray-900 dark:text-gray-50 text-lg font-bold">Community Badges</h3>
            </div>

            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3 px-3.5 py-2.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="text-2xl">🤝</div>
                    <div>
                        <div class="font-bold text-[13.5px] text-gray-900 dark:text-gray-50">Verified Resident</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Registered neighborhood sports member</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-3.5 py-2.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="text-2xl">🏅</div>
                    <div>
                        <div class="font-bold text-[13.5px] text-gray-900 dark:text-gray-50">Match Organizer</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Hosted <?= (int)$matchesCount ?> community game fixture<?= $matchesCount != 1 ? 's' : '' ?></div>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-3.5 py-2.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="text-2xl">⭐</div>
                    <div>
                        <div class="font-bold text-[13.5px] text-gray-900 dark:text-gray-50"><?= htmlspecialchars($topSkill) ?> Athlete</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Ranked based on verified sports experience</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
