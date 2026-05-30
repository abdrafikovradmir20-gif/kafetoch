<?php
// profile.php - Личный кабинет пользователя (только для авторизованных)

session_start();

// Проверка авторизации - доступ только для вошедших пользователей
if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header('Location: login.php');
    exit();
}

// Получение данных пользователя из сессии
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$login_time = $_SESSION['login_time'] ?? time();

// Подключение к БД для получения дополнительной информации
$host = 'localhost';
$dbname = 'cafe_db';
$username_db = 'root';
$password_db = '';

$user_info = [];
$error = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем дополнительную информацию о пользователе (дата регистрации и т.д.)
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Не удалось загрузить дополнительную информацию.';
}

// Обработка выхода из аккаунта
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — Кафе «Наше кафе»</title>
    <link rel="stylesheet" href="123.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #c49a6c 0%, #a87b4f 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .profile-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 48px;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            width: 150px;
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .info-value i {
            color: #c49a6c;
            margin-right: 8px;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background: #5a6268;
        }
        
        .welcome-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .booking-history {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .booking-history h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .alert-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            color: #1565c0;
            text-align: center;
        }
        
        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }
            .info-label {
                width: 100%;
                margin-bottom: 8px;
            }
            .profile-container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container header-container">
        <div class="logo">Кафе «Наше кафе»</div>
        <nav class="nav">
            <a href="index.html">Главная</a>
            <a href="index.html#about">О нас</a>
            <a href="index.html#menu">Меню</a>
            <a href="index.html#gallery">Галерея</a>
            <a href="index.html#contacts">Контакты</a>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="profile-container">
            <?php if (!empty($error)): ?>
                <div class="alert-info"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="avatar">
                    👤
                </div>
                <h1>Добро пожаловать, <?= htmlspecialchars($username) ?>!</h1>
                <p>Ваш личный кабинет в кафе «Наше кафе»</p>
            </div>
            
            <div class="welcome-message">
                ✨ Рады видеть вас снова! Хорошего дня и приятного аппетита ✨
            </div>
            
            <div class="profile-card">
                <h3 style="margin-bottom: 20px; color: #333;">📋 Информация об аккаунте</h3>
                
                <div class="info-row">
                    <div class="info-label">🔑 ID пользователя:</div>
                    <div class="info-value">#<?= htmlspecialchars($user_id) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">👤 Имя пользователя:</div>
                    <div class="info-value"><?= htmlspecialchars($username) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">📧 Email:</div>
                    <div class="info-value"><?= htmlspecialchars($email) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">📅 Дата регистрации:</div>
                    <div class="info-value"><?= isset($user_info['created_at']) ? date('d.m.Y в H:i', strtotime($user_info['created_at'])) : 'Не указана' ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">🕐 Последний вход:</div>
                    <div class="info-value"><?= date('d.m.Y в H:i:s', $login_time) ?></div>
                </div>
                
                <div class="actions">
                    <button class="btn-edit" onclick="alert('Функция редактирования профиля в разработке')">✏️ Редактировать профиль</button>
                    <a href="?logout=1" class="btn-logout" style="display: inline-block; text-decoration: none; background: #dc3545;" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">🚪 Выйти</a>
                </div>
            </div>
            
            <div class="booking-history">
                <h3>📅 Бронирования</h3>
                <div class="alert-info">
                    У вас пока нет активных бронирований.<br>
                    <a href="book.php" style="color: #c49a6c;">Забронировать столик →</a>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container footer-container">
        <p>© 2026 Кафе «Наше кафе» — все права защищены.</p>
    </div>
</footer>

</body>
</html>