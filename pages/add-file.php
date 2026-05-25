<?php
$clients = [
    'john_doe' => 'John Doe',
    'jane_smith' => 'Jane Smith',
    'mike_johnson' => 'Mike Johnson',
    'sarah_williams' => 'Sarah Williams',
    'david_brown' => 'David Brown'
];

$selectedClient = isset($_GET['client']) ? $_GET['client'] : '';
$uploadMessage = '';
$uploadSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['client_file']) && isset($_POST['client_id'])) {
    $clientId = $_POST['client_id'];
    $targetDir = 'uploads/clients/' . $clientId . '/';
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['client_file']['name']);
    $targetFile = $targetDir . $fileName;
    $check = getimagesize($_FILES['client_file']['tmp_name']);
    
    if ($check !== false) {
        if (move_uploaded_file($_FILES['client_file']['tmp_name'], $targetFile)) {
            $uploadMessage = 'File uploaded successfully to ' . $clients[$clientId] . '\'s account!';
            $uploadSuccess = true;
            $selectedClient = $clientId;
        } else {
            $uploadMessage = 'Sorry, there was an error uploading your file.';
        }
    } else {
        $uploadMessage = 'File is not an image.';
    }
}

$clientImages = [];
if ($selectedClient && isset($clients[$selectedClient])) {
    $clientDir = 'uploads/clients/' . $selectedClient . '/';
    if (file_exists($clientDir)) {
        $clientImages = glob($clientDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    }
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
                    <option value="<?php echo $id; ?>" <?php echo $selectedClient === $id ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="background: #2c3e50; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 500; cursor: pointer;">View</button>
        </form>
    </div>
    
    <?php if ($uploadMessage): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; <?php echo $uploadSuccess ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
            <?php echo $uploadMessage; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($selectedClient && isset($clients[$selectedClient])): ?>
        <div style="background: #f8f4e8; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <strong>Selected Client:</strong> <?php echo $clients[$selectedClient]; ?>
        </div>
        
        <form method="POST" enctype="multipart/form-data" style="margin-bottom: 40px; max-width: 600px;">
            <input type="hidden" name="client_id" value="<?php echo $selectedClient; ?>">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 500;">Upload Image</label>
                <input type="file" name="client_file" accept="image/*" required style="width: 100%; padding: 12px 15px; border: 2px dashed #e0e0e0; border-radius: 8px; font-size: 15px;">
            </div>
            <button type="submit" style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 14px 40px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                Upload to <?php echo $clients[$selectedClient]; ?>
            </button>
        </form>
        
        <?php if (!empty($clientImages)): ?>
            <h4 style="margin-bottom: 20px; color: #2c3e50; font-weight: 600;">Client's Images (<?php echo count($clientImages); ?>)</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
                <?php foreach ($clientImages as $image): ?>
                    <div style="border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        <img src="<?php echo $image; ?>" alt="Client Image" style="width: 100%; height: 150px; object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #888; font-style: italic;">No images uploaded yet for this client.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>