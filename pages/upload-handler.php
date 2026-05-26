<?php
// Upload handler - handles POST requests and redirects (no HTML output)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['client_id'])) {
    header('Location: add-file.php');
    exit;
}

require_once __DIR__ . '/../api/db.php';

$clientId = (int) $_POST['client_id'];
$clientName = $_POST['client_name'] ?? '';
$uploadedCount = 0;
$failedCount = 0;

try {
    $pdo = db();
    $targetDir = __DIR__ . '/../uploads/clients/' . $clientId . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    if (isset($_FILES['client_files'])) {
        $names = $_FILES['client_files']['name'] ?? [];
        $tmpNames = $_FILES['client_files']['tmp_name'] ?? [];
        $types = $_FILES['client_files']['type'] ?? [];
        $errors = $_FILES['client_files']['error'] ?? [];

        foreach ($names as $index => $originalNameRaw) {
            $tmpName = $tmpNames[$index] ?? '';
            $error = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);
            if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmpName)) {
                $failedCount++;
                continue;
            }

            $originalName = basename((string) $originalNameRaw);
            
            // Skip system files
            if (strtolower($originalName) === 'desktop.ini' || strtolower($originalName) === 'thumbs.db') {
                $failedCount++;
                continue;
            }

            $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            $safeExt = $ext !== '' ? '.' . preg_replace('/[^a-z0-9]/i', '', $ext) : '';

            try {
                $random = bin2hex(random_bytes(6));
            } catch (Throwable $e) {
                $random = substr(md5(uniqid('', true)), 0, 12);
            }

            $storedName = time() . '_' . $random . $safeExt;
            $targetFile = $targetDir . $storedName;

            if (!move_uploaded_file($tmpName, $targetFile)) {
                $failedCount++;
                continue;
            }

            $relativePath = 'uploads/clients/' . $clientId . '/' . $storedName;
            $stmt = $pdo->prepare('INSERT INTO uploads (user_id, file_name, file_path, file_type) VALUES (:user_id, :file_name, :file_path, :file_type)');
            $stmt->execute([
                ':user_id' => $clientId,
                ':file_name' => $originalName,
                ':file_path' => $relativePath,
                ':file_type' => $types[$index] ?? 'application/octet-stream',
            ]);

            $uploadedCount++;
        }
    }

    // Redirect to add-file.php with success/error parameters
    if ($uploadedCount > 0) {
        header('Location: add-file.php?client=user_' . $clientId . '&success=1&uploaded=' . $uploadedCount . ($failedCount > 0 ? '&failed=' . $failedCount : ''));
    } else {
        header('Location: add-file.php?client=user_' . $clientId . '&error=1');
    }
    exit;
} catch (Throwable $e) {
    header('Location: add-file.php?client=user_' . $clientId . '&error=1');
    exit;
}
