<?php
// book.php - Система бронирования столиков для кафе "Наше кафе"

// Настройки
$bookingsFile = 'bookings.json';
$adminEmail = 'admin@cafe-schiel.ru'; // Email администратора (измените на свой)

// Обработка отправки формы
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_submit'])) {
    // Валидация данных
    $errors = [];
    
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $guests = (int)($_POST['guests'] ?? 1);
    $comment = trim($_POST['comment'] ?? '');
    
    // Проверки
    if (empty($name)) {
        $errors[] = 'Пожалуйста, укажите ваше имя';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Имя должно содержать минимум 2 символа';
    }
    
    if (empty($phone)) {
        $errors[] = 'Пожалуйста, укажите номер телефона';
    } elseif (!preg_match('/^[\+\d\s\-\(\)]{10,20}$/', $phone)) {
        $errors[] = 'Введите корректный номер телефона';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email адрес';
    }
    
    if (empty($date)) {
        $errors[] = 'Пожалуйста, выберите дату';
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Дата не может быть в прошлом';
    }
    
    if (empty($time)) {
        $errors[] = 'Пожалуйста, выберите время';
    }
    
    if ($guests < 1 || $guests > 20) {
        $errors[] = 'Количество гостей должно быть от 1 до 20';
    }
    
    // Проверка на дублирование бронирования (столик уже занят?)
    if (empty($errors)) {
        $existingBookings = loadBookings();
        $datetime = $date . ' ' . $time;
        $isBooked = false;
        
        foreach ($existingBookings as $booking) {
            if ($booking['date'] === $date && $booking['time'] === $time) {
                $isBooked = true;
                break;
            }
        }
        
        if ($isBooked) {
            $errors[] = 'Извините, этот временной слот уже занят. Пожалуйста, выберите другое время.';
        }
    }
    
    // Если ошибок нет - сохраняем бронирование
    if (empty($errors)) {
        $booking = [
            'id' => uniqid(),
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
            'comment' => $comment,
            'status' => 'new', // new, confirmed, cancelled
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $allBookings = loadBookings();
        $allBookings[] = $booking;
        
        if (saveBookings($allBookings)) {
            $messageType = 'success';
            $message = 'Спасибо! Ваша заявка на бронирование принята. Мы свяжемся с вами для подтверждения в ближайшее время.';
            
            // Отправка email уведомления администратору
            sendNotificationEmail($booking, $adminEmail);
            
            // Отправка подтверждения клиенту
            if (!empty($email)) {
                sendConfirmationEmail($booking, $email);
            }
            
            // Очищаем форму
            $_POST = [];
        } else {
            $messageType = 'error';
            $message = 'Произошла ошибка при сохранении бронирования. Пожалуйста, попробуйте позже.';
        }
    } else {
        $messageType = 'error';
        $message = implode('<br>', $errors);
    }
}

// Функция загрузки бронирований из файла
function loadBookings() {
    global $bookingsFile;
    if (file_exists($bookingsFile)) {
        $content = file_get_contents($bookingsFile);
        return json_decode($content, true) ?? [];
    }
    return [];
}

// Функция сохранения бронирований
function saveBookings($bookings) {
    global $bookingsFile;
    return file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Отправка email уведомления администратору
function sendNotificationEmail($booking, $adminEmail) {
    $subject = 'Новое бронирование в кафе "Наше кафе"';
    $message = "
        <html>
        <head>
            <title>Новое бронирование</title>
        </head>
        <body>
            <h2>Новая заявка на бронирование столика</h2>
            <p><strong>Имя:</strong> {$booking['name']}</p>
            <p><strong>Телефон:</strong> {$booking['phone']}</p>
            <p><strong>Email:</strong> " . ($booking['email'] ?: 'не указан') . "</p>
            <p><strong>Дата:</strong> " . date('d.m.Y', strtotime($booking['date'])) . "</p>
            <p><strong>Время:</strong> {$booking['time']}</p>
            <p><strong>Гостей:</strong> {$booking['guests']}</p>
            <p><strong>Комментарий:</strong> " . ($booking['comment'] ?: 'нет') . "</p>
            <p><strong>Дата заявки:</strong> {$booking['created_at']}</p>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@cafe-schiel.ru" . "\r\n";
    
    @mail($adminEmail, $subject, $message, $headers);
}

// Отправка подтверждения клиенту
function sendConfirmationEmail($booking, $clientEmail) {
    $subject = 'Подтверждение бронирования | Кафе "Наше кафе"';
    $message = "
        <html>
        <head>
            <title>Подтверждение бронирования</title>
        </head>
        <body style='font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #fefaf5; border-radius: 16px;'>
                <h2 style='color: #c97e3a;'>Кафе «Наше кафе»</h2>
                <p>Здравствуйте, <strong>{$booking['name']}</strong>!</p>
                <p>Ваша заявка на бронирование столика успешно получена.</p>
                
                <div style='background: #fff; padding: 15px; border-radius: 12px; margin: 20px 0;'>
                    <h3 style='color: #4a3520; margin-top: 0;'>Детали бронирования:</h3>
                    <p><strong>📅 Дата:</strong> " . date('d.m.Y', strtotime($booking['date'])) . "</p>
                    <p><strong>⏰ Время:</strong> {$booking['time']}</p>
                    <p><strong>👥 Количество гостей:</strong> {$booking['guests']}</p>
                    " . ($booking['comment'] ? "<p><strong>📝 Ваш комментарий:</strong> {$booking['comment']}</p>" : "") . "
                </div>
                
                <p>Мы свяжемся с вами в ближайшее время для подтверждения бронирования.</p>
                <p>Ждем вас в нашем кафе! ☕</p>
                <p style='color: #888; font-size: 12px; margin-top: 20px;'>
                    г. Стерлитамак, ул. Ленина, д. 8 | +7 (347) 123-45-67
                </p>
            </div>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: booking@cafe-schiel.ru" . "\r\n";
    
    @mail($clientEmail, $subject, $message, $headers);
}

// Получение доступных временных слотов (можно расширить логику)
function getAvailableTimeSlots() {
    return [
        '12:00', '13:00', '14:00', '15:00', '16:00', 
        '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'
    ];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование столика | Кафе «Наше кафе»</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fefaf5 0%, #f5ebe0 100%);
            min-height: 100vh;
        }
        
        .booking-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .booking-header {
            background: linear-gradient(135deg, #b45f2b, #c97e3a);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .booking-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-family: 'Georgia', serif;
        }
        
        .booking-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .booking-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a3520;
        }
        
        .required::after {
            content: '*';
            color: #c97e3a;
            margin-left: 4px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e8ddd0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #c97e3a;
            box-shadow: 0 0 0 3px rgba(201, 126, 58, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-submit {
            width: 100%;
            background: #c97e3a;
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background: #a85f25;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(201, 126, 58, 0.3);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            width: 100%;
            color: #b45f2b;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .info-text {
            font-size: 0.85rem;
            color: #888;
            margin-top: 5px;
        }
        
        @media (max-width: 550px) {
            .booking-container {
                margin: 20px auto;
                padding: 15px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
            
            .booking-header {
                padding: 20px;
            }
            
            .booking-header h1 {
                font-size: 1.5rem;
            }
            
            .booking-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="booking-card">
            <div class="booking-header">
                <h1>📅 Бронирование столика</h1>
                <p>Забронируйте столик в нашем кафе заранее</p>
            </div>
            
            <div class="booking-form">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="bookingForm">
                    <div class="form-group">
                        <label class="required">Ваше имя</label>
                        <input type="text" name="name" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="Иван Иванов">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Телефон</label>
                            <input type="tel" name="phone" required 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   placeholder="+7 (900) 123-45-67">
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   placeholder="example@mail.ru">
                            <div class="info-text">Для отправки подтверждения</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Дата</label>
                            <input type="date" name="date" required 
                                   value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d', strtotime('+1 day'))); ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="required">Время</label>
                            <select name="time" required>
                                <option value="">Выберите время</option>
                                <?php foreach (getAvailableTimeSlots() as $slot): ?>
                                    <option value="<?php echo $slot; ?>" 
                                        <?php echo (($_POST['time'] ?? '') === $slot) ? 'selected' : ''; ?>>
                                        <?php echo $slot; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Количество гостей</label>
                        <select name="guests" required>
                            <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>" 
                                    <?php echo (($_POST['guests'] ?? 2) == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> <?php echo getGuestEnding($i); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Особые пожелания</label>
                        <textarea name="comment" placeholder="Например: нужен столик у окна, отмечаем день рождения..."><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="book_submit" class="btn-submit">
                        ✅ Забронировать столик
                    </button>
                </form>
                
                <a href="index.html" class="back-link">← Вернуться на главную</a>
            </div>
        </div>
    </div>
    
    <script>
        // Валидация формы на клиентской стороне
        document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
            const phone = this.querySelector('[name="phone"]').value;
            const phoneRegex = /^[\+\d\s\-\(\)]{10,20}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Пожалуйста, введите корректный номер телефона');
                return false;
            }
            
            const date = this.querySelector('[name="date"]').value;
            const today = new Date().toISOString().split('T')[0];
            if (date < today) {
                e.preventDefault();
                alert('Дата не может быть в прошлом');
                return false;
            }
            
            const time = this.querySelector('[name="time"]').value;
            if (!time) {
                e.preventDefault();
                alert('Пожалуйста, выберите время');
                return false;
            }
            return true;
        });
        
        // Установка минимальной даты для поля date
        const dateInput = document.querySelector('[name="date"]');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
        }
    </script>
</body>
</html>

<?php
// Функция для склонения слова "гость"
function getGuestEnding($number) {
    $number = $number % 100;
    if ($number >= 11 && $number <= 14) {
        return 'гостей';
    }
    $lastDigit = $number % 10;
    if ($lastDigit == 1) return 'гость';
    if ($lastDigit >= 2 && $lastDigit <= 4) return 'гостя';
    return 'гостей';
}
?>