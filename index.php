<?php
session_start();

$admin_email = 'admin@gmail.com';
$admin_password = 'admin123';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if ($email === $admin_email && $password === $admin_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['email'] = $email;
        header('Location: index.php');
        exit;
    } else {
        $login_error = 'Invalid email or password';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HilkVisual Admin - Login</title>
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
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f4e8 0%, #fff 100%);
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            width: 100px;
            height: auto;
        }
        .login-logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 10px;
        }
        .login-logo span {
            color: #d4af37;
        }
        .login-form h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 22px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #d4af37;
        }
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #d4af37 0%, #c9a227 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="assets/image/logo.png" alt="HilkVisual Logo">
            <h1>Hilk<span>Visual</span></h1>
        </div>
        <form method="POST" class="login-form">
            <h2>Admin Login</h2>
            <?php if ($login_error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i><?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="admin@gmail.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="admin123">
            </div>
            <button type="submit" name="login" class="login-btn">
                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>Login
            </button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}
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
            width: 240px;
            background: white;
            color: #2c3e50;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .sidebar .logo {
            text-align: center;
            padding: 10px 15px 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }
        .sidebar .logo svg {
            display: block;
            margin: 0 auto;
        }
        .sidebar .logo img {
            width: 100px !important;
            height: auto;
        }
        .sidebar .logo h1 {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 1px;
            margin: 10px 0 0 0;
        }
        .sidebar .logo span {
            color: #d4af37;
        }
        .sidebar nav ul {
            list-style: none;
        }
        .sidebar nav ul li {
            margin: 4px 12px;
        }
        .sidebar nav ul li a {
            display: block;
            padding: 12px 18px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background-color: #f8f4e8;
            color: #d4af37;
        }
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 18px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            margin-bottom: 20px;
        }
        .header h2 {
            color: #2c3e50;
            text-transform: capitalize;
            font-size: 22px;
            font-weight: 600;
        }
        .content-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
        }
        .content-card h3 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .content-card p {
            color: #666;
            line-height: 1.6;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-top: 0;
        }
        .stat-card {
            background: white;
            padding: 22px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            border-left: 4px solid #d4af37;
        }
        .stat-card h3 {
            font-size: 34px;
            margin-bottom: 6px;
            color: #2c3e50;
            font-weight: 700;
        }
        .stat-card p {
            font-size: 14px;
            color: #888;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #d4af37 !important;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }
        button:active {
            transform: translateY(0);
        }
        .content-card a:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .sidebar a[href*="logout"]:hover {
            background: #fdecea;
            transform: translateY(-1px);
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
        <div style="margin-top: auto; padding: 20px;">
            <a href="index.php?logout=1" style="display: block; padding: 12px 20px; background: #fafafa; color: #e74c3c; text-decoration: none; border-radius: 8px; text-align: center; font-weight: 500; transition: all 0.3s ease;">
                <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i>Logout
            </a>
        </div>
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