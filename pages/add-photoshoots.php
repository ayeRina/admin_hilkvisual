<?php
require_once __DIR__ . '/../api/db.php';

$uploadMessage = '';
$uploadSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photoshoot_image'])) {
    try {
        $pdo = db();
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

            $uploadMessage = 'Photoshoot image uploaded successfully!';
            $uploadSuccess = true;
        } else {
            $uploadMessage = 'Sorry, there was an error uploading your file.';
        }
    } catch (Throwable $exception) {
        $uploadMessage = 'Database connection failed. Check your API config and schema.';
    }
}
?>
<div class="header">
    <h2>Add Photoshoots</h2>
</div>
<div class="content-card">
    <h3>New Photoshoot</h3>
    
    <?php if ($uploadMessage): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; <?php echo $uploadSuccess ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
            <?php echo $uploadMessage; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Client Name</label>
            <input type="text" name="client_name" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s ease;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Photoshoot Date</label>
            <input type="date" name="photoshoot_date" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s ease;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Location</label>
            <input type="text" name="location" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s ease;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Photoshoot Image</label>
            <input type="file" name="photoshoot_image" accept="image/*" required style="width: 100%; padding: 12px 15px; border: 2px dashed #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s ease;">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Description</label>
            <textarea name="description" rows="4" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: border-color 0.3s ease; resize: vertical;"></textarea>
        </div>
        
        <button type="submit" style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 14px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
            Save Photoshoot
        </button>
    </form>
</div>
