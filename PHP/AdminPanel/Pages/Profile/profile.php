<?php
// IMPORTANT: include auth.php exactly once, before anything else runs.
require_once dirname(__DIR__) . '/Components/Auth/auth.php';

//include_once("connect.php");
$conn = mysqli_connect("localhost", "root", "", "teamup") or die("Not Connected");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Robust admin session check (handles scalar ID, array, or object formats)
$adminId = null;
if (isset($_SESSION['admin_id'])) {
  $adminId = (int)$_SESSION['admin_id'];
} elseif (isset($_SESSION['admin'])) {
  if (is_array($_SESSION['admin'])) {
    $adminId = (int)($_SESSION['admin']['admin_id'] ?? $_SESSION['admin']['id'] ?? 0);
  } elseif (is_numeric($_SESSION['admin'])) {
    $adminId = (int)$_SESSION['admin'];
  }
}

if (!$adminId || $adminId <= 0) {
  echo "<h3 style='color:red'>ERROR: You are not logged in. admin_id = 0</h3>";
  echo "Please log in first.";
  exit;
}

if (!isset($conn)) {
  echo "<h3 style='color:red'>ERROR: Database connection (\$conn) is not defined. Check auth.php / connect.php.</h3>";
  exit;
}

// Opt-in debug dump: visit profile.php?debug=1 to see raw admins row
if (isset($_GET['debug'])) {
  echo "<!-- DEBUG START -->\n<pre>\n";
  echo "Session admin_id = " . $adminId . "\n\n";
  $debugStmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
  $debugStmt->bind_param("i", $adminId);
  $debugStmt->execute();
  $debugRow = $debugStmt->get_result()->fetch_assoc();
  echo "Raw row from DB:\n";
  print_r($debugRow);
  $debugStmt->close();
  echo "</pre>\n<!-- DEBUG END -->\n";
}

$admin = [
  'username'      => '',
  'full_name'     => '',
  'email'         => '',
  'mobile'        => '',
  'status'        => '',
  'profile_photo' => '',
  'created_at'    => null,
  'updated_at'    => null,
];

if ($adminId > 0 && isset($conn)) {
  $stmt = $conn->prepare(
    "SELECT full_name, username, email, mobile, profile_photo, status, created_at, updated_at
     FROM admins
     WHERE admin_id = ?
     LIMIT 1"
  );
  $stmt->bind_param("i", $adminId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    $admin = $row;
    if (!empty($admin['profile_photo']) && !preg_match('#^https?://#i', $admin['profile_photo']) && strpos($admin['profile_photo'], '/') !== 0) {
      $admin['profile_photo'] = '/Team-Up/images/Admin/' . basename($admin['profile_photo']);
    }
  }
  $stmt->close();
}

// Helpers for safe, consistent output in the markup
function admin_esc($value)
{
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function admin_initials($fullName)
{
  $parts = preg_split('/\s+/', trim((string) $fullName));
  $parts = array_filter($parts);
  if (empty($parts)) return '?';
  $first = mb_substr(reset($parts), 0, 1);
  $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
  return mb_strtoupper($first . $last);
}

function admin_date($value, $withTime = false)
{
  if (empty($value)) return '—';
  $ts = strtotime($value);
  if ($ts === false) return '—';
  return $withTime ? date('M j, Y, g:i A', $ts) : date('M j, Y', $ts);
}

function admin_completion_percent($admin)
{
  $fields = [
    $admin['profile_photo'] ?? '',
    $admin['full_name'] ?? '',
    $admin['email'] ?? '',
    $admin['mobile'] ?? '',
    $admin['status'] ?? '',
  ];
  $filled = 0;
  foreach ($fields as $f) {
    if (trim((string) $f) !== '') $filled++;
  }
  return (int) round(($filled / count($fields)) * 100);
}

$completion    = admin_completion_percent($admin);
$ringRadius    = 34;
$ringCircum    = 2 * M_PI * $ringRadius;
$ringOffset    = $ringCircum * (1 - ($completion / 100));

// Split full_name into first/last name for input fields
$nameParts = preg_split('/\s+/', trim((string) $admin['full_name']), 2);
$firstName = $nameParts[0] ?? '';
$lastName  = $nameParts[1] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | TeamUp 2026 v4</title>
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
  <meta name="description" content="Profile — Profile. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
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
    .pc-toast {
      position: fixed;
      left: 50%;
      bottom: 24px;
      transform: translateX(-50%) translateY(12px);
      background: var(--card-bg, #1a2332);
      border: 1px solid var(--border-color-light, #263449);
      color: var(--text, #e5e9f0);
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
      opacity: 0;
      transition: opacity 180ms ease, transform 180ms ease;
      z-index: 9999;
      pointer-events: none;
    }

    .pc-toast.is-visible {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    .pc-toast-success {
      border-color: var(--primary, #1ABB9C);
      color: var(--primary, #1ABB9C);
    }

    .pc-toast-error {
      border-color: var(--red, #e5484d);
      color: var(--red, #e5484d);
    }

    .pc-toast-neutral {
      color: var(--text-muted, #8a94a6);
    }

    .form-control.is-invalid {
      border-color: var(--red, #e5484d) !important;
    }

    .field-error {
      color: var(--red, #e5484d);
      font-size: 12px;
      margin-top: 4px;
      display: none;
    }

    .field-error.is-visible {
      display: block;
    }

    .btn[disabled] {
      opacity: .45;
      cursor: not-allowed;
      pointer-events: none;
    }
  </style>
</head>

<body data-shell="admin" data-page="profile" data-breadcrumb="Home > Profile">

  <a class="skip-link" href="#main-content">Skip to main content</a>

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>

  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <div class="page-pretitle">Account</div>
            <h1 class="page-title">Your profile</h1>
          </div>
          <!-- <div class="page-actions">
            <button type="button" id="pageCancelBtn" class="btn btn-outline" disabled>Cancel</button>
            <button type="button" id="pageSaveBtn" class="btn btn-primary" disabled>Save changes</button>
          </div> -->
        </div>
      </div>

      <div class="row col-4-8">
        <!-- Profile summary card -->
        <div class="card">
          <div class="card-body" style="text-align:center;padding:24px 16px">
            <div class="pc-avatar-wrap" style="position:relative;width:96px;height:96px;margin:0 auto 12px">
              <div id="profileAvatarCircle" style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dk));display:flex;align-items:center;justify-content:center;color:white;font-size:32px;font-weight:600;overflow:hidden">
                <?php if (!empty($admin['profile_photo'])): ?>
                  <img id="profileAvatarImg" src="<?php echo admin_esc($admin['profile_photo']); ?>" alt="<?php echo admin_esc($admin['full_name']); ?>'s profile picture" style="width:100%;height:100%;object-fit:cover">
                  <span id="profileAvatarInitials" aria-hidden="true" style="display:none"><?php echo admin_esc(admin_initials($admin['full_name'])); ?></span>
                <?php else: ?>
                  <span id="profileAvatarInitials" aria-hidden="true"><?php echo admin_esc(admin_initials($admin['full_name'])); ?></span>
                  <img id="profileAvatarImg" src="" alt="<?php echo admin_esc($admin['full_name']); ?>'s profile picture" style="display:none;width:100%;height:100%;object-fit:cover">
                <?php endif; ?>
              </div>
            </div>
            <div id="avatarNameHeading" style="font-size:16px;font-weight:600;color:var(--text)"><?php echo admin_esc($admin['full_name']); ?></div>
            <div id="avatarStatusSubtext" style="font-size:12.5px;color:var(--text-muted);margin-top:2px"><?php echo admin_esc($admin['status']); ?></div>
            <div style="margin-top:14px;display:flex;gap:6px;justify-content:center">
              <button type="button" id="changeAvatarBtn" class="btn btn-outline btn-sm" aria-label="Change profile picture" aria-describedby="avatarError">Change Profile</button>
              <input type="file" id="avatarFileInput" accept="image/png,image/jpeg,image/webp" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0" tabindex="-1" aria-hidden="true">
            </div>
            <p id="avatarError" role="alert" style="color:var(--red,#e5484d);font-size:12px;margin:8px 0 0;display:none"></p>
            <div id="avatarStatus" aria-live="polite" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0"></div>

            <dl style="text-align:left;margin:18px 0 0;padding-top:16px;border-top:1px solid var(--border-color-light);display:flex;flex-direction:column;gap:10px">
              <div style="display:flex;justify-content:space-between;gap:12px">
                <dt style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted)">Username</dt>
                <dd style="margin:0;font-size:13px;color:var(--text)">@<?php echo admin_esc($admin['username']); ?></dd>
              </div>
              <div style="display:flex;justify-content:space-between;gap:12px">
                <dt style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted)">Status</dt>
                <dd style="margin:0"><span id="statusBadge" style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:999px;background:var(--primary-lt);color:var(--primary)"><?php echo admin_esc($admin['status']); ?></span></dd>
              </div>
              <div style="display:flex;justify-content:space-between;gap:12px">
                <dt style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted)">Email</dt>
                <dd style="margin:0;font-size:13px"><a id="emailDisplay" href="mailto:<?php echo admin_esc($admin['email']); ?>" style="color:var(--primary);text-decoration:none"><?php echo admin_esc($admin['email']); ?></a></dd>
              </div>
              <div style="display:flex;justify-content:space-between;gap:12px">
                <dt style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted)">Mobile</dt>
                <dd id="mobileDisplay" style="margin:0;font-size:13px;<?php echo empty($admin['mobile']) ? 'color:var(--text-muted)' : 'color:var(--text)'; ?>"><?php echo !empty($admin['mobile']) ? admin_esc($admin['mobile']) : 'Not provided'; ?></dd>
              </div>
              <div style="display:flex;justify-content:space-between;gap:12px">
                <dt style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted)">Date joined</dt>
                <dd style="margin:0;font-size:13px;color:var(--text)"><time datetime="<?php echo admin_esc($admin['created_at']); ?>"><?php echo admin_date($admin['created_at']); ?></time></dd>
              </div>
              <div style="display:flex;justify-content:space-between;gap:12px">
                <dt style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted)">Last updated</dt>
                <dd style="margin:0;font-size:13px;color:var(--text)"><time id="lastUpdatedTime" datetime="<?php echo admin_esc($admin['updated_at']); ?>"><?php echo admin_date($admin['updated_at'], true); ?></time></dd>
              </div>
            </dl>
          </div>
          <div style="border-top:1px solid var(--border-color-light)">
            <div class="profile-ring-box" style="padding:16px">
              <div class="ring-wrap">
                <svg viewBox="0 0 80 80">
                  <circle class="ring-bg" cx="40" cy="40" r="<?php echo $ringRadius; ?>" />
                  <circle id="ringFill" class="ring-fill" cx="40" cy="40" r="<?php echo $ringRadius; ?>"
                    stroke-dasharray="<?php echo round($ringCircum, 2); ?>"
                    stroke-dashoffset="<?php echo round($ringOffset, 2); ?>" />
                </svg>
                <div class="ring-center"><span id="ringCenterValue"><?php echo $completion; ?></span><span>%</span></div>
              </div>
              <div class="note">Profile completion</div>
              <a href="#" id="completeNowLink" class="ring-link" style="<?php echo $completion >= 100 ? 'display:none' : ''; ?>">Complete now →</a>
            </div>
          </div>
        </div>

        <!-- Editable profile form -->
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Personal information</div>
              <div class="card-subtitle">Update your name, email, and contact details.</div>
            </div>
          </div>
          <div class="card-body">
            <div id="personalInfoForm" data-save-url="/Team-Up/PHP/AdminPanel/Pages/Components/Auth/save_changes.php">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="firstName">First name</label>
                  <input type="text" id="firstName" name="first_name" class="form-control" value="<?php echo admin_esc($firstName); ?>">
                </div>
                <div class="form-group">
                  <label class="form-label" for="lastName">Last name</label>
                  <input type="text" id="lastName" name="last_name" class="form-control" value="<?php echo admin_esc($lastName); ?>">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="email2">Email <span class="required">*</span></label>
                  <input type="email" id="email2" name="email" class="form-control" value="<?php echo admin_esc($admin['email']); ?>">
                  <p id="emailError" class="field-error" role="alert"></p>
                </div>
                <div class="form-group">
                  <label class="form-label" for="mobile">Mobile</label>
                  <input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo admin_esc($admin['mobile']); ?>" placeholder="+371 …">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <input type="text" id="status" class="form-control" value="<?php echo admin_esc($admin['status']); ?>" disabled readonly aria-readonly="true" style="cursor:not-allowed">
                <p style="color:var(--text-muted);font-size:12px;margin-top:4px">Account status is managed by another administrator and can't be changed here.</p>
              </div>

              <div class="form-actions right" style="flex-wrap:wrap;gap:8px">
                <p id="saveError" role="alert" style="color:var(--red,#e5484d);font-size:12px;margin:0;width:100%;text-align:right;display:none"></p>
                <p id="saveSuccess" role="status" style="color:var(--primary,#1ABB9C);font-size:12px;margin:0;width:100%;text-align:right;display:none"></p>
                <button type="button" id="cardDiscardBtn" class="btn btn-outline" disabled>Discard</button>
                <button type="button" id="cardSaveBtn" class="btn btn-primary" disabled>Save changes</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>

  </main>

  <script>
    (function() {
      var MAX_SIZE = 5 * 1024 * 1024; // 5 MB
      var ACCEPTED = ["image/png", "image/jpeg", "image/webp"];

      var formWrap = document.getElementById("personalInfoForm");
      var saveUrl = formWrap.getAttribute("data-save-url");

      var fields = {
        first_name: document.getElementById("firstName"),
        last_name: document.getElementById("lastName"),
        email: document.getElementById("email2"),
        mobile: document.getElementById("mobile")
      };

      var emailError = document.getElementById("emailError");

      var cardSaveBtn = document.getElementById("cardSaveBtn");
      var cardDiscardBtn = document.getElementById("cardDiscardBtn");
      var pageSaveBtn = document.getElementById("pageSaveBtn");
      var pageCancelBtn = document.getElementById("pageCancelBtn");

      var avatarTrigger = document.getElementById("changeAvatarBtn");
      var avatarInput = document.getElementById("avatarFileInput");
      var avatarImg = document.getElementById("profileAvatarImg");
      var avatarInitials = document.getElementById("profileAvatarInitials");
      var avatarError = document.getElementById("avatarError");
      var avatarStatus = document.getElementById("avatarStatus");

      var baseline = snapshot();
      var baselineAvatarSrc = avatarImg.getAttribute("src") || "";
      var baselineAvatarWasVisible = avatarImg.style.display !== "none";

      var pendingAvatarFile = null;
      var pendingAvatarPreviewUrl = null;

      function snapshot() {
        var s = {};
        Object.keys(fields).forEach(function(key) {
          s[key] = fields[key] ? fields[key].value : '';
        });
        return s;
      }

      function isDirty() {
        var current = snapshot();
        var textDirty = Object.keys(fields).some(function(key) {
          return current[key] !== baseline[key];
        });
        return textDirty || pendingAvatarFile !== null;
      }

      function setButtonsEnabled(enabled) {
        [cardSaveBtn, cardDiscardBtn, pageSaveBtn, pageCancelBtn].forEach(function(btn) {
          if (!btn) return;
          if (enabled) btn.removeAttribute("disabled");
          else btn.setAttribute("disabled", "disabled");
        });
      }

      function refreshDirtyState() {
        setButtonsEnabled(isDirty());
      }

      Object.keys(fields).forEach(function(key) {
        if (fields[key]) {
          fields[key].addEventListener("input", refreshDirtyState);
        }
      });

      function showFieldError(message) {
        if (fields.email) fields.email.classList.add("is-invalid");
        if (emailError) {
          emailError.textContent = message;
          emailError.classList.add("is-visible");
        }
      }

      function clearFieldError() {
        if (fields.email) fields.email.classList.remove("is-invalid");
        if (emailError) {
          emailError.textContent = "";
          emailError.classList.remove("is-visible");
        }
      }

      function showAvatarError(message) {
        if (avatarError) {
          avatarError.textContent = message;
          avatarError.style.display = "block";
        }
        if (avatarStatus) avatarStatus.textContent = message;
      }

      function clearAvatarError() {
        if (avatarError) {
          avatarError.textContent = "";
          avatarError.style.display = "none";
        }
      }

      function showToast(message, type) {
        // type = type || "success";
        // var toast = document.createElement("div");
        // toast.className = "pc-toast pc-toast-" + type;
        // toast.setAttribute("role", "status");
        // toast.textContent = message;
        // document.body.appendChild(toast);
        // requestAnimationFrame(function() {
        //   toast.classList.add("is-visible");
        // });
        // window.setTimeout(function() {
        //   toast.classList.remove("is-visible");
        //   window.setTimeout(function() {
        //     toast.remove();
        //   }, 220);
        // }, 2600);
      }

      function validEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
      }

      function updateRing(percent) {
        var ringFill = document.getElementById("ringFill");
        var ringValue = document.getElementById("ringCenterValue");
        var completeLink = document.getElementById("completeNowLink");
        if (!ringFill || !ringValue) return;

        var circumference = parseFloat(ringFill.getAttribute("stroke-dasharray"));
        var offset = circumference * (1 - percent / 100);
        ringFill.setAttribute("stroke-dashoffset", offset.toFixed(2));
        ringValue.textContent = percent;
        if (completeLink) completeLink.style.display = percent >= 100 ? "none" : "";
      }

      function updateSummaryPanel(adminData) {
        if (!adminData) return;
        var nameHeading = document.getElementById("avatarNameHeading");
        var subtext = document.getElementById("avatarStatusSubtext");
        var badge = document.getElementById("statusBadge");

        if (nameHeading) nameHeading.textContent = adminData.full_name || "";
        if (subtext) subtext.textContent = adminData.status || "";
        if (badge) badge.textContent = adminData.status || "";

        var emailLink = document.getElementById("emailDisplay");
        if (emailLink) {
          emailLink.textContent = adminData.email || "";
          emailLink.href = "mailto:" + (adminData.email || "");
        }

        var mobileEl = document.getElementById("mobileDisplay");
        if (mobileEl) {
          if (adminData.mobile) {
            mobileEl.textContent = adminData.mobile;
            mobileEl.style.color = "var(--text)";
          } else {
            mobileEl.textContent = "Not provided";
            mobileEl.style.color = "var(--text-muted)";
          }
        }

        var lastUpdatedEl = document.getElementById("lastUpdatedTime");
        if (lastUpdatedEl && adminData.updated_at) {
          lastUpdatedEl.setAttribute("datetime", adminData.updated_at);
          var d = new Date(adminData.updated_at.replace(" ", "T"));
          if (!isNaN(d.getTime())) {
            lastUpdatedEl.textContent = d.toLocaleString(undefined, {
              year: "numeric",
              month: "short",
              day: "numeric",
              hour: "numeric",
              minute: "2-digit"
            });
          }
        }

        if (adminData.profile_photo && avatarImg) {
          var src = adminData.profile_photo + "?v=" + Date.now();
          avatarImg.src = src;
          avatarImg.style.display = "block";
          if (avatarInitials) avatarInitials.style.display = "none";
          baselineAvatarSrc = src;
          baselineAvatarWasVisible = true;
        }
      }

      // Avatar picker
      if (avatarTrigger && avatarInput) {
        avatarTrigger.addEventListener("click", function() {
          avatarInput.click();
        });

        avatarInput.addEventListener("change", function(e) {
          var file = e.target.files && e.target.files[0];
          if (!file) return;

          if (ACCEPTED.indexOf(file.type) === -1) {
            showAvatarError("Unsupported file type. Please use .png, .jpg, or .webp.");
            avatarInput.value = "";
            return;
          }
          if (file.size > MAX_SIZE) {
            showAvatarError("File is too large. Maximum size is 5 MB.");
            avatarInput.value = "";
            return;
          }

          clearAvatarError();
          pendingAvatarFile = file;

          if (pendingAvatarPreviewUrl) {
            URL.revokeObjectURL(pendingAvatarPreviewUrl);
          }
          pendingAvatarPreviewUrl = URL.createObjectURL(file);

          if (avatarImg) {
            avatarImg.src = pendingAvatarPreviewUrl;
            avatarImg.style.display = "block";
          }
          if (avatarInitials) avatarInitials.style.display = "none";
          if (avatarStatus) avatarStatus.textContent = "New profile picture selected. Not yet saved.";

          refreshDirtyState();
          avatarInput.value = "";
        });
      }

      // Save / Discard Actions
      function handleSave() {
        clearFieldError();
        var saveErrorEl = document.getElementById("saveError");
        if (saveErrorEl) {
          saveErrorEl.textContent = "";
          saveErrorEl.style.display = "none";
        }

        // ---- Validate required fields ----
        var firstNameVal = fields.first_name.value.trim();
        var lastNameVal = fields.last_name.value.trim();
        var emailVal = fields.email.value.trim();

        if (!firstNameVal) {
          if (saveErrorEl) {
            saveErrorEl.textContent = "First name is required.";
            saveErrorEl.style.display = "block";
          }
          fields.first_name.focus();
          return;
        }
        if (!lastNameVal) {
          if (saveErrorEl) {
            saveErrorEl.textContent = "Last name is required.";
            saveErrorEl.style.display = "block";
          }
          fields.last_name.focus();
          return;
        }
        if (!emailVal) {
          showFieldError("Email is required.");
          fields.email.focus();
          return;
        }
        if (!validEmail(emailVal)) {
          showFieldError("Enter a valid email address.");
          fields.email.focus();
          return;
        }
        var payload = new FormData();
        Object.keys(fields).forEach(function(key) {
          payload.append(key, fields[key].value.trim());
        });

        var fname = fields.first_name.value.trim();
        var lname = fields.last_name.value.trim();
        payload.append("full_name", (fname + " " + lname).trim());

        if (pendingAvatarFile) {
          payload.append("avatar", pendingAvatarFile);
        }

        setButtonsEnabled(false);
        [cardSaveBtn, pageSaveBtn].forEach(function(btn) {
          if (btn) btn.textContent = "Saving…";
        });

        fetch(saveUrl, {
            method: "POST",
            body: payload,
            credentials: "same-origin",
            headers: {
              "X-Requested-With": "XMLHttpRequest"
            }
          })
          .then(async function(res) {
            const rawText = await res.text();
            let parsedData;
            try {
              parsedData = JSON.parse(rawText);
            } catch (e) {
              console.error("Server output raw non-JSON text:", rawText);
              var snippet = rawText.trim().slice(0, 220);
              throw new Error(
                "Server returned an invalid response (HTTP " + res.status + ")." +
                (snippet ? " Details: " + snippet : "")
              );
            }
            return {
              ok: res.ok,
              status: res.status,
              data: parsedData
            };
          })
          .then(function(result) {
            if (result.data && (result.data.ok || result.data.success)) {
              baseline = snapshot();
              pendingAvatarFile = null;
              if (pendingAvatarPreviewUrl) {
                URL.revokeObjectURL(pendingAvatarPreviewUrl);
                pendingAvatarPreviewUrl = null;
              }
              updateSummaryPanel(result.data.admin || result.data.data);
              if (result.data.completion) updateRing(result.data.completion);
              if (saveErrorEl) {
                saveErrorEl.textContent = "";
                saveErrorEl.style.display = "none";
              }
              var saveSuccessEl = document.getElementById("saveSuccess");
              saveSuccessEl.textContent = "Profile updated successfully!";
              saveSuccessEl.style.display = "block";
            } else if (result.data && result.data.errors) {
              var errs = result.data.errors;
              if (errs.email) {
                showFieldError(errs.email);
                fields.email.focus();
              }
              if (errs.avatar && saveErrorEl) {
                saveErrorEl.textContent = errs.avatar;
                saveErrorEl.style.display = "block";
              }
            } else {
              var msg = (result.data && (result.data.message || result.data.error)) || "Could not save changes.";
              if (saveErrorEl) {
                saveErrorEl.textContent = msg;
                saveErrorEl.style.display = "block";
              }
            }
          })
          .catch(function(err) {
            console.error("Save profile request error:", err);
            if (saveErrorEl) {
              saveErrorEl.textContent = err.message;
              saveErrorEl.style.display = "block";
            }
          })
          .finally(function() {
            [cardSaveBtn, pageSaveBtn].forEach(function(btn) {
              if (btn) btn.textContent = "Save changes";
            });
            refreshDirtyState();
          });
      }

      function handleDiscard() {
        Object.keys(fields).forEach(function(key) {
          if (fields[key]) fields[key].value = baseline[key];
        });
        clearFieldError();

        pendingAvatarFile = null;
        if (pendingAvatarPreviewUrl) {
          URL.revokeObjectURL(pendingAvatarPreviewUrl);
          pendingAvatarPreviewUrl = null;
        }
        clearAvatarError();
        if (avatarImg) {
          if (baselineAvatarWasVisible) {
            avatarImg.src = baselineAvatarSrc;
            avatarImg.style.display = "block";
            if (avatarInitials) avatarInitials.style.display = "none";
          } else {
            avatarImg.style.display = "none";
            if (avatarInitials) avatarInitials.style.display = "";
          }
        }

        setButtonsEnabled(false);
        showToast("Changes discarded.", "neutral");
      }

      if (cardSaveBtn) cardSaveBtn.addEventListener("click", handleSave);
      if (pageSaveBtn) pageSaveBtn.addEventListener("click", handleSave);
      if (cardDiscardBtn) cardDiscardBtn.addEventListener("click", handleDiscard);
      if (pageCancelBtn) pageCancelBtn.addEventListener("click", handleDiscard);

      var completeLink = document.getElementById("completeNowLink");
      if (completeLink) {
        completeLink.addEventListener("click", function(e) {
          e.preventDefault();
          var hasAvatar = avatarImg && avatarImg.style.display !== "none" && avatarImg.getAttribute("src");
          if (!hasAvatar && avatarTrigger) {
            avatarTrigger.click();
            return;
          }
          var order = ["first_name", "last_name", "email", "mobile"];
          var target = order.find(function(key) {
            return fields[key] && !fields[key].value.trim();
          });
          if (target && fields[target]) fields[target].focus();
        });
      }
    })();
  </script>
</body>

</html>