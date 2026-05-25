<?php
$bookings = [
    [
        'id' => 1,
        'client_name' => 'John Doe',
        'date' => '2026-06-15',
        'time' => '10:00 AM',
        'services' => ['Wedding Photography', 'Videography'],
        'status' => 'pending'
    ],
    [
        'id' => 2,
        'client_name' => 'Jane Smith',
        'date' => '2026-06-20',
        'time' => '2:00 PM',
        'services' => ['Portrait Session', 'Photo Editing'],
        'status' => 'pending'
    ],
    [
        'id' => 3,
        'client_name' => 'Mike Johnson',
        'date' => '2026-06-10',
        'time' => '11:00 AM',
        'services' => ['Event Coverage'],
        'status' => 'confirmed'
    ],
    [
        'id' => 4,
        'client_name' => 'Sarah Williams',
        'date' => '2026-06-25',
        'time' => '3:00 PM',
        'services' => ['Family Photoshoot', 'Print Packages'],
        'status' => 'pending'
    ]
];

$confirmMessage = '';
if (isset($_GET['confirm_id'])) {
    $confirmId = $_GET['confirm_id'];
    $confirmMessage = "Booking #$confirmId has been confirmed!";
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
                            <?php echo $booking['client_name']; ?>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <i class="fas fa-calendar-alt" style="margin-right: 8px; color: #d4af37;"></i>
                            <?php echo date('F j, Y', strtotime($booking['date'])); ?><br>
                            <small><i class="fas fa-clock" style="margin-right: 5px;"></i><?php echo $booking['time']; ?></small>
                        </td>
                        <td style="padding: 15px; color: #666;">
                            <?php foreach ($booking['services'] as $service): ?>
                                <span style="display: inline-block; background: #f8f4e8; color: #d4af37; padding: 5px 12px; border-radius: 20px; margin: 3px; font-size: 13px; font-weight: 500;">
                                    <i class="fas fa-check" style="margin-right: 5px;"></i><?php echo $service; ?>
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
            </tbody>
        </table>
    </div>
</div>