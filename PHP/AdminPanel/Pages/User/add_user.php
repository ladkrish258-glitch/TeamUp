<?php
require_once dirname(__DIR__) . '/Components/Auth/auth.php';

$conn = mysqli_connect("localhost", "root", "", "teamup") or die("Not Connected");
$up = "ADD";

// Initialize all variables so they always exist (fixes "Undefined variable" warnings)
$id = "";
$full_name = "";
$username = "";
$email = "";
$password = "";
$mobile = "";
$dob = "";
$status = "";
$gender = "";
$profile_photo = "";
$country = "";
$state = "";
$city = "";
$area = "";

if (isset($_GET['uid'])) {
  $id = $_GET['uid'];
  $sql = "select * from users where user_id = ?";

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_array($result);

  if ($row) {
    $full_name     = $row['full_name'];
    $username      = $row['username'];
    $email         = $row['email'];
    $password      = $row['password'];
    $mobile        = $row['mobile'];
    $dob           = $row['dob'];
    $status        = $row['status'];
    $gender        = $row['gender'];
    $profile_photo = $row['profile_photo'];
    $country       = $row['country'];
    $state         = $row['state'];
    $city          = $row['city'];
    $area          = $row['area'];
  }

  $up = "Update";
}

if (isset($_POST['submit'])) {
  $destDir = dirname(__DIR__, 4) . "/images/User/Profile/";
  if (!is_dir($destDir)) {
    @mkdir($destDir, 0777, true);
  }
  @chmod($destDir, 0777);

  if (empty($_GET['uid'])) {
    $img = "";
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK && !empty($_FILES['profile']['tmp_name'])) {
      $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
      $safeName = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . ($ext ?: 'jpg');
      if (move_uploaded_file($_FILES['profile']['tmp_name'], $destDir . $safeName)) {
        $img = $safeName;
      }
    }

    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (full_name, username, email, password, mobile, dob, status, gender, profile_photo, country, state, city, area, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
      $stmt,
      "sssssssssssss",
      $_POST['fullname'],
      $_POST['username'],
      $_POST['email'],
      $hashedPassword,
      $_POST['mobile'],
      $_POST['dob'],
      $_POST['status'],
      $_POST['gender'],
      $img,
      $_POST['country'],
      $_POST['state'],
      $_POST['city'],
      $_POST['area']
    );
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
      header("Location: /Team-Up/PHP/AdminPanel/Pages/User/user_data.php");
      exit;
    } else {
      echo "Error: " . mysqli_error($conn);
    }
  } else {
    $img = $_POST['old_profile'] ?? '';

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK && !empty($_FILES['profile']['tmp_name'])) {
      $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
      $safeName = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . ($ext ?: 'jpg');
      if (move_uploaded_file($_FILES['profile']['tmp_name'], $destDir . $safeName)) {
        $img = $safeName;
        if (!empty($_POST['old_profile']) && file_exists($destDir . $_POST['old_profile'])) {
          @unlink($destDir . $_POST['old_profile']);
        }
      }
    }

    $pass = $_POST['password'];
    if (!str_starts_with($pass, '$2y$') && !str_starts_with($pass, '$2a$')) {
      $pass = password_hash($pass, PASSWORD_DEFAULT);
    }

    $sql = "UPDATE users SET full_name=?, username=?, email=?, password=?, mobile=?, dob=?, status=?, gender=?, profile_photo=?, country=?, state=?, city=?, area=?, updated_at=NOW() WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
      $stmt,
      "sssssssssssssi",
      $_POST['fullname'],
      $_POST['username'],
      $_POST['email'],
      $pass,
      $_POST['mobile'],
      $_POST['dob'],
      $_POST['status'],
      $_POST['gender'],
      $img,
      $_POST['country'],
      $_POST['state'],
      $_POST['city'],
      $_POST['area'],
      $id
    );
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
      header("Location: /Team-Up/PHP/AdminPanel/Pages/User/user_data.php");
      exit;
    } else {
      echo "Error: " . mysqli_error($conn);
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forms | TeamUp 2026 v4</title>
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
  <meta name="description" content="Forms — Forms > General. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Forms">
  <meta property="og:description" content="Forms — Forms > General. Free admin template by Colorlib. 60 pages, 20 chart variants, dark mode, PWA-ready.">
  <meta property="og:image" content="/Team-Up/images/android-chrome-512x512.svg">
  <meta property="og:site_name" content="TeamUp v4">
  <meta name="twitter:card" content="summary_large_image">
  <!-- <script>
    $(document).ready(function () {
      $("submit").click(function(){
        if ($("#fullname").val() == "") {
         $(".fullname").focus();
         return false;
        }
      })
      })
  </script> -->
  
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
  <script type="module" crossorigin src="/Team-Up/PHP/AdminPanel/js/form-CN1ttvzF.js"></script>
  <?php include_once dirname(__DIR__) . '/Components/Tailwind/head.php'; ?>
</head>

<body data-shell="admin" data-page="forms" data-breadcrumb="Home > Forms > General">

  <?php include dirname(__DIR__) . '/Components/Sidebar/aside.php'; ?>
  <?php include_once dirname(__DIR__) . '/Components/Header/header.php'; ?>
  <main id="main-content" tabindex="-1" class="main">
    <div class="page-wrapper">

      <div class="page-header">
        <div class="page-header-row">
          <div>
            <h1 class="page-title">Add User</h1>
            <a href="/Team-Up/PHP/AdminPanel/Pages/Dashboard/dashboard.php" style="color: var(--text-secondary);">Dashboard</a>
            <span>></span>
            <a style="color: var(--text-secondary);">User</a>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="old_profile" value="<?php echo $profile_photo; ?>">

            <div class="form-row">
              <!-- Profile Photo -->
              <div style="display:flex;gap:20px;align-items:center;margin-bottom:18px">
                <label class="avatar-upload" for="profile">
                  <?php if (!empty($profile_photo)): ?>
                    <img src="/Team-Up/images/User/Profile/<?php echo $profile_photo; ?>" alt="Profile" id="profile-preview" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                  <?php else: ?>
                    <img src="/Team-Up/images/default/default.png" alt="Default Profile" id="profile-preview" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                  <?php endif; ?>
                  <span class="overlay">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M3 7a2 2 0 012-2h2l2-2h6l2 2h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                      <circle cx="12" cy="12" r="3.5" />
                    </svg>
                  </span>
                  <input type="file" id="profile" name="profile" accept="image/*" hidden>
                </label>
              </div>

              <!-- Full Name -->
              <div class="form-group">
                <label class="form-label" for="full_name">Full Name : <span class="required">*</span></label>
                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="e.g. Krish Lad Jayeshbhai" value="<?php echo $full_name; ?>">
              </div>
            </div>

            <!-- User Name -->
            <div class="form-group">
              <label class="form-label" for="user_name">Username : <span class="required">*</span></label>
              <input type="text" name="username" placeholder="Username" class="form-control" placeholder="e.g. krish_lad" value="<?php echo $username; ?>">
            </div>

            <!-- Email -->
            <div class="form-group">
              <label class="form-label" for="email">Email : <span class="required">*</span></label>
              <input type="text" name="email" placeholder="Email" class="form-control" placeholder="e.g. ladkrish@gmail.com" value="<?php echo $email; ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="form-label" for="password">Password : <span class="required">*</span></label>
              <div class="password-field-wrapper" style="position:relative">
                <input type="password" name="password" id="password" placeholder="Password" class="form-control" style="padding-right:40px" value="<?php echo $password; ?>">
                <button type="button" class="password-toggle-btn" data-target="password" aria-label="Show password" aria-pressed="false" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);background:none;border:none;padding:4px;cursor:pointer;color:var(--text-secondary,#888);display:flex;align-items:center">
                  <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                    <path d="M1.5 12s4-7.5 10.5-7.5S22.5 12 22.5 12s-4 7.5-10.5 7.5S1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18" style="display:none">
                    <path d="M3 3l18 18" />
                    <path d="M10.6 5.1A10.9 10.9 0 0112 5c6.5 0 10.5 7 10.5 7a17.6 17.6 0 01-3.2 4.1M6.6 6.6C3.4 8.6 1.5 12 1.5 12s4 7 10.5 7c1.4 0 2.7-.3 3.9-.8" />
                    <path d="M9.9 9.9a3 3 0 004.2 4.2" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-row">
              <!-- Mobile Number -->
              <div class="form-group">
                <label class="form-label" for="mobile">Mobile No. : <span class="required">*</span></label>
                <input type="number" name="mobile" placeholder="Mobile" class="form-control" placeholder="1234567890" value="<?php echo $mobile; ?>">
              </div>

              <!-- Date Of Birth -->
              <div class="form-group">
                <label class="form-label" for="dob">Date Of Birth : <span class="required">*</span></label>
                <input type="date" name="dob" placeholder="Date of Birth" class="form-control" value="<?php echo $dob; ?>">
              </div>
            </div>

            <div class="form-row">
              <!-- Status -->
              <div class="form-group">
                <label class="form-label" for="status">Status : <span class="required">*</span></label>
                <select name="status" class="form-control">
                  <option value="" hidden>Select Status</option>
                  <option value="Active" <?php if ($status == "Active") {
                                            echo 'selected';
                                          } ?>>Active</option>
                  <option value="Inactive" <?php if ($status == "Inactive") {
                                              echo 'selected';
                                            } ?>>Inactive</option>
                </select>
              </div>

              <!-- Gender -->
              <div class="form-group">
                <label class="form-label" for="gender">Gender : <span class="required">*</span></label>
                <select name="gender" class="form-control">
                  <option value="" hidden>Select Gender</option>
                  <option value="Male" <?php if ($gender == "Male") {
                                          echo 'selected';
                                        } ?>>Male</option>
                  <option value="Female" <?php if ($gender == "Female") {
                                            echo 'selected';
                                          } ?>>Female</option>
                  <option value="Other" <?php if ($gender == "Other") {
                                          echo 'selected';
                                        } ?>>Other</option>
                </select>
              </div>
            </div>


            <div class="form-row">
              <!-- Conutry -->
              <div class="form-group">
                <label class="form-label" for="country">Country : <span class="required">*</span></label>
                <select name="country" id="country" class="form-control">
                  <option value="" hidden>Select Country</option>
                  <option value="India" <?php if ($country == "India") {
                                          echo 'selected';
                                        } ?>>India</option>
                  <option value="USA" <?php if ($country == "USA") {
                                        echo 'selected';
                                      } ?>>USA</option>
                  <option value="UK" <?php if ($country == "UK") {
                                        echo 'selected';
                                      } ?>>UK</option>
                </select>
              </div>

              <!-- State -->
              <div class="form-group">
                <label class="form-label" for="state">State : <span class="required">*</span></label>
                <select name="state" id="state" class="form-control">
                  <option value="" hidden>Select State</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <!-- City -->
              <div class="form-group">
                <label class="form-label" for="city">City : <span class="required">*</span></label>
                <select name="city" id="city" class="form-control">
                  <option value="" hidden>Select City</option>
                </select>
              </div>

              <!-- Area -->
              <div class="form-group">
                <label class="form-label" for="area">Area : <span class="required">*</span></label>
                <input type="text" name="area" placeholder="Area" class="form-control" value="<?php echo $area; ?>">
              </div>

              <div class="form-group">
                <button class="btn-primary form-control" type="submit" id="submit" name="submit" value="<?php echo $up; ?>">Submit</button>
              </div>

              <div class="form-group">
                <button class="btn-danger form-control" type="reset" name="reset_button" id="reset_button">Reset</button>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
    <?php include dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
  </main>
  <script src="/Team-Up/PHP/AdminPanel/js/location-data.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    // Profile photo preview
    document.getElementById('profile').addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        document.getElementById('profile-preview').src = URL.createObjectURL(file);
      }
    });

    // Password show/hide toggle
    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
        btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        btn.querySelector('.icon-eye').style.display = isHidden ? 'none' : '';
        btn.querySelector('.icon-eye-off').style.display = isHidden ? '' : 'none';
      });
    });

    
    // Values already saved in DB (used to pre-select in edit mode)
    const savedState = <?php echo json_encode($state); ?>;
    const savedCity   = <?php echo json_encode($city); ?>;
    const savedCountry = <?php echo json_encode($country); ?>;

    const countrySelect = document.getElementById('country');
    const stateSelect   = document.getElementById('state');
    const citySelect    = document.getElementById('city');

    function populateCountries(selectedCountry) {
      if (typeof locationData === 'undefined') return;
      countrySelect.innerHTML = '<option value="" hidden>Select Country</option>';
      Object.keys(locationData).sort().forEach(countryName => {
        const opt = document.createElement('option');
        opt.value = countryName;
        opt.textContent = countryName;
        if (countryName === selectedCountry) opt.selected = true;
        countrySelect.appendChild(opt);
      });
    }

    function populateStates(selectedState) {
      stateSelect.innerHTML = '<option value="" hidden>Select State</option>';
      if (typeof locationData === 'undefined') return;
      const country = countrySelect.value;
      if (!locationData[country]) return;

      Object.keys(locationData[country]).forEach(stateName => {
        const opt = document.createElement('option');
        opt.value = stateName;
        opt.textContent = stateName;
        if (stateName === selectedState) opt.selected = true;
        stateSelect.appendChild(opt);
      });
    }

    function populateCities(selectedCity) {
      citySelect.innerHTML = '<option value="" hidden>Select City</option>';
      if (typeof locationData === 'undefined') return;
      const country = countrySelect.value;
      const state = stateSelect.value;
      if (!locationData[country] || !locationData[country][state]) return;

      locationData[country][state].forEach(cityName => {
        const opt = document.createElement('option');
        opt.value = cityName;
        opt.textContent = cityName;
        if (cityName === selectedCity) opt.selected = true;
        citySelect.appendChild(opt);
      });
    }

    // Country changes -> rebuild states -> rebuild cities (fresh, no preselect)
    countrySelect.addEventListener('change', () => {
      populateStates();
      populateCities();
    });

    // State changes -> rebuild cities
    stateSelect.addEventListener('change', () => {
      populateCities();
    });

    // Initial load: build the full country list, then rebuild the chain
    // using saved DB values (edit mode) or blank (add mode)
    populateCountries(savedCountry);
    if (savedCountry) countrySelect.value = savedCountry;
    populateStates(savedState);
    populateCities(savedCity);

    const requiredFields = [
      { name: 'fullname', label: 'full name' },
      { name: 'username', label: 'username' },
      { name: 'email',    label: 'email' },
      { name: 'password', label: 'password' },
      { name: 'mobile',   label: 'mobile number' },
      { name: 'dob',      label: 'date of birth' },
      { name: 'status',   label: 'status' },
      { name: 'gender',   label: 'gender' },
      { name: 'country',  label: 'country' },
      { name: 'state',    label: 'state' },
      { name: 'city',     label: 'city' },
      { name: 'area',     label: 'area' },
    ];

    function getOrCreateError(field) {
      const target = field.closest('.password-field-wrapper') || field;
      let err = target.parentElement.querySelector('.error-message');
      if (!err) {
        err = document.createElement('div');
        err.className = 'error-message field-error text-xs text-red-500 mt-1';
        target.insertAdjacentElement('afterend', err);
      }
      err.style.display = 'none'; // default hidden
      return err;
    }

    // Hide any existing error text on load
    document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

    function showError(field, msg) {
      field.classList.add('is-invalid');
      const err = getOrCreateError(field);
      err.textContent = msg;
      err.style.display = 'block';
    }

    function clearError(field) {
      field.classList.remove('is-invalid');
      const target = field.closest('.password-field-wrapper') || field;
      const err = target.parentElement.querySelector('.error-message');
      if (err) err.style.display = 'none';
    }

    // Clear error as the user fixes a field
    requiredFields.forEach(({ name }) => {
      const field = form.querySelector(`[name="${name}"]`);
      if (field) {
        field.addEventListener('input', () => clearError(field));
        field.addEventListener('change', () => clearError(field));
      }
    });

    // CAPTURE phase (true) so this runs BEFORE the template's document-level
    // submit handler in main-v4-zXfl5ocF.js (line 2978), which fakes a
    // "Submit ✓" toast and resets the form for every form on the page.
    form.addEventListener('submit', function (e) {
      let firstInvalid = null;

      requiredFields.forEach(({ name, label }) => {
        const field = form.querySelector(`[name="${name}"]`);
        if (!field) return;
        const value = field.value.trim();

        if (!value) 
        {
          showError(field, `Please enter ${label}`);
          if (!firstInvalid) firstInvalid = field;
        } 
        else if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) 
        {
          showError(field, 'Please enter a valid email address');
          if (!firstInvalid) firstInvalid = field;
        } 
        else if (name === 'mobile' && !/^\d{10}$/.test(value)) 
        {
          showError(field, 'Please enter a valid 10-digit mobile number');
          if (!firstInvalid) firstInvalid = field;
        } 
        else {
          clearError(field);
        }
      });

      if (firstInvalid) {
        // Invalid: block submit entirely, don't let it reach the document handler
        e.preventDefault();
        e.stopPropagation();
        firstInvalid.focus();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        // Valid: stop the demo toast/reset handler, but let the form submit to PHP
        e.stopPropagation();
      }
    }, true); // <-- capture phase, critical
  });
</script>

</body>

</html>