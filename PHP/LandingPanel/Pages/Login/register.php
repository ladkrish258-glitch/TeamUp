<?php
$page = 'register';
$pageTitle = 'Register';

require_once dirname(__DIR__) . '/Components/Connection/db.php';
require_once dirname(__DIR__, 3) . '/UserPanel/Pages/Components/Auth/user_remember_me.php';

ensure_user_session_started();

if (!empty($_SESSION['user_id']) || attempt_user_remember_login($pdo)) {
    header('Location: ../../../UserPanel/Pages/Home/home.php');
    exit;
}

$errors = [];

// Keep submitted values so the form can be re-filled on error.
$old = [
    'full_name' => '',
    'username'  => '',
    'email'     => '',
    'mobile'    => '',
    'dob'       => '',
    'gender'    => '',
    'country'   => '',
    'state'     => '',
    'city'      => '',
    'area'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($old as $key => $default) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ---- Step 1: Account ----
    if ($old['full_name'] === '') { $errors[] = 'Please enter your full name.'; }
    if ($old['username'] === '') {
        $errors[] = 'Please choose a username.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $old['username'])) {
        $errors[] = 'Username must be 3-50 characters (letters, numbers, underscore, period only).';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // ---- Step 2: Personal ----
    if ($old['mobile'] === '' || !preg_match('/^\+?[0-9]{7,15}$/', $old['mobile'])) {
        $errors[] = 'Please enter a valid mobile number (7-15 digits).';
    }
    if ($old['dob'] === '') {
        $errors[] = 'Please enter your date of birth.';
    } else {
        $dobTime = strtotime($old['dob']);
        if ($dobTime === false || $dobTime > time()) {
            $errors[] = 'Please enter a valid date of birth.';
        } else {
            $age = floor((time() - $dobTime) / (365.25 * 24 * 60 * 60));
            if ($age < 13) {
                $errors[] = 'You must be at least 13 years old to register.';
            }
        }
    }
    if (!in_array($old['gender'], ['Male', 'Female', 'Other', 'Prefer not to say'], true)) {
        $errors[] = 'Please select a gender option.';
    }

    // ---- Step 3: Location ----
    if ($old['country'] === '') { $errors[] = 'Please enter your country.'; }
    if ($old['state'] === '')   { $errors[] = 'Please enter your state.'; }
    if ($old['city'] === '')    { $errors[] = 'Please enter your city.'; }
    if ($old['area'] === '')    { $errors[] = 'Please enter your area/neighborhood.'; }

    // ---- Step 4: Photo (optional) ----
    $profilePhotoPath = null;
    if (!empty($_FILES['profile_photo']['name'])) {
        $file = $_FILES['profile_photo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'There was a problem uploading your photo.';
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedTypes, true)) {
                $errors[] = 'Profile photo must be a JPG, PNG, or WEBP image.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Profile photo must be under 5 MB.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safeName = 'avatar_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
                $destDir = dirname(__DIR__, 4) . '/images/User/Profile/';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0777, true);
                }
                if (move_uploaded_file($file['tmp_name'], $destDir . $safeName)) {
                    $profilePhotoPath = $safeName;
                } else {
                    $errors[] = 'Could not save your profile photo. Please try again.';
                }
            }
        }
    }

    // ---- Uniqueness checks (only if no earlier errors, to avoid wasted queries) ----
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$old['username'], $old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'That username or email is already registered.';
        }
    }

    // ---- Insert ----
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO users
                (full_name, username, email, password, mobile, dob, status, gender, profile_photo, country, state, city, area, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $old['full_name'],
            $old['username'],
            $old['email'],
            password_hash($password, PASSWORD_DEFAULT),
            $old['mobile'],
            $old['dob'],
            'active',
            $old['gender'],
            $profilePhotoPath,
            $old['country'],
            $old['state'],
            $old['city'],
            $old['area'],
        ]);

        $newUserId = (int)$pdo->lastInsertId();
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['username'] = $old['username'];
        issue_user_remember_token($pdo, $newUserId);
        setcookie('remember_user_identifier', $old['username'], time() + (86400 * 30), '/', '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
        header('Location: ../../../UserPanel/Pages/Home/home.php');
        exit;
    }
}

require_once dirname(__DIR__) . '/Components/Header/header.php';
?>

<?php
$regInput = 'w-full box-border px-3.5 py-3 border-[1.5px] border-line rounded-[10px] text-[15px] leading-snug bg-white outline-none focus:border-brick focus:ring-4 focus:ring-brick/10 [&.invalid]:border-brick disabled:bg-[#f5f2ef] disabled:text-[#a49e98] disabled:cursor-not-allowed transition-all';
$regLabel = 'block font-semibold text-[13px] text-pine-dark mb-2';
$regErr = 'field-error hidden [&.show]:block text-brick text-xs mt-1';
$regBtnPrimary = 'px-[22px] py-3 rounded-[10px] font-bold text-[15px] bg-pine hover:bg-pine-dark text-paper border-0 cursor-pointer hover:-translate-y-0.5 hover:shadow-lg transition-all';
$regBtnBack = 'px-5 py-3 rounded-[10px] font-semibold text-[15px] border border-line bg-white hover:bg-paper-2 text-pine-dark cursor-pointer transition-colors';
$regStepClass = 'reg-progress-step group flex items-center gap-2 text-xs font-semibold text-[#b9b2ac] [&.active]:text-brick';
$regDotClass = 'flex items-center justify-center w-[22px] h-[22px] rounded-full bg-[#f1ece7] text-[#b9b2ac] text-[11px] font-bold group-[.active]:bg-brick group-[.active]:text-white transition-colors';
?>

<section class="py-12 pb-6">
  <div class="max-w-[1160px] mx-auto px-6">
    <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Join in</span>
    <h1 class="font-serif font-semibold text-4xl sm:text-5xl text-pine-dark mt-2 mb-3">Create your account.</h1>
    <p class="text-ink-soft text-lg m-0">Tell us a bit about yourself so we can connect you with the right matches and neighbors.</p>
  </div>
</section>

<section class="pt-6 pb-20">
  <div class="max-w-[560px] mx-auto px-6">

    <div class="bg-white border border-line rounded-[18px] shadow-xl px-9 pt-10 pb-8">

      <div class="text-center mb-7">
        <div class="w-[60px] h-[60px] mx-auto mb-4 flex items-center justify-center rounded-full bg-[#FBEAE3] text-brick" aria-hidden="true">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 20v-1.6a4 4 0 0 0-4-4H6.4a4 4 0 0 0-4 4V20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="8.7" cy="7.4" r="3.4" stroke="currentColor" stroke-width="1.6"/>
            <path d="M19 7.5v6M22 10.5h-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <h2 class="font-serif text-[28px] font-bold tracking-tight text-pine-dark m-0 mb-1.5">Create account</h2>
        <p class="m-0 text-sm text-[#7a7a7a]">Just a few steps to get you set up</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="bg-[#FBEAE3] border border-brick text-brick px-4 py-3 rounded-[10px] mb-5 text-sm">
          <?php foreach ($errors as $err): ?>
            <div>&bull; <?php echo htmlspecialchars($err); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- STEP PROGRESS -->
      <div class="flex justify-between gap-2 mb-8 flex-wrap">
        <span class="<?= $regStepClass ?> active" data-step-label="1"><span class="<?= $regDotClass ?>">1</span>Account</span>
        <span class="<?= $regStepClass ?>" data-step-label="2"><span class="<?= $regDotClass ?>">2</span>Personal</span>
        <span class="<?= $regStepClass ?>" data-step-label="3"><span class="<?= $regDotClass ?>">3</span>Location</span>
        <span class="<?= $regStepClass ?>" data-step-label="4"><span class="<?= $regDotClass ?>">4</span>Photo</span>
      </div>

      <form class="form-grid" method="post" action="../Login/register.php" enctype="multipart/form-data" novalidate>

        <!-- STEP 1: ACCOUNT -->
        <div id="reg-step-1" class="reg-step">
          <div class="field mb-5">
            <label for="full_name" class="<?= $regLabel ?>">Full name</label>
            <input type="text" id="full_name" name="full_name" placeholder="Jamie Rivera" class="<?= $regInput ?>"
                   value="<?php echo htmlspecialchars($old['full_name']); ?>" required>
            <div class="<?= $regErr ?>"></div>
          </div>
          <div class="field mb-5">
            <label for="username" class="<?= $regLabel ?>">Username</label>
            <input type="text" id="username" name="username" placeholder="jamierivera" class="<?= $regInput ?>"
                   value="<?php echo htmlspecialchars($old['username']); ?>" required>
            <div class="<?= $regErr ?>"></div>
          </div>
          <div class="field mb-5">
            <label for="email" class="<?= $regLabel ?>">Email address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" class="<?= $regInput ?>"
                   value="<?php echo htmlspecialchars($old['email']); ?>" required>
            <div class="<?= $regErr ?>"></div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-[18px]">
            <div class="field mb-5">
              <label for="password" class="<?= $regLabel ?>">Password</label>
              <input type="password" id="password" name="password" placeholder="At least 8 characters" class="<?= $regInput ?>" required minlength="8">
              <div class="<?= $regErr ?>"></div>
            </div>
            <div class="field mb-5">
              <label for="confirm_password" class="<?= $regLabel ?>">Confirm password</label>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" class="<?= $regInput ?>" required>
              <div class="<?= $regErr ?>"></div>
            </div>
          </div>

          <div class="flex justify-between items-center mt-5">
            <span></span>
            <button type="button" class="<?= $regBtnPrimary ?>" onclick="if (validateRegStep(1)) goToRegStep(2)">Next</button>
          </div>
        </div>

        <!-- STEP 2: PERSONAL -->
        <div id="reg-step-2" class="reg-step" style="display:none;">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-[18px]">
            <div class="field mb-5">
              <label for="mobile" class="<?= $regLabel ?>">Mobile number</label>
              <input type="tel" id="mobile" name="mobile" placeholder="9876543210" class="<?= $regInput ?>"
                     value="<?php echo htmlspecialchars($old['mobile']); ?>" required>
              <div class="<?= $regErr ?>"></div>
            </div>
            <div class="field mb-5">
              <label for="dob" class="<?= $regLabel ?>">Date of birth</label>
              <input type="date" id="dob" name="dob" class="<?= $regInput ?>"
                     value="<?php echo htmlspecialchars($old['dob']); ?>" required>
              <div class="<?= $regErr ?>"></div>
            </div>
          </div>
          <div class="field mb-5">
            <label for="gender" class="<?= $regLabel ?>">Gender</label>
            <select id="gender" name="gender" class="<?= $regInput ?>" required>
              <option value="" <?php echo $old['gender'] === '' ? 'selected' : ''; ?> hidden>Select gender</option>
              <option value="Male" <?php echo $old['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo $old['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?php echo $old['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
              <option value="Prefer not to say" <?php echo $old['gender'] === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
            </select>
            <div class="<?= $regErr ?>"></div>
          </div>

          <div class="flex justify-between items-center mt-5">
            <button type="button" class="<?= $regBtnBack ?>" onclick="goToRegStep(1)">Back</button>
            <button type="button" class="<?= $regBtnPrimary ?>" onclick="if (validateRegStep(2)) goToRegStep(3)">Next</button>
          </div>
        </div>

        <!-- STEP 3: LOCATION -->
        <div id="reg-step-3" class="reg-step" style="display:none;">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-[18px]">
            <div class="field mb-5">
              <label for="country" class="<?= $regLabel ?>">Country</label>
              <select id="country" name="country" class="<?= $regInput ?>" required data-old="<?php echo htmlspecialchars($old['country']); ?>">
                <option value="" hidden>Loading countries&hellip;</option>
              </select>
              <div class="<?= $regErr ?>"></div>
            </div>
            <div class="field mb-5">
              <label for="state" class="<?= $regLabel ?>">State</label>
              <select id="state" name="state" class="<?= $regInput ?>" required disabled data-old="<?php echo htmlspecialchars($old['state']); ?>">
                <option value="" hidden>Select country first</option>
              </select>
              <div class="<?= $regErr ?>"></div>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-[18px]">
            <div class="field mb-5">
              <label for="city" class="<?= $regLabel ?>">City</label>
              <select id="city" name="city" class="<?= $regInput ?>" required disabled data-old="<?php echo htmlspecialchars($old['city']); ?>">
                <option value="" hidden>Select state first</option>
              </select>
              <div class="<?= $regErr ?>"></div>
            </div>
            <div class="field mb-5">
              <label for="area" class="<?= $regLabel ?>">Area / Neighborhood</label>
              <input type="text" id="area" name="area" placeholder="Maple Street" class="<?= $regInput ?>"
                     value="<?php echo htmlspecialchars($old['area']); ?>" required>
              <div class="<?= $regErr ?>"></div>
            </div>
          </div>

          <div class="flex justify-between items-center mt-5">
            <button type="button" class="<?= $regBtnBack ?>" onclick="goToRegStep(2)">Back</button>
            <button type="button" class="<?= $regBtnPrimary ?>" onclick="if (validateRegStep(3)) goToRegStep(4)">Next</button>
          </div>
        </div>

        <!-- STEP 4: PHOTO -->
        <div id="reg-step-4" class="reg-step" style="display:none;">
          <div class="field mb-5">
            <label for="profile_photo" class="<?= $regLabel ?>">Profile photo <span class="text-ink-soft font-normal">(optional)</span></label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg,image/webp"
                   class="w-full box-border p-3 border-[1.5px] border-dashed border-line rounded-[10px] text-sm">
            <div class="<?= $regErr ?>" id="photo-error"></div>
            <p class="text-[13px] mt-1.5 text-[#7a7a7a] mb-0">JPG, PNG, or WEBP &middot; up to 5 MB.</p>
            <img id="photo-preview" alt="Profile photo preview" class="hidden mt-3 w-24 h-24 object-cover rounded-full border-[1.5px] border-line">
          </div>

          <div class="flex justify-between items-center mt-5">
            <button type="button" class="<?= $regBtnBack ?>" onclick="goToRegStep(3)">Back</button>
            <button type="submit" class="<?= $regBtnPrimary ?>">Create account</button>
          </div>
        </div>

        <p class="text-center text-[13px] mt-4 text-[#7a7a7a] mb-0">Already have an account? <a href="../Login/login.php" class="text-brick font-semibold no-underline hover:underline">Log in</a>.</p>
      </form>

    </div>

  </div>
</section>

<script>
  const regSteps = [1, 2, 3, 4];

  function goToRegStep(step) {
    regSteps.forEach(function(s) {
      document.getElementById('reg-step-' + s).style.display = (s === step) ? 'block' : 'none';
    });
    document.querySelectorAll('.reg-progress-step').forEach(function(el, i) {
      el.classList.toggle('active', (i + 1) === step);
    });
  }

  function fieldOf(id) {
    return document.getElementById(id);
  }

  function showError(input, message) {
    input.classList.add('invalid');
    const errorEl = input.closest('.field').querySelector('.field-error');
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.add('show');
    }
  }

  function clearError(input) {
    input.classList.remove('invalid');
    const errorEl = input.closest('.field').querySelector('.field-error');
    if (errorEl) {
      errorEl.classList.remove('show');
      errorEl.textContent = '';
    }
  }

  document.querySelectorAll('.reg-step input, .reg-step select').forEach(function(input) {
    input.addEventListener('input', function() { clearError(input); });
    input.addEventListener('change', function() { clearError(input); });
  });

  // ---- Country / State / City cascading dropdowns ----
  // Data source: CountriesNow (free, no API key) — https://countriesnow.space
  const COUNTRY_API = 'https://countriesnow.space/api/v0.1/countries/positions';
  const STATE_API = 'https://countriesnow.space/api/v0.1/countries/states';
  const CITY_API = 'https://countriesnow.space/api/v0.1/countries/state/cities';

  const countrySelect = fieldOf('country');
  const stateSelect = fieldOf('state');
  const citySelect = fieldOf('city');

  function setOptions(select, items, placeholder) {
    select.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.hidden = true;
    ph.textContent = placeholder;
    select.appendChild(ph);
    items.forEach(function(item) {
      const opt = document.createElement('option');
      opt.value = item;
      opt.textContent = item;
      select.appendChild(opt);
    });
    select.value = '';
  }

  function loadCountries() {
    countrySelect.disabled = true;
    fetch(COUNTRY_API)
      .then(function(res) { return res.json(); })
      .then(function(data) {
        const countries = (data.data || [])
          .map(function(c) { return c.name; })
          .sort(function(a, b) { return a.localeCompare(b); });
        setOptions(countrySelect, countries, 'Select country');
        countrySelect.disabled = false;

        const oldCountry = countrySelect.getAttribute('data-old');
        if (oldCountry && countries.indexOf(oldCountry) !== -1) {
          countrySelect.value = oldCountry;
          loadStates(oldCountry, stateSelect.getAttribute('data-old'));
        }
      })
      .catch(function() {
        setOptions(countrySelect, [], 'Could not load countries');
        showError(countrySelect, 'Could not load the country list. Please check your connection and refresh.');
      });
  }

  function loadStates(country, preselectState) {
    stateSelect.disabled = true;
    citySelect.disabled = true;
    setOptions(stateSelect, [], 'Loading states\u2026');
    setOptions(citySelect, [], 'Select state first');

    fetch(STATE_API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ country: country })
    })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        const states = ((data.data && data.data.states) || []).map(function(s) { return s.name; });
        if (states.length === 0) {
          setOptions(stateSelect, [], 'No states available');
          return;
        }
        setOptions(stateSelect, states, 'Select state');
        stateSelect.disabled = false;

        if (preselectState && states.indexOf(preselectState) !== -1) {
          stateSelect.value = preselectState;
          loadCities(country, preselectState, citySelect.getAttribute('data-old'));
        }
      })
      .catch(function() {
        setOptions(stateSelect, [], 'Could not load states');
        showError(stateSelect, 'Could not load the state list. Please try again.');
      });
  }

  function loadCities(country, state, preselectCity) {
    citySelect.disabled = true;
    setOptions(citySelect, [], 'Loading cities\u2026');

    fetch(CITY_API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ country: country, state: state })
    })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        const cities = data.data || [];
        if (cities.length === 0) {
          setOptions(citySelect, [], 'No cities available');
          return;
        }
        setOptions(citySelect, cities, 'Select city');
        citySelect.disabled = false;

        if (preselectCity && cities.indexOf(preselectCity) !== -1) {
          citySelect.value = preselectCity;
        }
      })
      .catch(function() {
        setOptions(citySelect, [], 'Could not load cities');
        showError(citySelect, 'Could not load the city list. Please try again.');
      });
  }

  countrySelect.addEventListener('change', function() {
    clearError(countrySelect);
    clearError(stateSelect);
    clearError(citySelect);
    if (countrySelect.value) {
      loadStates(countrySelect.value, null);
    } else {
      setOptions(stateSelect, [], 'Select country first');
      stateSelect.disabled = true;
      setOptions(citySelect, [], 'Select state first');
      citySelect.disabled = true;
    }
  });

  stateSelect.addEventListener('change', function() {
    clearError(stateSelect);
    clearError(citySelect);
    if (stateSelect.value) {
      loadCities(countrySelect.value, stateSelect.value, null);
    } else {
      setOptions(citySelect, [], 'Select state first');
      citySelect.disabled = true;
    }
  });

  loadCountries();

  function validateRegStep(step) {
    let valid = true;

    if (step === 1) {
      const fullName = fieldOf('full_name');
      const username = fieldOf('username');
      const email = fieldOf('email');
      const password = fieldOf('password');
      const confirmPassword = fieldOf('confirm_password');

      if (!fullName.value.trim()) { showError(fullName, 'Please enter your full name.'); valid = false; }
      if (!/^[a-zA-Z0-9_.]{3,50}$/.test(username.value.trim())) {
        showError(username, '3-50 characters: letters, numbers, underscore, period.');
        valid = false;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showError(email, 'Please enter a valid email address.');
        valid = false;
      }
      if (password.value.length < 8) {
        showError(password, 'Password must be at least 8 characters.');
        valid = false;
      }
      if (password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Passwords do not match.');
        valid = false;
      }
    }

    if (step === 2) {
      const mobile = fieldOf('mobile');
      const dob = fieldOf('dob');
      const gender = fieldOf('gender');

      if (!/^\+?[0-9]{7,15}$/.test(mobile.value.trim())) {
        showError(mobile, 'Enter a valid mobile number (7-15 digits).');
        valid = false;
      }
      if (!dob.value) {
        showError(dob, 'Please enter your date of birth.');
        valid = false;
      } else {
        const dobDate = new Date(dob.value);
        const ageMs = Date.now() - dobDate.getTime();
        const age = ageMs / (1000 * 60 * 60 * 24 * 365.25);
        if (dobDate > new Date()) {
          showError(dob, 'Date of birth cannot be in the future.');
          valid = false;
        } else if (age < 13) {
          showError(dob, 'You must be at least 13 years old.');
          valid = false;
        }
      }
      if (!gender.value) {
        showError(gender, 'Please select a gender option.');
        valid = false;
      }
    }

    if (step === 3) {
      ['country', 'state', 'city', 'area'].forEach(function(id) {
        const input = fieldOf(id);
        if (!input.value.trim()) {
          showError(input, 'This field is required.');
          valid = false;
        }
      });
    }

    return valid;
  }

  // ---- Photo validation (step 4, checked on submit since it's optional) ----
  const MAX_PHOTO_SIZE = 5 * 1024 * 1024;
  const photoInput = document.getElementById('profile_photo');
  const photoError = document.getElementById('photo-error');
  const photoPreview = document.getElementById('photo-preview');

  photoInput.addEventListener('change', function() {
    photoError.classList.remove('show');
    photoInput.classList.remove('invalid');
    photoPreview.style.display = 'none';
    photoPreview.src = '';

    const file = photoInput.files[0];
    if (!file) return;

    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowed.includes(file.type)) {
      photoError.textContent = 'Please upload a JPG, PNG, or WEBP image.';
      photoError.classList.add('show');
      photoInput.classList.add('invalid');
      photoInput.value = '';
      return;
    }
    if (file.size > MAX_PHOTO_SIZE) {
      photoError.textContent = 'Photo must be under 5 MB.';
      photoError.classList.add('show');
      photoInput.classList.add('invalid');
      photoInput.value = '';
      return;
    }

    // Confirm the browser can actually decode it as an image before accepting.
    const reader = new FileReader();
    reader.onload = function(e) {
      const testImg = new Image();
      testImg.onload = function() {
        photoPreview.src = e.target.result;
        photoPreview.style.display = 'inline-block';
      };
      testImg.onerror = function() {
        photoError.textContent = 'That file doesn\'t look like a valid image. Please choose another.';
        photoError.classList.add('show');
        photoInput.classList.add('invalid');
        photoInput.value = '';
      };
      testImg.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });

  document.querySelector('form.form-grid').addEventListener('submit', function(e) {
    if (!validateRegStep(1) || !validateRegStep(2) || !validateRegStep(3)) {
      e.preventDefault();
      // Jump back to the first step that has an error.
      if (!validateRegStep(1)) { goToRegStep(1); }
      else if (!validateRegStep(2)) { goToRegStep(2); }
      else { goToRegStep(3); }
    }
  });
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>