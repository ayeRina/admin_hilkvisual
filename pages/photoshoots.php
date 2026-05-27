<?php
require_once __DIR__ . '/../api/db.php';

$pdo = db();
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' && isset($_FILES['photoshoot_image'])) {
        try {
            $targetDir = __DIR__ . '/../uploads/photoshoots/';
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['photoshoot_image']['name']);
            $targetFile = $targetDir . $fileName;
            $check = getimagesize($_FILES['photoshoot_image']['tmp_name']);

            if ($check !== false && move_uploaded_file($_FILES['photoshoot_image']['tmp_name'], $targetFile)) {
                $relativePath = 'uploads/photoshoots/' . $fileName;
                $stmt = $pdo->prepare('INSERT INTO photoshoots (client_name, photoshoot_date, location, description, image_path) VALUES (:client_name, :photoshoot_date, :location, :description, :image_path)');
                $stmt->execute([
                    ':client_name' => $_POST['client_name'] ?? '',
                    ':photoshoot_date' => $_POST['photoshoot_date'] ?? date('Y-m-d'),
                    ':location' => $_POST['location'] ?? '',
                    ':description' => $_POST['description'] ?? null,
                    ':image_path' => $relativePath,
                ]);

                $message = 'Photoshoot added successfully!';
                $success = true;
            } else {
                $message = 'Sorry, there was an error uploading your file.';
            }
        } catch (Throwable $exception) {
            $message = 'Database connection failed. Check your API config and schema.';
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        try {
            $id = (int)$_POST['id'];
            $imagePath = $_POST['existing_image'] ?? null;

            if (isset($_FILES['photoshoot_image']) && $_FILES['photoshoot_image']['error'] === UPLOAD_ERR_OK) {
                $targetDir = __DIR__ . '/../uploads/photoshoots/';
                $fileName = time() . '_' . basename($_FILES['photoshoot_image']['name']);
                $targetFile = $targetDir . $fileName;
                $check = getimagesize($_FILES['photoshoot_image']['tmp_name']);

                if ($check !== false && move_uploaded_file($_FILES['photoshoot_image']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/photoshoots/' . $fileName;
                }
            }

            $stmt = $pdo->prepare('UPDATE photoshoots SET client_name = :client_name, photoshoot_date = :photoshoot_date, location = :location, description = :description, image_path = :image_path WHERE id = :id');
            $stmt->execute([
                ':client_name' => $_POST['client_name'] ?? '',
                ':photoshoot_date' => $_POST['photoshoot_date'] ?? date('Y-m-d'),
                ':location' => $_POST['location'] ?? '',
                ':description' => $_POST['description'] ?? null,
                ':image_path' => $imagePath,
                ':id' => $id,
            ]);

            $message = 'Photoshoot updated successfully!';
            $success = true;
        } catch (Throwable $exception) {
            $message = 'Error updating photoshoot: ' . $exception->getMessage();
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        try {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM photoshoots WHERE id = :id');
            $stmt->execute([':id' => $id]);

            $message = 'Photoshoot deleted successfully!';
            $success = true;
        } catch (Throwable $exception) {
            $message = 'Error deleting photoshoot: ' . $exception->getMessage();
        }
    }
}

$stmt = $pdo->query('SELECT * FROM photoshoots ORDER BY created_at DESC');
$photoshoots = $stmt->fetchAll();
?>
<div class="header">
    <h2>Photoshoots</h2>
</div>

<?php if ($message): ?>
    <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; <?php echo $success ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="content-card" style="margin-bottom: 24px;">
    <h3>Add New Photoshoot</h3>
    <form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
        <input type="hidden" name="action" value="add">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Client Name</label>
                <input type="text" name="client_name" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Photoshoot Date</label>
                <input type="date" name="photoshoot_date" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Location</label>
            <input type="text" name="location" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Photoshoot Image</label>
            <input type="file" name="photoshoot_image" accept="image/*" required style="width: 100%; padding: 12px 15px; border: 2px dashed #e0e0e0; border-radius: 8px; font-size: 15px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Description</label>
            <textarea name="description" rows="3" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; resize: vertical;"></textarea>
        </div>
        
        <button type="submit" style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 14px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
            <i class="fas fa-plus" style="margin-right: 8px;"></i>Add Photoshoot
        </button>
    </form>
</div>

<div class="content-card">
    <h3>All Photoshoots</h3>
    
    <?php if (count($photoshoots) === 0): ?>
        <p style="color: #666;">No photoshoots added yet.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($photoshoots as $photoshoot): ?>
                <div style="background: #f8f9fa; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <?php if ($photoshoot['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($photoshoot['image_path']); ?>" alt="Photoshoot" style="width: 100%; height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div style="padding: 20px;">
                        <h4 style="color: #2c3e50; margin-bottom: 10px; font-size: 18px;"><?php echo htmlspecialchars($photoshoot['client_name']); ?></h4>
                        <p style="color: #666; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-calendar" style="margin-right: 8px;"></i>
                            <?php echo htmlspecialchars($photoshoot['photoshoot_date']); ?>
                        </p>
                        <p style="color: #666; margin-bottom: 8px; font-size: 14px;">
                            <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i>
                            <?php echo htmlspecialchars($photoshoot['location']); ?>
                        </p>
                        <?php if ($photoshoot['description']): ?>
                            <p style="color: #888; margin-bottom: 15px; font-size: 13px; line-height: 1.5;">
                                <?php echo htmlspecialchars($photoshoot['description']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 10px;">
                            <button onclick="viewPhotoshoot(<?php echo $photoshoot['id']; ?>)" style="flex: 1; background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-eye" style="margin-right: 6px;"></i>View
                            </button>
                            <button onclick="editPhotoshoot(<?php echo $photoshoot['id']; ?>)" style="flex: 1; background: #17a2b8; color: white; border: none; padding: 10px 15px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-edit" style="margin-right: 6px;"></i>Edit
                            </button>
                            <button onclick="deletePhotoshoot(<?php echo $photoshoot['id']; ?>)" style="flex: 1; background: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-trash" style="margin-right: 6px;"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="viewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <button onclick="closeViewModal()" style="position: absolute; top: 20px; right: 20px; background: #f8f9fa; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; color: #666;">
            <i class="fas fa-times"></i>
        </button>
        <div id="viewModalContent" style="padding: 30px;"></div>
    </div>
</div>

<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <button onclick="closeEditModal()" style="position: absolute; top: 20px; right: 20px; background: #f8f9fa; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; color: #666;">
            <i class="fas fa-times"></i>
        </button>
        <div id="editModalContent" style="padding: 30px;"></div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
const photoshoots = <?php echo json_encode($photoshoots); ?>;

function viewPhotoshoot(id) {
    const photoshoot = photoshoots.find(p => p.id == id);
    if (photoshoot) {
        let html = `
            <h2 style="color: #2c3e50; margin-bottom: 20px; font-size: 24px;">${escapeHtml(photoshoot.client_name)}</h2>
        `;
        
        if (photoshoot.image_path) {
            html += `<img src="${escapeHtml(photoshoot.image_path)}" alt="Photoshoot" style="width: 100%; border-radius: 12px; margin-bottom: 25px;">`;
        }
        
        html += `
            <div style="margin-bottom: 15px;">
                <p style="color: #666; margin-bottom: 10px; font-size: 15px;">
                    <i class="fas fa-calendar" style="margin-right: 10px; color: #d4af37;"></i>
                    <strong>Date:</strong> ${escapeHtml(photoshoot.photoshoot_date)}
                </p>
                <p style="color: #666; margin-bottom: 10px; font-size: 15px;">
                    <i class="fas fa-map-marker-alt" style="margin-right: 10px; color: #d4af37;"></i>
                    <strong>Location:</strong> ${escapeHtml(photoshoot.location)}
                </p>
            </div>
        `;
        
        if (photoshoot.description) {
            html += `
                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;">
                    <h4 style="color: #2c3e50; margin-bottom: 10px; font-size: 16px;">Description</h4>
                    <p style="color: #666; line-height: 1.6;">${escapeHtml(photoshoot.description)}</p>
                </div>
            `;
        }
        
        document.getElementById('viewModalContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
    }
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

function editPhotoshoot(id) {
    const photoshoot = photoshoots.find(p => p.id == id);
    if (photoshoot) {
        const html = `
            <h2 style="color: #2c3e50; margin-bottom: 20px; font-size: 24px;">Edit Photoshoot</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="${photoshoot.id}">
                <input type="hidden" name="existing_image" value="${escapeHtml(photoshoot.image_path)}">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Client Name</label>
                    <input type="text" name="client_name" required value="${escapeHtml(photoshoot.client_name)}" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Photoshoot Date</label>
                    <input type="date" name="photoshoot_date" required value="${escapeHtml(photoshoot.photoshoot_date)}" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Location</label>
                    <input type="text" name="location" required value="${escapeHtml(photoshoot.location)}" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Photoshoot Image (optional)</label>
                    <input type="file" name="photoshoot_image" accept="image/*" style="width: 100%; padding: 12px 15px; border: 2px dashed #e0e0e0; border-radius: 8px; font-size: 15px;">
                    <p style="color: #888; font-size: 12px; margin-top: 8px;">Leave empty to keep current image</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Description</label>
                    <textarea name="description" rows="3" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; resize: vertical;">${escapeHtml(photoshoot.description || '')}</textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeEditModal()" style="flex: 1; background: #6c757d; color: white; border: none; padding: 14px 20px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" style="flex: 1; background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 14px 20px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        Save Changes
                    </button>
                </div>
            </form>
        `;
        
        document.getElementById('editModalContent').innerHTML = html;
        document.getElementById('editModal').style.display = 'flex';
    }
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function deletePhotoshoot(id) {
    if (confirm('Are you sure you want to delete this photoshoot?')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
