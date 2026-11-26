<?php
$page = 'settings';
$pageTitle = 'Account & Profile Settings';
$feedMaxWidth = 'max-w-[1100px]';
require_once dirname(__DIR__) . '/Components/Header/header.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$successMsg = '';
$errorMsg = '';
$activeTab = $_GET['tab'] ?? 'profile';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. UPDATE BASIC PROFILE
    if ($action === 'update_profile') {
        $activeTab = 'profile';
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Male');

        if (empty($fullName) || empty($username)) {
            $errorMsg = 'Full name and username are required.';
        } else {
            $stmtCheck = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $stmtCheck->execute([$username, $userId]);
            if ($stmtCheck->fetch()) {
                $errorMsg = 'Username is already taken by another player.';
            } else {
                $newPhotoName = null;
                if (!empty($_FILES['profile_photo']['name']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['profile_photo']['tmp_name'];
                    $fileName = $_FILES['profile_photo']['name'];
                    $fileSize = $_FILES['profile_photo']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                    if (!in_array($fileExt, $allowedExts)) {
                        $errorMsg = 'Invalid image format. Allowed formats: JPG, PNG, WEBP, GIF.';
                    } elseif ($fileSize > 5 * 1024 * 1024) {
                        $errorMsg = 'Image file size must be less than 5MB.';
                    } else {
                        $uploadDir = dirname(__DIR__, 4) . '/images/User/Profile/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $cleanFilename = 'user_' . $userId . '_' . time() . '.' . $fileExt;
                        $destPath = $uploadDir . $cleanFilename;
                        if (move_uploaded_file($fileTmp, $destPath)) {
                            $newPhotoName = $cleanFilename;
                        } else {
                            $errorMsg = 'Failed to save uploaded profile photo.';
                        }
                    }
                }

                if (empty($errorMsg)) {
                    if ($newPhotoName) {
                        $stmtUpdate = $pdo->prepare("
                            UPDATE users 
                            SET full_name = ?, username = ?, mobile = ?, dob = ?, gender = ?, profile_photo = ?, updated_at = NOW() 
                            WHERE user_id = ?
                        ");
                        $stmtUpdate->execute([$fullName, $username, $mobile, $dob ?: null, $gender, $newPhotoName, $userId]);
                    } else {
                        $stmtUpdate = $pdo->prepare("
                            UPDATE users 
                            SET full_name = ?, username = ?, mobile = ?, dob = ?, gender = ?, updated_at = NOW() 
                            WHERE user_id = ?
                        ");
                        $stmtUpdate->execute([$fullName, $username, $mobile, $dob ?: null, $gender, $userId]);
                    }
                    $successMsg = 'Profile information updated successfully!';
                }
            }
        }
    }

    // 2. UPDATE LOCATION SETTINGS
    elseif ($action === 'update_location') {
        $activeTab = 'location';
        $area = trim($_POST['area'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $country = trim($_POST['country'] ?? 'India');

        $stmtLoc = $pdo->prepare("UPDATE users SET area = ?, city = ?, state = ?, country = ?, updated_at = NOW() WHERE user_id = ?");
        $stmtLoc->execute([$area, $city, $state, $country, $userId]);
        $successMsg = 'Neighborhood and location updated successfully!';
    }

    // 3. ADD NEW SPORT
    elseif ($action === 'add_sport') {
        $activeTab = 'sports';
        $sportId = (int)($_POST['sport_id'] ?? 0);
        $skillLevel = $_POST['skill_level'] ?? 'Beginner';
        $expYears = (int)($_POST['experience_years'] ?? 0);
        $prefPos = trim($_POST['preferred_position'] ?? '');

        if ($sportId <= 0) {
            $errorMsg = 'Please select a valid sport.';
        } else {
            $stmtCheckSport = $pdo->prepare("SELECT user_sport_id FROM user_sports WHERE user_id = ? AND sport_id = ?");
            $stmtCheckSport->execute([$userId, $sportId]);
            if ($stmtCheckSport->fetch()) {
                $errorMsg = 'You have already added this sport. You can edit its skill level below.';
            } else {
                $stmtAddSport = $pdo->prepare("
                    INSERT INTO user_sports (user_id, sport_id, skill_level, experience_years, preferred_position, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmtAddSport->execute([$userId, $sportId, $skillLevel, $expYears, $prefPos]);
                $successMsg = 'Sport added to your profile successfully!';
            }
        }
    }

    // 4. UPDATE EXISTING SPORT
    elseif ($action === 'update_sport') {
        $activeTab = 'sports';
        $userSportId = (int)($_POST['user_sport_id'] ?? 0);
        $skillLevel = $_POST['skill_level'] ?? 'Beginner';
        $expYears = (int)($_POST['experience_years'] ?? 0);
        $prefPos = trim($_POST['preferred_position'] ?? '');

        $stmtUpSport = $pdo->prepare("
            UPDATE user_sports 
            SET skill_level = ?, experience_years = ?, preferred_position = ? 
            WHERE user_sport_id = ? AND user_id = ?
        ");
        $stmtUpSport->execute([$skillLevel, $expYears, $prefPos, $userSportId, $userId]);
        $successMsg = 'Sport skills updated successfully!';
    }

    // 5. DELETE SPORT
    elseif ($action === 'delete_sport') {
        $activeTab = 'sports';
        $userSportId = (int)($_POST['user_sport_id'] ?? 0);

        $stmtDelSport = $pdo->prepare("DELETE FROM user_sports WHERE user_sport_id = ? AND user_id = ?");
        $stmtDelSport->execute([$userSportId, $userId]);
        $successMsg = 'Sport removed from your profile.';
    }

    // 6. UPDATE PASSWORD
    elseif ($action === 'update_password') {
        $activeTab = 'security';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $stmtPass = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmtPass->execute([$userId]);
        $dbPass = $stmtPass->fetchColumn();

        if (empty($currentPassword) || empty($newPassword)) {
            $errorMsg = 'Please provide both your current and new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMsg = 'New password and confirm password do not match.';
        } elseif (strlen($newPassword) < 6) {
            $errorMsg = 'New password must be at least 6 characters long.';
        } else {
            $isMatch = ($currentPassword === $dbPass) || (password_verify($currentPassword, $dbPass));
            if (!$isMatch) {
                $errorMsg = 'Incorrect current password.';
            } else {
                $stmtNewPass = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?");
                $stmtNewPass->execute([$newPassword, $userId]);
                $successMsg = 'Your password has been changed successfully!';
            }
        }
    }
}

// Fetch refreshed user details
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Resolve avatar
$avatarPhoto = null;
if (!empty($user['profile_photo'])) {
    $raw = trim($user['profile_photo']);
    if (strpos($raw, 'http') === 0 || strpos($raw, '/') === 0 || strpos($raw, '../') === 0) {
        $avatarPhoto = strpos($raw, '/Team-Up/') === 0 ? '../../../../' . substr($raw, 9) : $raw;
    } else {
        $avatarPhoto = '../../../../images/User/Profile/' . ltrim($raw, '/');
    }
}
$initial = strtoupper(substr($user['full_name'] ?? 'U', 0, 1));

// Fetch all available sports for the dropdown
$allSports = $pdo->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's registered sports
$stmtUserSports = $pdo->prepare("
    SELECT us.*, s.sport_name 
    FROM user_sports us 
    JOIN sports s ON us.sport_id = s.sport_id 
    WHERE us.user_id = ? 
    ORDER BY s.sport_name ASC
");
$stmtUserSports->execute([$userId]);
$mySports = $stmtUserSports->fetchAll(PDO::FETCH_ASSOC);
$mySportIds = array_column($mySports, 'sport_id');

$inputClass = 'w-full px-3.5 py-2.5 rounded-[10px] border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 text-sm outline-none focus:border-nd-green transition-colors';
$labelClass = 'block text-[13.5px] font-semibold text-gray-900 dark:text-gray-100 mb-1.5';
$tabBase = 'px-[18px] py-3 text-[14.5px] font-semibold border-b-[3px] no-underline inline-flex items-center gap-2 whitespace-nowrap transition-colors';
$tabActive = 'text-nd-green border-nd-green';
$tabIdle = 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-900 dark:hover:text-gray-100';
?>

<!-- Header & Title -->
<div class="mb-5 flex justify-between items-end flex-wrap gap-3.5">
    <div>
        <h2 class="m-0 mb-1.5 text-[26px] text-gray-900 dark:text-gray-50 font-extrabold">Account Settings</h2>
        <p class="m-0 text-gray-500 dark:text-gray-400 text-[14.5px]">Manage your profile, sports skills, neighborhood location, and security preferences.</p>
    </div>
    <a href="../Profile/profile.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-semibold text-[13.5px] border border-nd-green text-nd-green hover:bg-nd-green hover:text-white no-underline transition-colors">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <span>View Public Profile</span>
    </a>
</div>

<!-- Alerts -->
<?php if (!empty($successMsg)): ?>
    <div class="px-4 py-3 rounded-xl text-sm font-medium mb-5 flex items-center gap-2.5 bg-nd-green/15 text-nd-green border border-nd-green/30">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="px-4 py-3 rounded-xl text-sm font-medium mb-5 flex items-center gap-2.5 bg-red-500/15 text-red-500 border border-red-500/30">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div class="flex gap-2 border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
    <a href="../Settings/settings.php?tab=profile" class="<?= $tabBase ?> <?= ($activeTab === 'profile') ? $tabActive : $tabIdle ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <span>Profile Info</span>
    </a>
    <a href="../Settings/settings.php?tab=sports" class="<?= $tabBase ?> <?= ($activeTab === 'sports') ? $tabActive : $tabIdle ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5-9c0 2.76 2.24 5 5 5s5-2.24 5-5-2.24-5-5-5-5 2.24-5 5z"/></svg>
        <span>My Sports (<?= count($mySports) ?>)</span>
    </a>
    <a href="../Settings/settings.php?tab=location" class="<?= $tabBase ?> <?= ($activeTab === 'location') ? $tabActive : $tabIdle ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
        <span>Neighborhood &bull; Location</span>
    </a>
    <a href="../Settings/settings.php?tab=security" class="<?= $tabBase ?> <?= ($activeTab === 'security') ? $tabActive : $tabIdle ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
        <span>Security &bull; Password</span>
    </a>
</div>

<!-- ================= TAB 1: PROFILE INFO ================= -->
<?php if ($activeTab === 'profile'): ?>
<div class="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-6 items-start">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm">
        <h3 class="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[19px] font-bold">Edit Profile Information</h3>

        <form method="POST" action="../Settings/settings.php?tab=profile" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">

            <!-- Avatar Preview & Upload -->
            <div class="flex items-center gap-5 mb-6 pb-5 border-b border-gray-200 dark:border-gray-700 flex-wrap">
                <div class="relative w-[84px] h-[84px] shrink-0">
                    <?php if (!empty($avatarPhoto)): ?>
                        <img id="avatar-preview-img" src="<?= htmlspecialchars($avatarPhoto) ?>" 
                             alt="Avatar" class="w-[84px] h-[84px] rounded-full object-cover border-[3px] border-gray-200 dark:border-gray-700"
                             onerror="this.style.display='none'; document.getElementById('avatar-preview-initial').style.display='flex';">
                        <div id="avatar-preview-initial" class="hidden w-[84px] h-[84px] rounded-full items-center justify-center text-[32px] font-extrabold text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <?= $initial ?>
                        </div>
                    <?php else: ?>
                        <div id="avatar-preview-initial" class="w-[84px] h-[84px] rounded-full flex items-center justify-center text-[32px] font-extrabold text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <?= $initial ?>
                        </div>
                        <img id="avatar-preview-img" src="" alt="Avatar" class="hidden w-[84px] h-[84px] rounded-full object-cover border-[3px] border-gray-200 dark:border-gray-700">
                    <?php endif; ?>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="<?= $labelClass ?> mb-1">Profile Photo</label>
                    <input type="file" name="profile_photo" id="profile-photo-input" accept="image/*" class="<?= $inputClass ?> text-[13px] py-1.5 px-2.5">
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mt-1">JPG, PNG, or WEBP up to 5MB.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-5">
                    <label class="<?= $labelClass ?>">Full Name *</label>
                    <input type="text" name="full_name" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                </div>

                <div class="mb-5">
                    <label class="<?= $labelClass ?>">Username (@handle) *</label>
                    <input type="text" name="username" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-5">
                    <label class="<?= $labelClass ?>">Mobile Number</label>
                    <input type="tel" name="mobile" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" placeholder="e.g. 9157488444">
                </div>

                <div class="mb-5">
                    <label class="<?= $labelClass ?>">Date of Birth</label>
                    <input type="date" name="dob" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Gender</label>
                <select name="gender" class="<?= $inputClass ?>">
                    <option value="Male" <?= (($user['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= (($user['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= (($user['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                    <option value="Prefer not to say" <?= (($user['gender'] ?? '') === 'Prefer not to say') ? 'selected' : '' ?>>Prefer not to say</option>
                </select>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors">Save Profile Changes</button>
            </div>
        </form>
    </div>

    <!-- Quick Info Sidebar -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-5 shadow-sm">
        <h4 class="m-0 mb-3 text-gray-900 dark:text-gray-50 text-base font-bold">Account Details</h4>
        <div class="text-[13.5px] text-gray-500 dark:text-gray-400 leading-relaxed">
            <div class="py-2 border-b border-gray-200 dark:border-gray-700">
                <strong class="text-gray-900 dark:text-gray-100">Email:</strong> <?= htmlspecialchars($user['email'] ?? 'Not set') ?>
            </div>
            <div class="py-2 border-b border-gray-200 dark:border-gray-700">
                <strong class="text-gray-900 dark:text-gray-100">Account Status:</strong> <span class="text-nd-green font-semibold"><?= htmlspecialchars($user['status'] ?? 'Active') ?></span>
            </div>
            <div class="py-2">
                <strong class="text-gray-900 dark:text-gray-100">Member Since:</strong> <?= !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'July 2026' ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ================= TAB 2: MY SPORTS & SKILLS ================= -->
<?php if ($activeTab === 'sports'): ?>
<div class="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-6 items-start">
    <!-- Active Sports List -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm">
        <h3 class="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[19px] font-bold">My Registered Sports</h3>

        <?php if (!empty($mySports)): ?>
            <div class="flex flex-col gap-4">
                <?php foreach ($mySports as $sp): ?>
                    <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[14px] p-4">
                        <form method="POST" action="../Settings/settings.php?tab=sports">
                            <input type="hidden" name="action" value="update_sport">
                            <input type="hidden" name="user_sport_id" value="<?= (int)$sp['user_sport_id'] ?>">

                            <div class="flex justify-between items-center mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-[10px] bg-nd-green/15 text-nd-green flex items-center justify-center text-lg">
                                        ⚽
                                    </div>
                                    <h4 class="m-0 text-base text-gray-900 dark:text-gray-50 font-bold">
                                        <?= htmlspecialchars($sp['sport_name']) ?>
                                    </h4>
                                </div>

                                <button type="submit" name="action" value="delete_sport" 
                                        onclick="return confirm('Remove <?= htmlspecialchars(addslashes($sp['sport_name'])) ?> from your profile?');"
                                        class="px-2.5 py-1 text-xs rounded-full font-semibold bg-transparent text-red-500 border border-red-500/30 hover:bg-red-500 hover:text-white cursor-pointer transition-colors">
                                    Remove
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                <div>
                                    <label class="block text-[12.5px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Skill Level</label>
                                    <select name="skill_level" class="<?= $inputClass ?> text-[13px] py-2 px-2.5">
                                        <option value="Beginner" <?= ($sp['skill_level'] === 'Beginner') ? 'selected' : '' ?>>Beginner</option>
                                        <option value="Intermediate" <?= ($sp['skill_level'] === 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                        <option value="Advanced" <?= ($sp['skill_level'] === 'Advanced') ? 'selected' : '' ?>>Advanced</option>
                                        <option value="Professional" <?= ($sp['skill_level'] === 'Professional') ? 'selected' : '' ?>>Professional</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[12.5px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Experience (Yrs)</label>
                                    <input type="number" name="experience_years" class="<?= $inputClass ?> text-[13px] py-2 px-2.5" min="0" max="60" value="<?= (int)$sp['experience_years'] ?>">
                                </div>

                                <div>
                                    <label class="block text-[12.5px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Position (Optional)</label>
                                    <input type="text" name="preferred_position" class="<?= $inputClass ?> text-[13px] py-2 px-2.5" placeholder="e.g. Forward, Mid" value="<?= htmlspecialchars($sp['preferred_position'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="flex justify-end mt-2.5">
                                <button type="submit" class="px-3.5 py-1.5 text-[12.5px] rounded-full font-semibold bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors">Update</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-9 px-4 bg-gray-100 dark:bg-gray-900 rounded-[14px] border border-dashed border-gray-200 dark:border-gray-700">
                <div class="text-[32px] mb-2">🎯</div>
                <div class="font-semibold text-gray-900 dark:text-gray-50 mb-1">No sports added yet</div>
                <p class="text-gray-500 dark:text-gray-400 text-[13.5px] m-0">Add your sports using the form on the right so neighbors know what you play.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add New Sport Card -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-5 shadow-sm">
        <h4 class="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[17px] font-bold">+ Add a New Sport</h4>

        <form method="POST" action="../Settings/settings.php?tab=sports">
            <input type="hidden" name="action" value="add_sport">

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Select Sport *</label>
                <select name="sport_id" class="<?= $inputClass ?>" required>
                    <option value="">-- Choose Sport --</option>
                    <?php foreach ($allSports as $s): ?>
                        <option value="<?= $s['sport_id'] ?>" <?= in_array($s['sport_id'], $mySportIds) ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($s['sport_name']) ?> <?= in_array($s['sport_id'], $mySportIds) ? '(Already Added)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Skill Level</label>
                <select name="skill_level" class="<?= $inputClass ?>">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate" selected>Intermediate</option>
                    <option value="Advanced">Advanced</option>
                    <option value="Professional">Professional</option>
                </select>
            </div>

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Years of Experience</label>
                <input type="number" name="experience_years" class="<?= $inputClass ?>" min="0" max="60" value="2" placeholder="e.g. 2">
            </div>

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Preferred Position</label>
                <input type="text" name="preferred_position" class="<?= $inputClass ?>" placeholder="e.g. Striker, Batsman, Defender">
            </div>

            <button type="submit" class="w-full justify-center py-2.5 px-4 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors">
                Add Sport to Profile
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ================= TAB 3: LOCATION & NEIGHBORHOOD ================= -->
<?php if ($activeTab === 'location'): ?>
<div class="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-6 items-start">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm">
        <h3 class="m-0 mb-2 text-gray-900 dark:text-gray-50 text-[19px] font-bold">Neighborhood &bull; Location</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm m-0 mb-5">
            Your neighborhood determines which nearby sports matches, fixtures, and neighbors you discover on TeamUp.
        </p>

        <form method="POST" action="../Settings/settings.php?tab=location">
            <input type="hidden" name="action" value="update_location">

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Area / Neighborhood *</label>
                <input type="text" name="area" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['area'] ?? '') ?>" placeholder="e.g. Pal, Vesu, Adajan, East Zone" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-5">
                    <label class="<?= $labelClass ?>">City *</label>
                    <input type="text" name="city" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="e.g. Surat, Ahmedabad, Mumbai" required>
                </div>

                <div class="mb-5">
                    <label class="<?= $labelClass ?>">State</label>
                    <input type="text" name="state" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['state'] ?? '') ?>" placeholder="e.g. Gujarat, Maharashtra">
                </div>
            </div>

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Country</label>
                <input type="text" name="country" class="<?= $inputClass ?>" value="<?= htmlspecialchars($user['country'] ?? 'India') ?>">
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors">Save Location Details</button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-5 shadow-sm">
        <h4 class="m-0 mb-2.5 text-gray-900 dark:text-gray-50 text-base font-bold">Why this matters</h4>
        <p class="text-[13.5px] text-gray-500 dark:text-gray-400 leading-relaxed m-0">
            TeamUp connects local sports communities block by block. Keeping your area and city updated ensures you see nearby neighbors, join ground bookings around you, and get matched with players within your travel radius.
        </p>
    </div>
</div>
<?php endif; ?>

<!-- ================= TAB 4: SECURITY & PASSWORD ================= -->
<?php if ($activeTab === 'security'): ?>
<div class="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-6 items-start">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm">
        <h3 class="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[19px] font-bold">Change Password</h3>

        <form method="POST" action="../Settings/settings.php?tab=security">
            <input type="hidden" name="action" value="update_password">

            <div class="mb-5">
                <label class="<?= $labelClass ?>">Current Password *</label>
                <input type="password" name="current_password" class="<?= $inputClass ?>" required placeholder="Enter your current password">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-5">
                    <label class="<?= $labelClass ?>">New Password *</label>
                    <input type="password" name="new_password" class="<?= $inputClass ?>" required minlength="6" placeholder="At least 6 characters">
                </div>

                <div class="mb-5">
                    <label class="<?= $labelClass ?>">Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="<?= $inputClass ?>" required minlength="6" placeholder="Repeat new password">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors">Update Password</button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-5 shadow-sm">
        <h4 class="m-0 mb-2.5 text-gray-900 dark:text-gray-50 text-base font-bold">Security Tips</h4>
        <ul class="m-0 pl-4 text-[13.5px] text-gray-500 dark:text-gray-400 leading-relaxed list-disc space-y-1">
            <li>Use at least 6-8 characters.</li>
            <li>Combine letters, numbers, and symbols.</li>
            <li>Never share your credentials with others.</li>
        </ul>
    </div>
</div>
<?php endif; ?>

<script>
const photoInput = document.getElementById('profile-photo-input');
const previewImg = document.getElementById('avatar-preview-img');
const previewInitial = document.getElementById('avatar-preview-initial');

if (photoInput) {
    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                if (previewInitial) previewInitial.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
