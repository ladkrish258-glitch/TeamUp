<?php
// Suppress raw HTML errors so PHP never corrupts the JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Ensure clean JSON output
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  // 1. Resolve Admin ID safely
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
    ob_end_clean();
    echo json_encode(['ok' => false, 'error' => 'Unauthorized session. Please log in again.']);
    exit;
  }

  // 2. Database connection (matches profile.php — connect.php is not used in this project)
  $conn = mysqli_connect("localhost", "root", "", "teamup");

  if (!$conn) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
  }

  // 3. Read form input data
  $fullName = trim($_POST['full_name'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $mobile   = trim($_POST['mobile'] ?? '');
  // Status is read-only in profile.php and intentionally NOT accepted here —
  // it must only ever be changed by a separate admin-management flow.

  // Fallback if full_name wasn't combined in JS
  if (empty($fullName)) {
    $fname = trim($_POST['first_name'] ?? '');
    $lname = trim($_POST['last_name'] ?? '');
    $fullName = trim($fname . ' ' . $lname);
  }

  if (empty($fullName)) {
    ob_end_clean();
    echo json_encode(['ok' => false, 'error' => 'Full Name cannot be empty.']);
    exit;
  }

  if (empty($email)) {
    ob_end_clean();
    echo json_encode(['ok' => false, 'errors' => ['email' => 'Email is required.']]);
    exit;
  }

  // 4. Handle avatar upload if sent
  $avatarPath = null;
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = dirname(__DIR__, 4) . '/images/Admin/'; // filesystem: up out of Components/Auth, into project root, then images/Admin
    if (!is_dir($uploadDir)) {
      if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['ok' => false, 'errors' => ['avatar' => 'Could not create upload folder at ' . $uploadDir . '. PHP does not have permission to create it — check folder ownership/permissions on the parent "images" directory.']]);
        exit;
      }
    }
    if (!is_writable($uploadDir)) {
      ob_end_clean();
      http_response_code(500);
      echo json_encode(['ok' => false, 'errors' => ['avatar' => 'Upload folder exists at ' . $uploadDir . ' but is not writable by PHP. Fix its permissions (e.g. chmod 775 or check ownership).']]);
      exit;
    }

    $fileTmp   = $_FILES['avatar']['tmp_name'];
    $fileName  = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['avatar']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmp, $targetFile)) {
      $avatarPath = '/Team-Up/images/Admin/' . $fileName; // browser URL
    } else {
      ob_end_clean();
      http_response_code(500);
      echo json_encode(['ok' => false, 'errors' => ['avatar' => 'Folder is writable, but move_uploaded_file() failed writing to ' . $targetFile . '. This usually means the upload itself was interrupted or tmp_name is invalid.']]);
      exit;
    }
  }

  // 5. Perform SQL Update (status column intentionally never written here)
  if ($avatarPath) {
    $stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ?, mobile = ?, profile_photo = ?, updated_at = NOW() WHERE admin_id = ?");
    if ($stmt === false) {
      ob_end_clean();
      http_response_code(500);
      echo json_encode(['ok' => false, 'error' => 'Query prepare failed: ' . $conn->error]);
      exit;
    }
    $stmt->bind_param("ssssi", $fullName, $email, $mobile, $avatarPath, $adminId);
  } else {
    $stmt = $conn->prepare("UPDATE admins SET full_name = ?, email = ?, mobile = ?, updated_at = NOW() WHERE admin_id = ?");
    if ($stmt === false) {
      ob_end_clean();
      http_response_code(500);
      echo json_encode(['ok' => false, 'error' => 'Query prepare failed: ' . $conn->error]);
      exit;
    }
    $stmt->bind_param("sssi", $fullName, $email, $mobile, $adminId);
  }

  if (!$stmt->execute()) {
    ob_end_clean();
    echo json_encode(['ok' => false, 'error' => 'Database update failed: ' . $stmt->error]);
    exit;
  }
  $stmt->close();

  // Fetch current status fresh from DB (this endpoint never writes it, but the UI still displays it)
  $currentStatus = 'Active';
  $statusStmt = $conn->prepare("SELECT status FROM admins WHERE admin_id = ? LIMIT 1");
  if ($statusStmt !== false) {
    $statusStmt->bind_param("i", $adminId);
    $statusStmt->execute();
    $statusRow = $statusStmt->get_result()->fetch_assoc();
    if ($statusRow && isset($statusRow['status'])) {
      $currentStatus = $statusRow['status'];
    }
    $statusStmt->close();
  }

  // 6. Return fresh updated row data
  ob_end_clean();
  echo json_encode([
    'ok' => true,
    'message' => 'Profile updated successfully!',
    'admin' => [
      'full_name'     => $fullName,
      'email'         => $email,
      'mobile'        => $mobile,
      'status'        => $currentStatus,
      'profile_photo' => $avatarPath,
      'updated_at'    => date('Y-m-d H:i:s')
    ],
    'completion' => 100
  ]);
  exit;
} catch (\Throwable $e) {
  ob_end_clean();
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server Error: ' . $e->getMessage() . ' (line ' . $e->getLine() . ')']);
  exit;
}