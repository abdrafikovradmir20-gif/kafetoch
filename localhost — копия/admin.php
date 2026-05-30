<?php
// admin.php - Просмотр бронирований (защитите паролем или IP)

// Простая аутентификация (измените пароль!)
$adminPassword = 'cafe2026'; // Пароль для входа

session_start();
if (!isset($_SESSION['admin_logged']) && (!isset($_POST['password']) || $_POST['password'] !== $adminPassword)) {
    if (isset($_POST['password'])) {
        $error = 'Неверный пароль!';
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Вход в админ-панель</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #fefaf5;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                text-align: center;
            }
            input {
                padding: 10px;
                margin: 10px 0;
                width: 200px;
                border: 1px solid #ddd;
                border-radius: 8px;
            }
            button {
                background: #c97e3a;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                cursor: pointer;
            }
            .error { color: red; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Вход в админ-панель</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Введите пароль" required>
                <br>
                <button type="submit">Войти</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
} else {
    $_SESSION['admin_logged'] = true;
}

$bookings = loadBookings();

function loadBookings() {
    if (file_exists('bookings.json')) {
        return json_decode(file_get_contents('bookings.json'), true) ?? [];
    }
    return [];
}

// Обновление статуса
if (isset($_GET['action']) && isset($_GET['id'])) {
    $all = loadBookings();
    foreach ($all as &$b) {
        if ($b['id'] === $_GET['id']) {
            $b['status'] = $_GET['action'];
            break;
        }
    }
    file_put_contents('bookings.json', json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | Бронирования</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5ebe0; padding: 30px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #b45f2b; margin-bottom: 20px; }
        table { width: 100%; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #c97e3a; color: white; }
        tr:hover { background: #fef8f0; }
        .status-new { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .status-confirmed { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .status-cancelled { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .btn { padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 12px; margin: 0 2px; display: inline-block; }
        .btn-confirm { background: #28a745; color: white; }
        .btn-cancel { background: #dc3545; color: white; }
        .logout { float: right; background: #6c757d; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        @media (max-width: 800px) { table, thead, tbody, th, td, tr { display: block; } th { display: none; } tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; } td { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; } td:before { content: attr(data-label); font-weight: bold; width: 40%; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Управление бронированиями <a href="?logout=1" class="logout" onclick="return confirm('Выйти?')">Выйти</a></h1>
        <?php if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; } ?>
        
        <?php if (empty($bookings)): ?>
            <p style="background: white; padding: 30px; text-align: center; border-radius: 16px;">Пока нет ни одного бронирования.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Имя</th><th>Телефон</th><th>Дата</th><th>Время</th><th>Гостей</th><th>Статус</th><th>Действия</th></tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($bookings) as $b): ?>
                    <tr>
                        <td data-label="ID"><?php echo substr($b['id'], -6); ?></td>
                        <td data-label="Имя"><?php echo htmlspecialchars($b['name']); ?></td>
                        <td data-label="Телефон"><?php echo htmlspecialchars($b['phone']); ?></td>
                        <td data-label="Дата"><?php echo date('d.m.Y', strtotime($b['date'])); ?></td>
                        <td data-label="Время"><?php echo $b['time']; ?></td>
                        <td data-label="Гостей"><?php echo $b['guests']; ?></td>
                        <td data-label="Статус"><span class="status-<?php echo $b['status']; ?>"><?php echo $b['status'] == 'new' ? 'Новая' : ($b['status'] == 'confirmed' ? 'Подтверждена' : 'Отменена'); ?></span></td>
                        <td data-label="Действия">
                            <?php if ($b['status'] == 'new'): ?>
                                <a href="?action=confirmed&id=<?php echo $b['id']; ?>" class="btn btn-confirm" onclick="return confirm('Подтвердить бронирование?')">✅ Подтвердить</a>
                                <a href="?action=cancelled&id=<?php echo $b['id']; ?>" class="btn btn-cancel" onclick="return confirm('Отменить бронирование?')">❌ Отменить</a>
                            <?php elseif ($b['status'] == 'confirmed'): ?>
                                <a href="?action=cancelled&id=<?php echo $b['id']; ?>" class="btn btn-cancel" onclick="return confirm('Отменить бронирование?')">❌ Отменить</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>