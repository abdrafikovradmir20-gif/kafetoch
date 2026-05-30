<?php
// register.php - Регистрация с хешированием пароля через password_hash()

session_start();

// Если уже авторизован, перенаправляем в профиль
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

$host = 'localhost';
$dbname = 'cafe_db';
$username_db = 'root';
$password_db = '';

$error = '';
$success = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = 'Ошибка подключения к базе данных.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все обязательные поля.';
    } elseif (strlen($username) < 3) {
        $error = 'Имя пользователя должно содержать не менее 3 символов.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email адрес.';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать не менее 6 символов.';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают.';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $checkStmt->execute([$email, $username]);
            
            if ($checkStmt->fetch()) {
                $error = 'Пользователь с таким email или именем уже существует.';
            } else {
                // Хеширование пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                $insertStmt->execute([$username, $email, $hashed_password]);
                
                $success = 'Регистрация прошла успешно! Теперь вы можете войти.';
                $username = $email = '';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при регистрации. Пожалуйста, попробуйте позже.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Кафе «Наше кафе»</title>
    <link rel="stylesheet" href="123.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #c49a6c;
        }
        
        .btn-register {
            width: 100%;
            background: #c49a6c;
            color: white;
            border: none;
            padding: 14px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
        }
        
        .btn-register:hover {
            background: #a87b4f;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .login-link a {
            color: #c49a6c;
            text-decoration: none;
        }
        
        .alert-error {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #c00;
        }
        
        .alert-success {
            background: #e6ffe6;
            color: #006600;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #006600;
        }
        
        .password-hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
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
        <div class="register-container">
            <h2>Регистрация</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Имя пользователя * (минимум 3 символа)</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Пароль * (минимум 6 символов)</label>
                    <input type="password" name="password" required>
                    <div class="password-hint">Пароль будет зашифрован перед сохранением в базу</div>
                </div>
                
                <div class="form-group">
                    <label>Подтверждение пароля *</label>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="register" class="btn-register">Зарегистрироваться</button>
            </form>
            
            <div class="login-link">
                Уже есть аккаунт? <a href="login.php">Войти</a>
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