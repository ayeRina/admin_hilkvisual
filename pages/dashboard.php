<?php
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/helpers.php';

try {
    $pdo = db();
    $stats = [
        'total_clients' => safe_count($pdo, 'SELECT COUNT(*) FROM users WHERE role = "user"'),
        'total_bookings' => safe_count($pdo, 'SELECT COUNT(*) FROM bookings'),
        'total_uploads' => safe_count($pdo, 'SELECT COUNT(*) FROM uploads'),
        'total_reports' => safe_count($pdo, 'SELECT COUNT(*) FROM reports'),
    ];

    $recentBookings = $pdo->query('
        SELECT
            b.id,
            b.client_name,
            b.booking_date,
            b.booking_time,
            b.status,
            GROUP_CONCAT(bs.service_name ORDER BY bs.service_name SEPARATOR ", ") AS services
        FROM bookings b
        LEFT JOIN booking_services bs ON bs.booking_id = b.id
        GROUP BY b.id, b.client_name, b.booking_date, b.booking_time, b.status, b.created_at
        ORDER BY b.created_at DESC
        LIMIT 3
    ')->fetchAll();
} catch (Throwable $exception) {
    $stats = [
        'total_clients' => 0,
        'total_bookings' => 0,
        'total_uploads' => 0,
        'total_reports' => 0,
    ];
    $recentBookings = [];
}
?>
<div class="header">
    <h2 style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-tachometer-alt" style="color: #d4af37;"></i>Dashboard
    </h2>
</div>

<div class="stats-grid" style="margin-bottom: 35px;">
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo $stats['total_clients']; ?></h3><p>Total Clients</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-users" style="color: white; font-size: 24px;"></i></div></div></div>
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo $stats['total_bookings']; ?></h3><p>Bookings</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-camera" style="color: white; font-size: 24px;"></i></div></div></div>
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo $stats['total_uploads']; ?></h3><p>Files</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-images" style="color: white; font-size: 24px;"></i></div></div></div>
    <div class="stat-card"><div style="display: flex; justify-content: space-between; align-items: start;"><div><h3><?php echo $stats['total_reports']; ?></h3><p>Reports</p></div><div style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-chart-bar" style="color: white; font-size: 24px;"></i></div></div></div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <div class="content-card">
        <h3 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-clock" style="color: #d4af37;"></i>Recent Bookings
        </h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php if (!empty($recentBookings)): ?>
                <?php foreach ($recentBookings as $booking): ?>
                    <div style="display: flex; align-items: center; gap: 15px; padding: 12px; background: #fafafa; border-radius: 8px;">
                        <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas fa-user" style="color: white; font-size: 18px;"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #2c3e50;"><?php echo htmlspecialchars($booking['client_name']); ?></div>
                            <div style="font-size: 13px; color: #888;"><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?> - <?php echo htmlspecialchars($booking['booking_time']); ?></div>
                        </div>
                        <span style="background: <?php echo $booking['status'] === 'confirmed' ? '#d4edda' : '#fff3cd'; ?>; color: <?php echo $booking['status'] === 'confirmed' ? '#155724' : '#856404'; ?>; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><?php echo ucfirst($booking['status']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #888; font-style: italic;">No bookings yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="content-card">
        <h3 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-bullhorn" style="color: #d4af37;"></i>Quick Actions
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <a href="index.php?page=add-photoshoots" style="text-decoration: none; background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; font-weight: 600; transition: transform 0.2s ease;"><i class="fas fa-plus" style="display: block; font-size: 28px; margin-bottom: 10px;"></i>Add Photoshoot</a>
            <a href="index.php?page=clients" style="text-decoration: none; background: #2c3e50; color: white; padding: 20px; border-radius: 12px; text-align: center; font-weight: 600; transition: transform 0.2s ease;"><i class="fas fa-check" style="display: block; font-size: 28px; margin-bottom: 10px;"></i>Confirm Bookings</a>
            <a href="index.php?page=add-file" style="text-decoration: none; background: #2c3e50; color: white; padding: 20px; border-radius: 12px; text-align: center; font-weight: 600; transition: transform 0.2s ease;"><i class="fas fa-upload" style="display: block; font-size: 28px; margin-bottom: 10px;"></i>Upload Files</a>
            <a href="index.php?page=reports" style="text-decoration: none; background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; font-weight: 600; transition: transform 0.2s ease;"><i class="fas fa-chart-line" style="display: block; font-size: 28px; margin-bottom: 10px;"></i>View Reports</a>
        </div>
    </div>
</div>