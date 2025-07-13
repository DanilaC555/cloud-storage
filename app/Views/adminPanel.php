<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Админ панель | Облачное хранилище</title>
  <link rel="stylesheet" href="./css/style.css">
</head>
<body>
  <header class="header">
    <div class="header__container flex">
        <h1 class="header__title title">Облачное хранилище</h1>
      <nav class="nav_menu flex">
        <a id="user" href="#">Admin</a>
        <a id="role" href="#">[ Admin ]</a>
        <p>|</p>
        <a id="logoutBtn" href="#">Выход</a>
      </nav>
    </div>
  </header>
  <main class="main">
    <div class="main__container container">
      <h2>Список пользователей</h2>
      <div id="usersContainer">
        <!-- Таблица пользователей будет вставлена сюда -->
        <p id="usersLoading">Загрузка пользователей...</p>
      </div>
      <div id="adminError" class="error"></div>
    </div>
    <!-- Модальное окно ИЗМЕНЕНИЯ пользователя -->
    <div id="changeModal" class="modules">
      <div class="modules__content">
        <span class="modules__close">&times;</span>
        <h3 class="modules__title">Изменить пользователя</h3>
        <form id="changeFormModal" class="modules__form-block-input modules__form">
          <div class="form__wrapper">
            <label class="label" for="changeModalEmailInput">Новый Email:</label>
            <input type="text" id="changeModalEmailInput" class="modules__form-input" required>
          </div>
          <div class="form__wrapper">
            <label class="label label-password" for="changeModalPasswordInput">Новый пароль (необязательно):</label>
            <input type="password" id="changeModalPasswordInput" class="modules__form-input">
          </div>
          <div class="modules__form-group-btn-save-cansel">
            <button type="submit" class="modules__form-button-save-contact">Сохранить</button>
          </div>
        </form>
      </div>
    </div>
    <!-- модальное окно удаление -->
    <div id="deleteModal" class="modules">
      <div class="modules__content">
        <span class="modules__close">&times;</span>
        <h3 class="modules__title">Удалить файл 📤</h3>
        <form id="deleteFormModal" class="modules__form-block-input modules__form">
          <div class="modules__form-group-btn-save-cansel">
              <button type="submit" class="modules__form-button-save-contact">Удалить</button>
          </div>
        </form>
      </div>
    </div>
  </main>
  <footer class="footer flex">
    <div class="footer__wrapper">
        <p>© 2025 Облачное хранилище</p>
    </div>
</footer>
  <script type="module" src="./js/admin.js"></script>
</body>
</html>
