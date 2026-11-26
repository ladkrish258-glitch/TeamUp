<?php
$page = 'post';
$pageTitle = 'Post';
require_once dirname(__DIR__) . '/Components/Header/header.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

$avatarSrc = !empty($profile['profile_photo']) ? htmlspecialchars($profile['profile_photo']) : '../../../../images/default/default.png';
if (strpos($avatarSrc, '/Team-Up/') === 0) {
    $avatarSrc = '../../../../' . substr($avatarSrc, 9);
} elseif (!empty($profile['profile_photo']) && strpos($avatarSrc, 'http') !== 0 && strpos($avatarSrc, '/') !== 0 && strpos($avatarSrc, '../') !== 0) {
    $avatarSrc = '../../../../images/User/Profile/' . ltrim($avatarSrc, '/');
}

// Fetch sports list for the dropdown
$sports = $pdo->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name")->fetchAll(PDO::FETCH_ASSOC);

$pInput = 'peer w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 text-sm outline-none focus:border-nd-green [&.is-invalid]:border-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed';
$pLabel = 'block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1.5';
$pErr = 'invalid-feedback hidden peer-[.is-invalid]:block text-red-500 text-xs mt-1 font-medium';
$pBtnPrimary = 'inline-flex items-center justify-center px-6 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors';
$pBtnBack = 'inline-flex items-center justify-center px-5 py-2.5 rounded-full font-semibold text-sm bg-transparent border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors';
?>

<form method="POST" action="../Post/create_post.php" enctype="multipart/form-data" class="nd-composer-form bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex flex-col gap-3 mb-6">

    <!-- STEP 1: Post Details -->
    <div id="step-1" class="nd-form-step">
        <h4 class="font-bold text-xl mb-4 text-center text-gray-900 dark:text-gray-50">Post Details</h4>

        <div class="mb-4">
            <label for="post_content" class="<?= $pLabel ?>">Content:</label>
            <textarea
                id="post_content"
                name="post_content"
                class="<?= $pInput ?> resize-y min-h-[88px]"
                placeholder="What's on your mind, <?= htmlspecialchars($currentUser['full_name']) ?>? Ask for a match, share an update..."
                rows="3"
                maxlength="2000"></textarea>
            <div class="<?= $pErr ?>" id="post-content-feedback">Please add some content (at least 3 characters) or upload a photo.</div>
        </div>

        <!-- PHOTO UPLOAD DROPZONE -->
        <div class="mb-4">
            <label class="<?= $pLabel ?>">Upload Photo</label>
            <p class="text-xs text-gray-500 dark:text-gray-400 m-0 mb-2">Drag &amp; drop or click to browse &middot; Max 10 MB</p>

            <div id="upload-dropzone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-7 text-center cursor-pointer bg-gray-100 dark:bg-gray-900 hover:border-nd-green hover:bg-nd-green/5 [&.nd-dropzone-active]:border-nd-green [&.nd-dropzone-active]:bg-nd-green/10 transition-colors">

                <input
                    type="file"
                    id="post_photo"
                    name="post_photo"
                    class="hidden"
                    accept="image/*"
                    onchange="handleFileSelect(event)">
                <div class="<?= $pErr ?>" id="post-photo-feedback">Please upload an image file under 10 MB.</div>

                <div id="dropzone-empty" class="flex flex-col items-center gap-1.5">
                    <svg viewBox="0 0 24 24" class="w-10 h-10 fill-nd-green">
                        <path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z" />
                    </svg>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Drop a photo here or click to browse</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG up to 10 MB</div>
                </div>

                <div id="dropzone-preview" class="flex flex-col items-center gap-2.5" style="display: none;">
                    <img
                        id="image-preview"
                        class="w-full max-h-[320px] rounded-xl object-contain"
                        alt="Preview">
                    <div class="flex items-center justify-between w-full gap-3 pt-1">
                        <span id="preview-filename" class="text-xs text-gray-500 dark:text-gray-400 truncate"></span>
                        <button type="button" class="nd-remove-photo px-3 py-1 rounded-full text-xs font-semibold text-red-500 border border-red-500/30 hover:bg-red-500 hover:text-white bg-transparent cursor-pointer transition-colors" onclick="removePhoto(event)">Remove</button>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Click the photo to change it</div>
                </div>

            </div>
        </div>

        <div class="flex justify-end items-center border-t border-gray-200 dark:border-gray-700 pt-4">
            <button type="button" class="<?= $pBtnPrimary ?>" onclick="if (validateStep1()) goToStep2()">Next</button>
        </div>

    </div>

    <!-- STEP 2: Sport Details -->
    <div id="step-2" class="nd-form-step" style="display: none;">
        <h4 class="font-bold text-xl mb-4 text-center text-gray-900 dark:text-gray-50">Sport Details</h4>

        <div class="mb-4">
            <label for="sport_id" class="<?= $pLabel ?>">Sport</label>
            <select id="sport_id" name="sport_id" class="<?= $pInput ?>" required>
                <option value="" hidden>Select a sport</option>
                <?php foreach ($sports as $sport): ?>
                    <option value="<?= $sport['sport_id'] ?>"><?= htmlspecialchars($sport['sport_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="<?= $pErr ?>">Please select a sport.</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="min_players" class="<?= $pLabel ?>">Minimum Players</label>
                <input type="number" id="min_players" name="min_players" class="<?= $pInput ?>" min="1" required>
                <div class="<?= $pErr ?>">Enter a minimum of at least 1 player.</div>
            </div>
            <div>
                <label for="max_players" class="<?= $pLabel ?>">Maximum Players</label>
                <input type="number" id="max_players" name="max_players" class="<?= $pInput ?>" min="1" required>
                <div class="<?= $pErr ?>" id="max-players-feedback">Maximum must be greater than or equal to minimum.</div>
            </div>
        </div>

        <div class="mb-4">
            <label for="sport_description" class="<?= $pLabel ?>">Sport Related Description</label>
            <textarea
                id="sport_description"
                name="sport_description"
                class="<?= $pInput ?>"
                placeholder="e.g. ground rules, skill level, equipment needed..."
                rows="3"
                minlength="10"
                maxlength="1000"
                required></textarea>
            <div class="<?= $pErr ?>" id="sport-description-feedback">Please enter a description of at least 10 characters.</div>
        </div>

        <div class="mb-4">
            <label for="skill_level" class="<?= $pLabel ?>">Skill Level</label>
            <select id="skill_level" name="skill_level" class="<?= $pInput ?>" required>
                <option value="" hidden>Select skill level</option>
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
            </select>
            <div class="<?= $pErr ?>">Please select a skill level.</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
                <label for="experience_years" class="<?= $pLabel ?>">Experience (Years)</label>
                <input type="number" id="experience_years" name="experience_years" class="<?= $pInput ?>" min="0" max="50" required>
                <div class="<?= $pErr ?>">Enter experience between 0 and 50 years.</div>
            </div>
            <div>
                <label for="preferred_position" class="<?= $pLabel ?>">Preferred Position</label>
                <input type="text" id="preferred_position" name="preferred_position" class="<?= $pInput ?>" maxlength="50" required placeholder="e.g. Striker, Batsman, Goalkeeper">
                <div class="<?= $pErr ?>">Please enter a preferred position.</div>
            </div>
        </div>

        <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
            <button type="button" class="<?= $pBtnBack ?>" onclick="backToStep1()">Back</button>
            <button type="button" class="<?= $pBtnPrimary ?>" onclick="goToStep3()">Next</button>
        </div>

    </div>


    <!-- STEP 3: Match Details -->
    <div id="step-3" class="nd-form-step" style="display: none;">
        <h4 class="font-bold text-xl mb-5 text-center text-gray-900 dark:text-gray-50">Match Details</h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="match_date" class="<?= $pLabel ?>">Match Date</label>
                <input type="date" id="match_date" name="match_date" class="<?= $pInput ?>" required>
                <div class="<?= $pErr ?>">Please select a match date.</div>
            </div>
            <div>
                <label for="skill_level_match" class="<?= $pLabel ?>">Skill Level</label>
                <select id="skill_level_match" name="skill_level_match" class="<?= $pInput ?>" required>
                    <option value="" hidden>Select skill level</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
                <div class="<?= $pErr ?>">Please select a skill level.</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="start_time" class="<?= $pLabel ?>">Start Time</label>
                <input type="time" id="start_time" name="start_time" class="<?= $pInput ?>" required>
                <div class="<?= $pErr ?>">Please select a start time.</div>
            </div>
            <div>
                <label for="end_time" class="<?= $pLabel ?>">End Time</label>
                <input type="time" id="end_time" name="end_time" class="<?= $pInput ?>" required>
                <div class="<?= $pErr ?>" id="end-time-feedback">End time must be after start time.</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
                <label for="visibility" class="<?= $pLabel ?>">Visibility</label>
                <select id="visibility" name="visibility" class="<?= $pInput ?>" required>
                    <option value="" hidden>Select visibility</option>
                    <option value="Public">Public</option>
                    <option value="Private">Private</option>
                </select>
                <div class="<?= $pErr ?>">Please select visibility.</div>
            </div>
            <div>
                <label for="entry_fee" class="<?= $pLabel ?>">Entry Fee</label>
                <input type="number" id="entry_fee" name="entry_fee" class="<?= $pInput ?>" min="0" step="0.01" placeholder="0.00">
                <div class="<?= $pErr ?>">Enter a valid entry fee.</div>
            </div>
        </div>

        <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" class="<?= $pBtnBack ?>" onclick="backToStep2()">Back</button>
            <button type="button" class="<?= $pBtnPrimary ?>" onclick="goToStep4()">Next</button>
        </div>

    </div>


    <!-- STEP 4: Ground Details -->
    <div id="step-4" class="nd-form-step" style="display: none;">
        <h4 class="font-bold text-xl mb-5 text-center text-gray-900 dark:text-gray-50">Ground Details</h4>

        <div class="mb-4">
            <label for="ground_city" class="<?= $pLabel ?>">City</label>
            <select id="ground_city" name="ground_city" class="<?= $pInput ?>" required onchange="loadGrounds()">
                <option value="" hidden>Select a city</option>
                <?php
                $cities = $pdo->query("SELECT DISTINCT city FROM grounds WHERE status = 'Available' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($cities as $cityOption):
                ?>
                    <option value="<?= htmlspecialchars($cityOption) ?>"><?= htmlspecialchars($cityOption) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="<?= $pErr ?>">Please select a city.</div>
        </div>

        <div class="mb-5">
            <label for="ground_id" class="<?= $pLabel ?>">Ground</label>
            <select id="ground_id" name="ground_id" class="<?= $pInput ?>" required disabled>
                <option value="">Select a city first</option>
            </select>
            <div class="<?= $pErr ?>">Please select a ground.</div>
        </div>

        <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" class="<?= $pBtnBack ?>" onclick="backToStep3()">Back</button>
            <button type="button" class="<?= $pBtnPrimary ?>" onclick="goToStep5()">Next</button>
        </div>

    </div>
    <!-- STEP 5: Preview -->
    <div id="step-5" class="nd-form-step" style="display: none;">
        <h4 class="font-bold text-xl mb-5 text-center text-gray-900 dark:text-gray-50">Preview</h4>

        <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-5">

            <div class="flex items-center gap-3 mb-3">
                <img id="preview-user-avatar" src="<?= $avatarSrc ?>" alt="Profile" class="w-11 h-11 rounded-full object-cover">
                <span id="preview-user-name" class="font-bold text-base text-gray-900 dark:text-gray-50"><?= htmlspecialchars($currentUser['full_name']) ?></span>
            </div>

            <p id="preview-content-text" class="text-[15px] text-gray-800 dark:text-gray-100 leading-relaxed mb-3.5"></p>

            <img id="preview-photo" class="w-full max-h-[340px] object-contain rounded-xl mb-4" style="display: none;" alt="Post photo">

            <ul class="list-none m-0 p-0 grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-sm text-gray-700 dark:text-gray-200">
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700"><span>🏅</span> <span id="preview-sport"></span></li>
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700"><span>🎯</span> <span id="preview-skill"></span></li>
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700"><span>👥</span> <span id="preview-players"></span></li>
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700"><span>📅</span> <span id="preview-date"></span></li>
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700"><span>⏰</span> <span id="preview-time"></span></li>
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700"><span>📍</span> <span id="preview-ground"></span></li>
                <li class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 sm:col-span-2"><span>💰</span> <span id="preview-fee"></span></li>
            </ul>

        </div>

        <div class="flex justify-between pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" class="<?= $pBtnBack ?>" onclick="backToStep4()">Back</button>
            <button type="submit" class="<?= $pBtnPrimary ?>">Create Match</button>
        </div>

    </div>

</form>



<script>
    // ---------- STEP NAVIGATION ----------
    function goToStep2() {
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('step-2').style.display = 'block';
    }

    function backToStep1() {
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-1').style.display = 'block';
    }

    function goToStep3() {
        if (!validateStep2()) return;
        document.getElementById('step-2').style.display = 'none';
        document.getElementById('step-3').style.display = 'block';
    }

    function backToStep2() {
        document.getElementById('step-3').style.display = 'none';
        document.getElementById('step-2').style.display = 'block';
    }

    function goToStep4() {
        if (!validateStep3()) return;
        document.getElementById('step-3').style.display = 'none';
        document.getElementById('step-4').style.display = 'block';
    }

    function backToStep3() {
        document.getElementById('step-4').style.display = 'none';
        document.getElementById('step-3').style.display = 'block';
    }

    function goToStep5() {
        if (!validateStep4()) return;
        populatePreview();
        document.getElementById('step-4').style.display = 'none';
        document.getElementById('step-5').style.display = 'block';
    }

    function backToStep4() {
        document.getElementById('step-5').style.display = 'none';
        document.getElementById('step-4').style.display = 'block';
    }

    // ---------- PHOTO UPLOAD (DRAG & DROP) ----------
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('post_photo');
    const emptyState = document.getElementById('dropzone-empty');
    const previewState = document.getElementById('dropzone-preview');
    const imagePreview = document.getElementById('image-preview');
    const previewFilename = document.getElementById('preview-filename');

    function triggerFileInput(event) {
        if (event) event.stopPropagation();
        fileInput.click();
    }

    // Allow clicking anywhere inside the dropzone
    dropzone.addEventListener('click', function(e) {
        if (e.target.closest('.nd-remove-photo')) return;
        fileInput.click();
    });

    const MAX_PHOTO_SIZE = 10 * 1024 * 1024; // 10 MB

    function handleFileSelect(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;

        if (!validatePhotoFile(file)) {
            event.target.value = '';
            return;
        }

        showPreview(file);
    }

    function validatePhotoFile(file) {
        const feedback = document.getElementById('post-photo-feedback');
        if (!file.type.startsWith('image/')) {
            feedback.textContent = 'Please upload a valid image file (PNG, JPG, WebP, etc).';
            feedback.style.display = 'block';
            dropzone.classList.add('border-danger');
            fileInput.classList.add('is-invalid');
            return false;
        }
        if (file.size > MAX_PHOTO_SIZE) {
            feedback.textContent = 'Photo must be under 10 MB.';
            feedback.style.display = 'block';
            dropzone.classList.add('border-danger');
            fileInput.classList.add('is-invalid');
            return false;
        }
        feedback.style.display = 'none';
        dropzone.classList.remove('border-danger');
        fileInput.classList.remove('is-invalid');
        return true;
    }

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            previewFilename.textContent = file.name;
            emptyState.style.display = 'none';
            previewState.style.display = 'flex';
            const feedback = document.getElementById('post-photo-feedback');
            if (feedback) feedback.style.display = 'none';
            dropzone.classList.remove('border-danger');
        };
        reader.readAsDataURL(file);
    }

    function removePhoto(event) {
        if (event) event.stopPropagation();
        fileInput.value = '';
        imagePreview.src = '';
        previewFilename.textContent = '';
        emptyState.style.display = 'flex';
        previewState.style.display = 'none';
        const previewPhoto = document.getElementById('preview-photo');
        if (previewPhoto) {
            previewPhoto.src = '';
            previewPhoto.style.display = 'none';
        }
        const feedback = document.getElementById('post-photo-feedback');
        if (feedback) feedback.style.display = 'none';
        dropzone.classList.remove('border-danger');
    }

    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('nd-dropzone-active');
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropzone.classList.remove('nd-dropzone-active');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('nd-dropzone-active');

        const file = e.dataTransfer.files[0];
        if (file && validatePhotoFile(file)) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            } catch (err) {
                fileInput.files = e.dataTransfer.files;
            }
            showPreview(file);
        }
    });

    // ---------- STEP 1 VALIDATION (runs on "Next") ----------
    const postContentInput = document.getElementById('post_content');

    function validateStep1() {
        let valid = true;

        const hasContent = postContentInput.value.trim().length >= 3;
        const hasPhoto = fileInput.files && fileInput.files.length > 0;

        if (!hasContent && !hasPhoto) {
            postContentInput.classList.add('is-invalid');
            document.getElementById('post-content-feedback').textContent =
                'Please add some content (at least 3 characters) or upload a photo.';
            valid = false;
        } else if (postContentInput.value.trim().length > 0 && postContentInput.value.trim().length < 3) {
            postContentInput.classList.add('is-invalid');
            document.getElementById('post-content-feedback').textContent =
                'Content must be at least 3 characters long.';
            valid = false;
        } else {
            postContentInput.classList.remove('is-invalid');
        }

        return valid;
    }

    postContentInput.addEventListener('input', function() {
        postContentInput.classList.remove('is-invalid');
    });

    // ---------- STEP 2 VALIDATION (runs on "Next") ----------
    const minPlayersInput = document.getElementById('min_players');
    const maxPlayersInput = document.getElementById('max_players');

    function validateStep2() {
        let valid = true;

        const step2Fields = document.querySelectorAll('#step-2 [required]');
        step2Fields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        const min = parseInt(minPlayersInput.value, 10);
        const max = parseInt(maxPlayersInput.value, 10);
        if (!isNaN(min) && !isNaN(max) && max < min) {
            maxPlayersInput.classList.add('is-invalid');
            valid = false;
        } else {
            maxPlayersInput.classList.remove('is-invalid');
        }

        const sportDescInput = document.getElementById('sport_description');
        const descFeedback = document.getElementById('sport-description-feedback');
        const descLength = sportDescInput.value.trim().length;
        if (descLength === 0) {
            sportDescInput.classList.add('is-invalid');
            descFeedback.textContent = 'Please enter a sport related description.';
            valid = false;
        } else if (descLength < 10) {
            sportDescInput.classList.add('is-invalid');
            descFeedback.textContent = 'Description must be at least 10 characters long.';
            valid = false;
        } else {
            sportDescInput.classList.remove('is-invalid');
        }

        return valid;
    }

    document.querySelectorAll('#step-2 [required]').forEach(function(field) {
        field.addEventListener('input', function() {
            field.classList.remove('is-invalid');
        });
        field.addEventListener('change', function() {
            field.classList.remove('is-invalid');
        });
    });

    // ---------- STEP 3 VALIDATION (runs on "Next") ----------
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    function validateStep3() {
        let valid = true;

        const step3Fields = document.querySelectorAll('#step-3 [required]');
        step3Fields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (startTimeInput.value && endTimeInput.value && endTimeInput.value <= startTimeInput.value) {
            endTimeInput.classList.add('is-invalid');
            valid = false;
        } else {
            endTimeInput.classList.remove('is-invalid');
        }

        const entryFeeInput = document.getElementById('entry_fee');
        const feeFeedback = entryFeeInput.parentElement.querySelector('.invalid-feedback');
        if (entryFeeInput.value.trim() !== '') {
            const fee = parseFloat(entryFeeInput.value);
            if (isNaN(fee) || fee < 0) {
                entryFeeInput.classList.add('is-invalid');
                feeFeedback.textContent = 'Entry fee cannot be negative.';
                valid = false;
            } else if (fee > 100000) {
                entryFeeInput.classList.add('is-invalid');
                feeFeedback.textContent = 'Entry fee seems too high — please double-check.';
                valid = false;
            } else {
                entryFeeInput.classList.remove('is-invalid');
            }
        } else {
            entryFeeInput.classList.remove('is-invalid');
        }

        return valid;
    }

    document.querySelectorAll('#step-3 [required]').forEach(function(field) {
        field.addEventListener('input', function() {
            field.classList.remove('is-invalid');
        });
        field.addEventListener('change', function() {
            field.classList.remove('is-invalid');
        });
    });

    document.getElementById('entry_fee').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });

    // ---------- STEP 4 VALIDATION (runs on "Next") ----------
    function validateStep4() {
        let valid = true;

        const step4Fields = document.querySelectorAll('#step-4 [required]:not(:disabled)');
        step4Fields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return valid;
    }

    document.querySelectorAll('#step-4 [required]').forEach(function(field) {
        field.addEventListener('input', function() {
            field.classList.remove('is-invalid');
        });
        field.addEventListener('change', function() {
            field.classList.remove('is-invalid');
        });
    });

    // ---------- GROUND LOADING ----------
    function loadGrounds() {
        const city = document.getElementById('ground_city').value;
        const sportId = document.getElementById('sport_id').value;
        const groundSelect = document.getElementById('ground_id');

        groundSelect.innerHTML = '';
        groundSelect.disabled = true;

        if (!city) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Select a city first';
            groundSelect.appendChild(opt);
            return;
        }

        const loadingOpt = document.createElement('option');
        loadingOpt.textContent = 'Loading...';
        groundSelect.appendChild(loadingOpt);

        fetch('../Grounds/get_grounds.php?city=' + encodeURIComponent(city) + '&sport_id=' + encodeURIComponent(sportId))
            .then(function(res) {
                if (!res.ok) {
                    throw new Error('Server returned ' + res.status);
                }
                return res.json();
            })
            .then(function(grounds) {
                groundSelect.innerHTML = '';

                if (grounds.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No grounds found for this city/sport';
                    groundSelect.appendChild(opt);
                    groundSelect.disabled = true;
                    return;
                }

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = 'Select a ground';
                groundSelect.appendChild(defaultOpt);

                grounds.forEach(function(g) {
                    const opt = document.createElement('option');
                    opt.value = g.ground_id;
                    opt.textContent = g.ground_name;
                    groundSelect.appendChild(opt);
                });

                groundSelect.disabled = false;
            })
            .catch(function(err) {
                console.error('Fetch error:', err);
                groundSelect.innerHTML = '';
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Error loading grounds';
                groundSelect.appendChild(opt);
            });
    }

    // ---------- PREVIEW POPULATION ----------
    function formatTime12h(time24) {
        if (!time24) return '';
        const [h, m] = time24.split(':');
        const hour = parseInt(h, 10);
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const hour12 = ((hour + 11) % 12) + 1;
        return hour12 + ':' + m + ' ' + suffix;
    }

    function formatDateReadable(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }

    function populatePreview() {
        document.getElementById('preview-content-text').textContent = document.getElementById('post_content').value || '(No description added)';

        const previewPhoto = document.getElementById('preview-photo');
        if (imagePreview.src && imagePreview.src.startsWith('data:') && fileInput.files && fileInput.files.length > 0) {
            previewPhoto.src = imagePreview.src;
            previewPhoto.style.display = 'block';
        } else {
            previewPhoto.src = '';
            previewPhoto.style.display = 'none';
        }

        const sportSelect = document.getElementById('sport_id');
        const sportName = sportSelect.options[sportSelect.selectedIndex] ? sportSelect.options[sportSelect.selectedIndex].text : '';
        document.getElementById('preview-sport').textContent = sportName;

        const skillLevel = document.getElementById('skill_level_match').value || document.getElementById('skill_level').value;
        document.getElementById('preview-skill').textContent = skillLevel;

        const minP = document.getElementById('min_players').value;
        const maxP = document.getElementById('max_players').value;
        document.getElementById('preview-players').textContent = minP + ' - ' + maxP + ' Players';

        document.getElementById('preview-date').textContent = formatDateReadable(document.getElementById('match_date').value);

        const startT = formatTime12h(document.getElementById('start_time').value);
        const endT = formatTime12h(document.getElementById('end_time').value);
        document.getElementById('preview-time').textContent = startT + ' - ' + endT;

        const groundSelect = document.getElementById('ground_id');
        const groundName = groundSelect.options[groundSelect.selectedIndex] ? groundSelect.options[groundSelect.selectedIndex].text : '';
        const   cityName = document.getElementById('ground_city').value;
        document.getElementById('preview-ground').textContent = groundName + (cityName ? ', ' + cityName : '');

        const fee = document.getElementById('entry_fee').value;
        document.getElementById('preview-fee').textContent = fee ? '\u20B9' + fee : 'Free';
    }

    // ---------- FINAL SUBMIT (Step 5 has no inputs to validate) ----------
    const postForm = document.querySelector('.nd-composer-form');

    postForm.addEventListener('submit', function(e) {
        // All validation already happened in steps 2-4 before reaching preview.
        // Nothing further to check here since step-5 is read-only.
    });
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>