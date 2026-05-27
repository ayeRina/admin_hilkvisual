<?php
require_once __DIR__ . '/../api/db.php';

$pdo = db();
$stmt = $pdo->query('SELECT * FROM photoshoots ORDER BY created_at DESC');
$photoshoots = $stmt->fetchAll();
?>
<div class="header">
    <h2>View Photoshoots</h2>
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
                        <button onclick="viewPhotoshoot(<?php echo $photoshoot['id']; ?>)" style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 10px 25px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                            <i class="fas fa-eye" style="margin-right: 8px;"></i>View
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="photoshootModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <button onclick="closeModal()" style="position: absolute; top: 20px; right: 20px; background: #f8f9fa; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; color: #666;">
            <i class="fas fa-times"></i>
        </button>
        <div id="modalContent" style="padding: 30px;"></div>
    </div>
</div>

<script>
function viewPhotoshoot(id) {
    const photoshoots = <?php echo json_encode($photoshoots); ?>;
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
        
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('photoshootModal').style.display = 'flex';
    }
}

function closeModal() {
    document.getElementById('photoshootModal').style.display = 'none';
}

document.getElementById('photoshootModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
