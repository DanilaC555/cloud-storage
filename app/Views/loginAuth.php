<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Облачное хранилище</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <header class="header">
        <div class="header__container flex">
            <h1 class="header__title title">Облачное хранилище</h1>
            <nav class="nav_menu flex">
                <a href="/">Главная страница</a>
                <p href="#">|</p>
                <a href="/">О нас</a>
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="main__container container">
            <h2 class="title login-register-title">Вход</h2>
            <form class="login-register-form" id="loginForm">
                <input class="login-register-input" type="email" id="email" placeholder="Email" required>
                <input class="login-register-input" type="password" id="password" placeholder="Пароль" required>
                <button class="login-register-btn btn" type="submit">Войти</button>
            </form>
            <p class="error" id="loginError"></p>
        </div>
    </main>
    <footer class="footer flex">
        <div class="footer__wrapper">
            <p>© 2025 Облачное хранилище</p>
        </div>
    </footer>
    <script defer src="./js/login.js"></script>
</body>
</html>