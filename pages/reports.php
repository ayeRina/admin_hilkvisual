<?php
require_once __DIR__ . '/../api/db.php';

try {
    $pdo = db();
    $totalRevenue = (float) safe_value($pdo, 'SELECT COALESCE(SUM(total_amount), 0) FROM bookings');
    $bookingsConfirmed = safe_count($pdo, 'SELECT COUNT(*) FROM bookings WHERE status = "confirmed"');
    $photosProcessed = safe_count($pdo, 'SELECT COUNT(*) FROM uploads');
    $averageRating = (float) safe_value($pdo, 'SELECT COALESCE(AVG(rating), 0) FROM reviews');

    $topClients = $pdo->query('
        SELECT client_name, COUNT(*) AS booking_count, COALESCE(SUM(total_amount), 0) AS spent
        FROM bookings
        GROUP BY client_name
        ORDER BY spent DESC, booking_count DESC
        LIMIT 3
    ')->fetchAll();
} catch (Throwable $exception) {
    $totalRevenue = 0;
    $bookingsConfirmed = 0;
    $photosProcessed = 0;
    $averageRating = 0;
    $topClients = [];
}
?>
<div class="header">
    <h2 style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-chart-bar" style="color: #d4af37;"></i>Reports
    </h2>
</div>

<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3>₱<?php echo number_format($totalRevenue, 2); ?></h3><p>Total Revenue</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-peso-sign" style="color: white; font-size: 24px;"></i></div></div></div>
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo $bookingsConfirmed; ?></h3><p>Bookings Confirmed</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-check-circle" style="color: white; font-size: 24px;"></i></div></div></div>
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo $photosProcessed; ?></h3><p>Photos Processed</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-images" style="color: white; font-size: 24px;"></i></div></div></div>
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo number_format($averageRating, 1); ?></h3><p>Avg. Client Rating</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-star" style="color: white; font-size: 24px;"></i></div></div></div>
</div>

<div class="content-card" style="margin-top: 30px;">
    <h3 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-trophy" style="color: #d4af37;"></i>Top Clients
    </h3>
    <div style="display: flex; flex-direction: column; gap: 15px;">
        <?php if (!empty($topClients)): ?>
            <?php foreach ($topClients as $index => $client): ?>
                <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #fafafa; border-radius: 10px;">
                    <div style="width: 50px; height: 50px; background: <?php echo $index === 0 ? 'linear-gradient(135deg, #d4af37 0%, #c9a227 100%)' : ($index === 1 ? '#c0c0c0' : '#cd7f32'); ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px;"><?php echo $index + 1; ?></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #2c3e50;"><?php echo htmlspecialchars($client['client_name']); ?></div>
                        <div style="font-size: 13px; color: #888;"><?php echo (int) $client['booking_count']; ?> bookings • ₱<?php echo number_format((float) $client['spent'], 2); ?> spent</div>
                    </div>
                    <?php if ($index === 0): ?><i class="fas fa-crown" style="color: #d4af37; font-size: 22px;"></i><?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #888; font-style: italic;">No client booking data yet.</p>
        <?php endif; ?>
    </div>
</div>