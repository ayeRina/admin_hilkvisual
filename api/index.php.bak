<?php

require_once __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? 'health';
$method = $_SERVER['REQUEST_METHOD'];
$pdo = db();

try {
switch ($action) {
    case 'health':
        json_response([
            'success' => true,
            'message' => 'HilkVisual API is running',
        ]);

    case 'dashboard':
        $stats = [
            'total_clients' => safe_count($pdo, 'SELECT COUNT(*) FROM users WHERE role = "user"'),
            'total_bookings' => safe_count($pdo, 'SELECT COUNT(*) FROM bookings'),
            'total_uploads' => safe_count($pdo, 'SELECT COUNT(*) FROM uploads'),
            'total_reports' => safe_count($pdo, 'SELECT COUNT(*) FROM reports'),
        ];

        json_response([
            'success' => true,
            'data' => $stats,
        ]);

    case 'bookings':
        if ($method === 'GET') {
            $stmt = $pdo->query('
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
            ');
            json_response([
                'success' => true,
                'data' => $stmt->fetchAll(),
            ]);
        }

        if ($method === 'POST') {
            $input = json_input();

            $bookingDate = trim((string) ($input['booking_date'] ?? ''));
            $bookingTime = trim((string) ($input['booking_time'] ?? ''));

            if ($bookingDate === '' || $bookingTime === '') {
                json_response([
                    'success' => false,
                    'message' => 'Booking date and time are required',
                ], 422);
            }

            $conflictStmt = $pdo->prepare('
                SELECT id
                FROM bookings
                WHERE booking_date = :booking_date
                  AND booking_time = :booking_time
                  AND LOWER(COALESCE(status, "")) NOT IN ("cancelled", "canceled", "rejected")
                LIMIT 1
            ');
            $conflictStmt->execute([
                ':booking_date' => $bookingDate,
                ':booking_time' => $bookingTime,
            ]);

            if ($conflictStmt->fetch()) {
                json_response([
                    'success' => false,
                    'message' => 'This date and time slot is already booked. Please choose another slot.',
                ], 409);
            }

            $stmt = $pdo->prepare('
                INSERT INTO bookings (user_id, client_name, booking_date, booking_time, location, notes, status)
                VALUES (:user_id, :client_name, :booking_date, :booking_time, :location, :notes, :status)
            ');
            $stmt->execute([
                ':user_id' => $input['user_id'] ?? null,
                ':client_name' => $input['client_name'] ?? '',
                ':booking_date' => $bookingDate,
                ':booking_time' => $bookingTime,
                ':location' => $input['location'] ?? null,
                ':notes' => $input['notes'] ?? null,
                ':status' => $input['status'] ?? 'pending',
            ]);

            $bookingId = (int) $pdo->lastInsertId();
            $services = $input['services'] ?? [];

            if (is_array($services) && $services !== []) {
                $serviceStmt = $pdo->prepare('
                    INSERT INTO booking_services (booking_id, service_name)
                    VALUES (:booking_id, :service_name)
                ');
                foreach ($services as $serviceName) {
                    $serviceStmt->execute([
                        ':booking_id' => $bookingId,
                        ':service_name' => $serviceName,
                    ]);
                }
            }

            json_response([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => ['id' => $bookingId],
            ], 201);
        }

        if ($method === 'PATCH') {
            $input = json_input();
            $stmt = $pdo->prepare('UPDATE bookings SET status = :status WHERE id = :id');
            $stmt->execute([
                ':status' => $input['status'] ?? 'pending',
                ':id' => $input['id'] ?? 0,
            ]);

            json_response([
                'success' => true,
                'message' => 'Booking updated successfully',
            ]);
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);

    case 'users':
        if ($method === 'GET') {
            $stmt = $pdo->query('SELECT id, full_name, email, phone, profile_photo_path, role, created_at FROM users ORDER BY created_at DESC');
            json_response([
                'success' => true,
                'data' => $stmt->fetchAll(),
            ]);
        }

        if ($method === 'POST') {
            $input = json_input();
            $fullName = trim((string) ($input['full_name'] ?? ''));
            $email = trim((string) ($input['email'] ?? ''));
            $phone = trim((string) ($input['phone'] ?? ''));
            $password = (string) ($input['password'] ?? '');

            if ($fullName === '' || $email === '' || $password === '') {
                json_response([
                    'success' => false,
                    'message' => 'Full name, email, and password are required',
                ], 422);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                json_response([
                    'success' => false,
                    'message' => 'Invalid email format',
                ], 422);
            }

            $existsStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $existsStmt->execute([':email' => $email]);
            if ($existsStmt->fetch()) {
                json_response([
                    'success' => false,
                    'message' => 'Email is already registered',
                ], 409);
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(' 
                INSERT INTO users (full_name, email, phone, profile_photo_path, password_hash, role)
                VALUES (:full_name, :email, :phone, :profile_photo_path, :password_hash, :role)
            ');
            $stmt->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':phone' => $phone !== '' ? $phone : null,
                ':profile_photo_path' => null,
                ':password_hash' => $passwordHash,
                ':role' => 'user',
            ]);

            json_response([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => (int) $pdo->lastInsertId(),
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone !== '' ? $phone : null,
                    'profile_photo_path' => null,
                ],
            ], 201);
        }

        if ($method === 'PATCH') {
            $input = json_input();
            $userId = (int) ($input['id'] ?? 0);
            $fullName = trim((string) ($input['full_name'] ?? ''));
            $email = trim((string) ($input['email'] ?? ''));
            $phone = trim((string) ($input['phone'] ?? ''));
            $profilePhotoPath = trim((string) ($input['profile_photo_path'] ?? ''));
            $newPassword = (string) ($input['password'] ?? '');

            if ($userId <= 0) {
                json_response([
                    'success' => false,
                    'message' => 'User id is required',
                ], 422);
            }

            if ($fullName === '' || $email === '') {
                json_response([
                    'success' => false,
                    'message' => 'Full name and email are required',
                ], 422);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                json_response([
                    'success' => false,
                    'message' => 'Invalid email format',
                ], 422);
            }

            $existsStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
            $existsStmt->execute([
                ':email' => $email,
                ':id' => $userId,
            ]);
            if ($existsStmt->fetch()) {
                json_response([
                    'success' => false,
                    'message' => 'Email is already registered to another account',
                ], 409);
            }

            $fields = [
                'full_name = :full_name',
                'email = :email',
                'phone = :phone',
            ];
            $params = [
                ':id' => $userId,
                ':full_name' => $fullName,
                ':email' => $email,
                ':phone' => $phone !== '' ? $phone : null,
            ];

            if ($profilePhotoPath !== '') {
                $fields[] = 'profile_photo_path = :profile_photo_path';
                $params[':profile_photo_path'] = $profilePhotoPath;
            }

            if ($newPassword !== '') {
                if (strlen($newPassword) < 8) {
                    json_response([
                        'success' => false,
                        'message' => 'Password must be at least 8 characters long',
                    ], 422);
                }

                $fields[] = 'password_hash = :password_hash';
                $params[':password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
            }

            $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $userStmt = $pdo->prepare('SELECT id, full_name, email, phone, profile_photo_path, role, created_at FROM users WHERE id = :id LIMIT 1');
            $userStmt->execute([':id' => $userId]);
            $user = $userStmt->fetch();

            json_response([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user,
            ]);
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);

    case 'login':
        if ($method === 'POST') {
            $input = json_input();
            $email = trim((string) ($input['email'] ?? ''));
            $password = (string) ($input['password'] ?? '');

            if ($email === '' || $password === '') {
                json_response([
                    'success' => false,
                    'message' => 'Email and password are required',
                ], 422);
            }

            $stmt = $pdo->prepare('SELECT id, full_name, email, phone, profile_photo_path, role, password_hash, created_at FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, (string) $user['password_hash'])) {
                json_response([
                    'success' => false,
                    'message' => 'Invalid email or password',
                ], 401);
            }

            json_response([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'id' => (int) $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'profile_photo_path' => $user['profile_photo_path'],
                    'role' => $user['role'],
                    'created_at' => $user['created_at'],
                ],
            ]);
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);

    case 'uploads':
        if ($method === 'GET') {
            $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

            if ($userId > 0) {
                $stmt = $pdo->prepare('
                    SELECT u.id, u.user_id, u.booking_id, u.file_name, u.file_path, u.file_type, u.created_at, users.full_name AS client_name
                    FROM uploads u
                    LEFT JOIN users ON users.id = u.user_id
                    WHERE u.user_id = :user_id
                    ORDER BY u.created_at DESC
                ');
                $stmt->execute([':user_id' => $userId]);
            } else {
                $stmt = $pdo->query('
                    SELECT u.id, u.user_id, u.booking_id, u.file_name, u.file_path, u.file_type, u.created_at, users.full_name AS client_name
                    FROM uploads u
                    LEFT JOIN users ON users.id = u.user_id
                    ORDER BY u.created_at DESC
                ');
            }

            json_response([
                'success' => true,
                'data' => $stmt->fetchAll(),
            ]);
        }

        if ($method === 'POST') {
            // Support multipart/form-data uploads from mobile clients (FormData)
            if (!empty($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                $file = $_FILES['file'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    json_response(['success' => false, 'message' => 'File upload error'], 400);
                }

                // Accept optional form fields: user_id, booking_id
                $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : null;
                $bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : null;

                // Build destination directory: uploads/clients/<userId|guest>/
                $baseUploads = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
                if ($userId) {
                    $targetDir = $baseUploads . DIRECTORY_SEPARATOR . 'clients' . DIRECTORY_SEPARATOR . $userId;
                } else {
                    $targetDir = $baseUploads . DIRECTORY_SEPARATOR . 'mobile';
                }

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $origName = basename($file['name']);
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                try {
                    $random = bin2hex(random_bytes(6));
                } catch (Exception $e) {
                    $random = substr(md5(uniqid('', true)), 0, 12);
                }
                $fileName = time() . '_' . $random . ($ext ? '.' . $ext : '');
                $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    json_response(['success' => false, 'message' => 'Failed to move uploaded file'], 500);
                }

                // Store relative path for DB (web-accessible relative to project root)
                $relativePath = 'uploads/' . (isset($userId) ? 'clients/' . $userId . '/' . $fileName : 'mobile/' . $fileName);

                $stmt = $pdo->prepare('
                    INSERT INTO uploads (user_id, booking_id, file_name, file_path, file_type)
                    VALUES (:user_id, :booking_id, :file_name, :file_path, :file_type)
                ');
                $stmt->execute([
                    ':user_id' => $userId ?: null,
                    ':booking_id' => $bookingId ?: null,
                    ':file_name' => $origName,
                    ':file_path' => $relativePath,
                    ':file_type' => $file['type'] ?? null,
                ]);

                json_response([
                    'success' => true,
                    'message' => 'File uploaded and saved',
                    'data' => [
                        'id' => (int) $pdo->lastInsertId(),
                        'file_name' => $origName,
                        'file_path' => $relativePath,
                    ],
                ], 201);
            }

            // Fallback: accept JSON metadata (existing behavior)
            $input = json_input();
            $stmt = $pdo->prepare('
                INSERT INTO uploads (user_id, booking_id, file_name, file_path, file_type)
                VALUES (:user_id, :booking_id, :file_name, :file_path, :file_type)
            ');
            $stmt->execute([
                ':user_id' => $input['user_id'] ?? null,
                ':booking_id' => $input['booking_id'] ?? null,
                ':file_name' => $input['file_name'] ?? '',
                ':file_path' => $input['file_path'] ?? '',
                ':file_type' => $input['file_type'] ?? '',
            ]);

            json_response([
                'success' => true,
                'message' => 'Upload saved successfully',
                'data' => ['id' => (int) $pdo->lastInsertId()],
            ], 201);
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);

    case 'photoshoots':
        if ($method === 'GET') {
            $stmt = $pdo->query('SELECT * FROM photoshoots ORDER BY created_at DESC');
            json_response([
                'success' => true,
                'data' => $stmt->fetchAll(),
            ]);
        }

        if ($method === 'POST') {
            $input = json_input();
            $stmt = $pdo->prepare('
                INSERT INTO photoshoots (client_name, photoshoot_date, location, description, image_path)
                VALUES (:client_name, :photoshoot_date, :location, :description, :image_path)
            ');
            $stmt->execute([
                ':client_name' => $input['client_name'] ?? '',
                ':photoshoot_date' => $input['photoshoot_date'] ?? null,
                ':location' => $input['location'] ?? null,
                ':description' => $input['description'] ?? null,
                ':image_path' => $input['image_path'] ?? null,
            ]);

            json_response([
                'success' => true,
                'message' => 'Photoshoot created successfully',
                'data' => ['id' => (int) $pdo->lastInsertId()],
            ], 201);
        }

        json_response(['success' => false, 'message' => 'Method not allowed'], 405);

    case 'reports':
        $summary = [
            'total_revenue' => (float) safe_value($pdo, 'SELECT COALESCE(SUM(total_amount), 0) FROM bookings'),
            'bookings_confirmed' => safe_count($pdo, 'SELECT COUNT(*) FROM bookings WHERE status = "confirmed"'),
            'photos_processed' => safe_count($pdo, 'SELECT COUNT(*) FROM uploads'),
            'average_rating' => (float) safe_value($pdo, 'SELECT COALESCE(AVG(rating), 0) FROM reviews'),
        ];

        json_response([
            'success' => true,
            'data' => $summary,
        ]);

    default:
        json_response([
            'success' => false,
            'message' => 'Unknown action',
        ], 404);
}
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'message' => 'API error: ' . $e->getMessage(),
    ], 500);
}
