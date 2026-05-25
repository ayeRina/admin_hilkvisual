<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HilkVisual Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 260px;
            background: white;
            color: #2c3e50;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        .sidebar .logo {
            text-align: center;
            padding: 10px 20px 30px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .sidebar .logo svg {
            display: block;
            margin: 0 auto;
        }
        .sidebar .logo h1 {
            font-size: 26px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 1px;
            margin: 0;
        }
        .sidebar .logo span {
            color: #d4af37;
        }
        .sidebar nav ul {
            list-style: none;
        }
        .sidebar nav ul li {
            margin: 5px 15px;
        }
        .sidebar nav ul li a {
            display: block;
            padding: 14px 20px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background-color: #f8f4e8;
            color: #d4af37;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        .header {
            background: white;
            padding: 25px 35px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        .header h2 {
            color: #2c3e50;
            text-transform: capitalize;
            font-size: 24px;
            font-weight: 600;
        }
        .content-card {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
        }
        .content-card h3 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .content-card p {
            color: #666;
            line-height: 1.6;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }
        .stat-card {
            background: white;
            padding: 28px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            border-left: 4px solid #d4af37;
        }
        .stat-card h3 {
            font-size: 40px;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 700;
        }
        .stat-card p {
            font-size: 15px;
            color: #888;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="assets/image/logo.png" alt="HilkVisual Logo" style="width: 120px; height: auto; margin-bottom: 10px;">
            <h1>Hilk<span>Visual</span></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt" style="margin-right: 12px;"></i>DASHBOARD</a></li>
                <li><a href="index.php?page=clients" class="<?php echo $current_page === 'clients' ? 'active' : ''; ?>"><i class="fas fa-users" style="margin-right: 12px;"></i>CLIENTS</a></li>
                <li><a href="index.php?page=add-photoshoots" class="<?php echo $current_page === 'add-photoshoots' ? 'active' : ''; ?>"><i class="fas fa-camera" style="margin-right: 12px;"></i>ADD PHOTOSHOOTS</a></li>
                <li><a href="index.php?page=add-file" class="<?php echo $current_page === 'add-file' ? 'active' : ''; ?>"><i class="fas fa-file-upload" style="margin-right: 12px;"></i>ADD FILE</a></li>
                <li><a href="index.php?page=reports" class="<?php echo $current_page === 'reports' ? 'active' : ''; ?>"><i class="fas fa-chart-bar" style="margin-right: 12px;"></i>REPORTS</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <?php
        switch($current_page) {
            case 'clients':
                include 'pages/clients.php';
                break;
            case 'add-photoshoots':
                include 'pages/add-photoshoots.php';
                break;
            case 'add-file':
                include 'pages/add-file.php';
                break;
            case 'reports':
                include 'pages/reports.php';
                break;
            default:
                include 'pages/dashboard.php';
        }
        ?>
    </div>
</body>
</html>