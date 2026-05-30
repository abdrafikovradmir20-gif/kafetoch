// script.js - интерактивность для кафе "Наше кафе"

document.addEventListener('DOMContentLoaded', function() {
    // ==================== ПЛАВНЫЙ СКРОЛЛ ДЛЯ ЯКОРНЫХ ССЫЛОК ====================
    const navLinks = document.querySelectorAll('.nav a, .btn[href^="#"], .policy-link[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#' && targetId.startsWith('#')) {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ==================== АНИМАЦИЯ ПРИ ПОЯВЛЕНИИ (СКРОЛЛ) ====================
    const animatedElements = document.querySelectorAll('.menu-item, .gallery-grid img, .about-text, .about-image');
    
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Устанавливаем начальные стили для анимации
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // Для элементов галереи добавляем задержку
    const galleryImgs = document.querySelectorAll('.gallery-grid img');
    galleryImgs.forEach((img, index) => {
        img.style.transitionDelay = `${index * 0.1}s`;
    });

    // ==================== КНОПКА "НАВЕРХ" ====================
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '↑';
    scrollBtn.className = 'scroll-top-btn';
    scrollBtn.setAttribute('aria-label', 'Наверх');
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: #c97e3a;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
    `;
    document.body.appendChild(scrollBtn);
    
    scrollBtn.addEventListener('mouseenter', () => {
        scrollBtn.style.backgroundColor = '#a85f25';
        scrollBtn.style.transform = 'scale(1.05)';
    });
    scrollBtn.addEventListener('mouseleave', () => {
        scrollBtn.style.backgroundColor = '#c97e3a';
        scrollBtn.style.transform = 'scale(1)';
    });
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            scrollBtn.style.opacity = '1';
            scrollBtn.style.visibility = 'visible';
        } else {
            scrollBtn.style.opacity = '0';
            scrollBtn.style.visibility = 'hidden';
        }
    });
    
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // ==================== МОДАЛЬНОЕ ОКНО ДЛЯ БЛЮД МЕНЮ ====================
    const menuItems = document.querySelectorAll('.menu-item');
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: white;
        max-width: 450px;
        width: 90%;
        border-radius: 24px;
        padding: 25px;
        text-align: center;
        position: relative;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Функция открытия модалки с данными блюда
    function openModal(title, description, price, imgSrc) {
        modalContent.innerHTML = `
            <button class="modal-close" style="
                position: absolute;
                top: 12px;
                right: 16px;
                background: none;
                border: none;
                font-size: 26px;
                cursor: pointer;
                color: #888;
            ">&times;</button>
            <img src="${imgSrc}" alt="${title}" style="
                width: 100%;
                height: 180px;
                object-fit: cover;
                border-radius: 16px;
                margin-bottom: 15px;
            ">
            <h3 style="font-size: 1.6rem; color: #4a3520; margin-bottom: 10px;">${title}</h3>
            <p style="color: #6b5a4a; margin-bottom: 15px; line-height: 1.5;">${description}</p>
            <div style="font-size: 1.5rem; font-weight: bold; color: #c97e3a; margin-bottom: 20px;">${price}</div>
            <button class="modal-order-btn" style="
                background-color: #c97e3a;
                color: white;
                border: none;
                padding: 12px 28px;
                border-radius: 40px;
                font-size: 1rem;
                cursor: pointer;
                transition: background 0.3s;
            ">Забронировать столик</button>
        `;
        
        modal.style.opacity = '1';
        modal.style.visibility = 'visible';
        modalContent.style.transform = 'scale(1)';
        
        // Закрытие по крестику
        const closeBtn = modalContent.querySelector('.modal-close');
        closeBtn.addEventListener('click', closeModal);
        
        // Кнопка заказа → скролл к контактам
        const orderBtn = modalContent.querySelector('.modal-order-btn');
        orderBtn.addEventListener('click', () => {
            closeModal();
            const contactsSection = document.querySelector('#contacts');
            if (contactsSection) {
                contactsSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
    
    function closeModal() {
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
        modalContent.style.transform = 'scale(0.8)';
    }
    
    // Закрытие по клику на фон
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    
    // Добавляем кликабельность каждому элементу меню
    menuItems.forEach(item => {
        item.style.cursor = 'pointer';
        const img = item.querySelector('img');
        const title = item.querySelector('h3')?.innerText || 'Блюдо';
        const desc = item.querySelector('p')?.innerText || 'Описание';
        const price = item.querySelector('.price')?.innerText || 'Цена';
        const imgSrc = img ? img.src : 'https://via.placeholder.com/300';
        
        item.addEventListener('click', () => {
            openModal(title, desc, price, imgSrc);
        });
    });

    // ==================== МОБИЛЬНОЕ МЕНЮ (бургер) ====================
    // Создаем бургер-кнопку для мобильных устройств
    const headerContainer = document.querySelector('.header-container');
    const nav = document.querySelector('.nav');
    
    if (headerContainer && nav && window.innerWidth <= 850) {
        const burgerBtn = document.createElement('button');
        burgerBtn.innerHTML = '☰';
        burgerBtn.className = 'burger-btn';
        burgerBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            color: #b45f2b;
            display: block;
            padding: 5px 12px;
        `;
        
        // Вставляем кнопку бургера перед навигацией
        headerContainer.insertBefore(burgerBtn, nav);
        
        nav.style.transition = 'all 0.3s ease';
        
        let menuOpen = false;
        burgerBtn.addEventListener('click', () => {
            menuOpen = !menuOpen;
            if (menuOpen) {
                nav.style.display = 'flex';
                nav.style.flexDirection = 'column';
                nav.style.width = '100%';
                nav.style.backgroundColor = '#fffaf5';
                nav.style.padding = '15px 0';
                nav.style.borderRadius = '12px';
                burgerBtn.innerHTML = '✕';
            } else {
                nav.style.display = '';
                nav.style.flexDirection = '';
                burgerBtn.innerHTML = '☰';
            }
        });
        
        // При изменении размера окна сбрасываем мобильное меню
        window.addEventListener('resize', () => {
            if (window.innerWidth > 850) {
                nav.style.display = '';
                nav.style.flexDirection = '';
                burgerBtn.style.display = 'none';
            } else {
                burgerBtn.style.display = 'block';
                if (!menuOpen) {
                    nav.style.display = 'none';
                }
            }
        });
        
        // Изначально на мобильных скрываем навигацию
        if (window.innerWidth <= 850) {
            nav.style.display = 'none';
        }
    }

    // ==================== ДОБАВЛЯЕМ ЭФФЕКТ НАВЕДЕНИЯ НА ГАЛЕРЕЮ ====================
    const galleryImages = document.querySelectorAll('.gallery-grid img');
    galleryImages.forEach(img => {
        img.addEventListener('mouseenter', () => {
            img.style.filter = 'brightness(0.95)';
        });
        img.addEventListener('mouseleave', () => {
            img.style.filter = 'brightness(1)';
        });
    });

    // ==================== ФИКС ДЛЯ НЕКОРРЕКТНЫХ ССЫЛОК НА КАРТИНКИ ====================
    // Если изображения не загрузились - ставим заглушку
    const allImages = document.querySelectorAll('img');
    allImages.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'https://placehold.co/600x400/f0e4d4/8b5a2b?text=Фото+кафе';
        });
    });

    // ==================== АНИМАЦИЯ ДЛЯ ХОВЕРА КАРТОЧЕК МЕНЮ ====================
    console.log('Добро пожаловать в "Наше кафе"! ☕ Приятного аппетита!');
});

// ==================== ДОПОЛНИТЕЛЬНЫЙ ЭФФЕКТ: ПАРАЛЛАКС ДЛЯ HERO ====================
window.addEventListener('scroll', () => {
    const hero = document.querySelector('.hero');
    if (hero) {
        const scrolled = window.scrollY;
        hero.style.backgroundPositionY = `${scrolled * 0.3}px`;
    }
});