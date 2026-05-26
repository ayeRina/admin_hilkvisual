<?php
require_once __DIR__ . '/../api/db.php';

$confirmMessage = '';

try {
    $pdo = db();

    if (isset($_GET['confirm_id'])) {
        $confirmId = (int) $_GET['confirm_id'];
        $stmt = $pdo->prepare('UPDATE bookings SET status = "confirmed" WHERE id = :id');
        $stmt->execute([':id' => $confirmId]);
        $confirmMessage = "Booking #$confirmId has been confirmed!";
    }

    $bookings = $pdo->query('
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
    ')->fetchAll();
} catch (Throwable $exception) {
    $bookings = [];
}
?>
<div class="header">
    <h2>Clients</h2>
</div>
<div class="content-card">
    <h3>Bookings Management</h3>
    
    <?php if ($confirmMessage): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; background-color: #d4edda; color: #155724;">
            <i class="fas fa-check-circle" style="margin-right: 10px;"></i><?php echo $confirmMessage; ?>
        </div>
    <?php endif; ?>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f4e8;">
                    <th style="padding: 15px; text-align: left; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37;">Client Name</th>
                    <th style="padding: 15px; text-align: left; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37;">Date & Time</th>
                    <th style="padding: 15px; text-align: left; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37;">Services</th>
                    <th style="padding: 15px; text-align: left; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37;">Status</th>
                    <th style="padding: 15px; text-align: left; color: #2c3e50; font-weight: 600; border-bottom: 2px solid #d4af37;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px; color: #2c3e50; font-weight: 500;">
                            <i class="fas fa-user" style="margin-right: 10px; color: #d4af37;"></i>
                            <?php echo htmlspecialchars($booking['client_name']); ?>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <i class="fas fa-calendar-alt" style="margin-right: 8px; color: #d4af37;"></i>
                            <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?><br>
                            <small><i class="fas fa-clock" style="margin-right: 5px;"></i><?php echo htmlspecialchars($booking['booking_time']); ?></small>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <?php $services = !empty($booking['services']) ? explode(', ', $booking['services']) : []; ?>
                            <?php foreach ($services as $service): ?>
                                <span style="display: inline-block; background: #f8f4e8; color: #d4af37; padding: 5px 12px; border-radius: 20px; margin: 3px; font-size: 13px; font-weight: 500;">
                                    <i class="fas fa-check" style="margin-right: 5px;"></i><?php echo htmlspecialchars($service); ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                        <td style="padding: 15px;">
                            <?php if ($booking['status'] === 'confirmed'): ?>
                                <span style="background: #d4edda; color: #155724; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-check-circle" style="margin-right: 5px;"></i>Confirmed
                                </span>
                            <?php else: ?>
                                <span style="background: #fff3cd; color: #856404; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-clock" style="margin-right: 5px;"></i>Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px;">
                            <?php if ($booking['status'] === 'pending'): ?>
                                <a href="index.php?page=clients&confirm_id=<?php echo $booking['id']; ?>" style="background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block;">
                                    <i class="fas fa-check" style="margin-right: 8px;"></i>Confirm
                                </a>
                            <?php else: ?>
                                <span style="color: #888; font-style: italic; font-size: 14px;">
                                    <i class="fas fa-check-double" style="margin-right: 5px;"></i>Done
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="5" style="padding: 20px; color: #888; font-style: italic;">No bookings found yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>