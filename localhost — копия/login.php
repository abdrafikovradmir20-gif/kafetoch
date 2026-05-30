<?php
// login.php - Страница входа с проверкой через password_verify()

// Запускаем сессию для работы с $_SESSION
session_start();

// Если пользователь уже авторизован, перенаправляем в профиль
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

// Настройки подключения к базе данных
$host = 'localhost';
$dbname = 'cafe_db';
$username_db = 'root';
$password_db = '';

$error = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = 'Ошибка подключения к базе данных.';
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $login_input = trim($_POST['login_input'] ?? ''); // может быть email или username
    $password = $_POST['password'] ?? '';
    
    if (empty($login_input) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля.';
    } else {
        try {
            // Поиск пользователя по email ИЛИ username
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();
            
            // Проверка пароля с помощью password_verify()
            if ($user && password_verify($password, $user['password'])) {
                // Пароль верный - создаем сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                // Перенаправление в личный кабинет
                header('Location: profile.php');
                exit();
            } else {
                $error = 'Неверный email/username или пароль.';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при входе. Пожалуйста, попробуйте позже.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Кафе «Наше кафе»</title>
    <link rel="stylesheet" href="123.css">
    <style>
        .login-container {
            max-width: 450px;
            margin: 60px auto;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
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
            box-shadow: 0 0 0 3px rgba(196,154,108,0.1);
        }
        
        .btn-login {
            width: 100%;
            background: #c49a6c;
            color: white;
            border: none;
            padding: 14px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #a87b4f;
            transform: translateY(-2px);
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }
        
        .register-link a {
            color: #c49a6c;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert-error {
            background: #fee;
            color: #c00;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #c00;
            font-size: 14px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-link a {
            color: #888;
            text-decoration: none;
            font-size: 13px;
        }
        
        .back-link a:hover {
            color: #c49a6c;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            color: #bbb;
            font-size: 12px;
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
        <div class="login-container">
            <h2>Вход в аккаунт</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email или Имя пользователя</label>
                    <input type="text" name="login_input" placeholder="example@email.com или username" value="<?= htmlspecialchars($_POST['login_input'] ?? '') ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" name="login" class="btn-login">Войти</button>
            </form>
            
            <div class="divider">или</div>
            
            <div class="register-link">
                Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
            </div>
            
            <div class="back-link">
                <a href="index.html">← Вернуться на главную</a>
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