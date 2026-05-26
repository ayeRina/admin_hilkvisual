<?php
ob_start(); // Buffer output so header() can be called safely

// Load the page content
require_once __DIR__ . '/../api/db.php';

$selectedClient = isset($_GET['client']) ? (string) $_GET['client'] : '';
$uploadMessage = '';
$uploadSuccess = false;

$clients = [];
$clientFiles = [];

function public_asset_url(string $relativePath): string
{
    $normalized = str_replace('\\\\', '/', trim($relativePath));
    $normalized = ltrim($normalized, '/');
    return '/admin_hilkvisual/' . $normalized;
}

function is_image_file(string $mimeType, string $fileName): bool
{
    if (stripos($mimeType, 'image/') === 0) {
        return true;
    }

    $ext = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
}

try {
    $pdo = db();

    $users = $pdo->query('SELECT id, full_name FROM users WHERE role = "user" ORDER BY full_name ASC')->fetchAll();
    foreach ($users as $user) {
        $clients['user_' . $user['id']] = $user['full_name'];
    }

    // Handle file upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
        $clientId = (int) $_POST['client_id'];
        $clientName = $_POST['client_name'] ?? '';
        $uploadedCount = 0;
        $failedCount = 0;

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

        if ($uploadedCount > 0) {
            $uploadSuccess = true;
            $uploadMessage = 'Uploaded ' . $uploadedCount . ' file(s).';
            if ($failedCount > 0) {
                $uploadMessage .= ' ' . $failedCount . ' file(s) failed.';
            }
        } elseif ($failedCount > 0) {
            $uploadMessage = 'Upload failed. ' . $failedCount . ' file(s) could not be processed.';
        }

        $selectedClient = 'user_' . $clientId;
    }

    if ($selectedClient && isset($clients[$selectedClient])) {
        $clientId = (int) str_replace('user_', '', $selectedClient);
        $stmt = $pdo->prepare('SELECT id, file_name, file_path, file_type, created_at FROM uploads WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $clientId]);
        $rows = $stmt->fetchAll();

        $clientFiles = array_map(function (array $row) {
            $filePath = (string) ($row['file_path'] ?? '');
            $fileName = (string) ($row['file_name'] ?? basename($filePath));
            $fileType = (string) ($row['file_type'] ?? '');

            return [
                'id' => (int) ($row['id'] ?? 0),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_url' => public_asset_url($filePath),
                'file_type' => $fileType,
                'is_image' => is_image_file($fileType, $fileName),
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }, $rows);
    }
} catch (Throwable $exception) {
    $clients = [];
    $uploadMessage = 'Error: ' . $exception->getMessage();
    $uploadSuccess = false;
}
?>
<div class="header">
    <h2>Add File</h2>
</div>
<div class="content-card">
    <h3>Upload & Manage Client Files</h3>

    <div style="margin-bottom: 30px;">
        <label style="display: block; margin-bottom: 10px; color: #2c3e50; font-weight: 500;">Select Client</label>
        <form method="GET" style="display: flex; gap: 10px; max-width: 400px;">
            <input type="hidden" name="page" value="add-file">
            <select name="client" required style="flex: 1; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; background: white;">
                <option value="">-- Choose a Client --</option>
                <?php foreach ($clients as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>" <?php echo $selectedClient === $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="background: #2c3e50; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 500; cursor: pointer;">View</button>
        </form>
    </div>

    <?php if ($uploadMessage): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; <?php echo $uploadSuccess ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
            <?php echo htmlspecialchars($uploadMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($selectedClient && isset($clients[$selectedClient])): ?>
        <div style="background: #f8f4e8; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <strong>Selected Client:</strong> <?php echo htmlspecialchars($clients[$selectedClient]); ?>
        </div>

        <form method="POST" enctype="multipart/form-data" style="margin-bottom: 40px; max-width: 700px;">
            <input type="hidden" name="client_id" value="<?php echo (int) str_replace('user_', '', $selectedClient); ?>">
            <input type="hidden" name="client_name" value="<?php echo htmlspecialchars($clients[$selectedClient]); ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Upload Images / Files (Multi-select or Folder)</label>
                <input
                    type="file"
                    name="client_files[]"
                    multiple
                    webkitdirectory
                    directory
                    style="width: 100%; padding: 12px 15px; border: 2px dashed #e0e0e0; border-radius: 8px; font-size: 15px;"
                >
                <small style="display:block; margin-top:8px; color:#666;">Tip: You can choose many files at once or pick a folder in supported browsers.</small>
            </div>

            <button type="submit" style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 14px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                Upload to <?php echo htmlspecialchars($clients[$selectedClient]); ?>
            </button>
        </form>

        <?php if (!empty($clientFiles)): ?>
            <h4 style="margin-bottom: 20px; color: #2c3e50; font-weight: 600;">Client Files (<?php echo count($clientFiles); ?>)</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                <?php foreach ($clientFiles as $file): ?>
                    <div style="border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); background: #fff; border: 1px solid #f0f0f0;">
                        <?php if ($file['is_image']): ?>
                            <img src="<?php echo htmlspecialchars($file['file_url']); ?>" alt="<?php echo htmlspecialchars($file['file_name']); ?>" style="width: 100%; height: 150px; object-fit: cover; display:block;" loading="lazy">
                        <?php else: ?>
                            <div style="height:150px; display:flex; align-items:center; justify-content:center; background:#f7f7f7; color:#666; font-weight:600;">FILE</div>
                        <?php endif; ?>
                        <div style="padding:10px 12px;">
                            <div style="font-size:13px; color:#2c3e50; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($file['file_name']); ?>"><?php echo htmlspecialchars($file['file_name']); ?></div>
                            <div style="font-size:12px; color:#888; margin-top:4px;"><?php echo htmlspecialchars($file['created_at']); ?></div>
                            <a href="<?php echo htmlspecialchars($file['file_url']); ?>" target="_blank" rel="noopener" style="display:inline-block; margin-top:8px; font-size:12px; color:#2c3e50; text-decoration:none;">Open</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #888; font-style: italic;">No files uploaded yet for this client.</p>
        <?php endif; ?>
    <?php elseif (empty($clients)): ?>
        <p style="color: #888; font-style: italic;">No user accounts found yet. Add users first from the mobile app or direct database inserts.</p>
    <?php endif; ?>
</div>