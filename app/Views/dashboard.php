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
                <a id="user" href="#">Гость</a>
                <a id="role" href="#">[ Гость ]</a>
                <p href="#">|</p>
                <a id="logoutBtn" href="#">Выход</a>
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="main__container container">
          <h2 class="title">Личный кабинет</h2>
          <div class="main__wrapper flex">
              <div class="main__wrapper-content-box">
                  <h3 class="dashboard__title">📂 Мои файлы</h3>
                  <p class="error" id="loadError"></p>
                  <ul class="list__files list-reset" id="fileList">
                      <li>Загрузка файлов...</li>
                  </ul>  
              </div>
              <div class="main__wrapper-content-box">
                  <!-- Модальное окно загрузки файла -->
                  <div id="uploadModal" class="modules">
                      <div class="modules__content">
                        <span class="modules__close">&times;</span>
                        <h3 class="modules__title">Загрузить файл 📤</h3>
                        <form id="uploadFormModal" class="modules__form-block-input modules__form">
                          <input type="file" id="modalFileInput" class="modules__form-input" required>
                          <div class="modules__form-group-btn-save-cansel">
                            <button type="submit" class="modules__form-button-save-contact">Загрузить</button>
                          </div>
                        </form>
                        <p class="error" id="uploadError"></p>
                  </div>
                  </div>
                    <!-- Кнопка для открытия модального окна -->
                    <button id="openUploadModalButton" class="container__btn-add">Добавить файл</button>
              </div>     
            </div>
            <!-- модальное окно изменение -->
            <div id="changeModal" class="modules">
                <div class="modules__content">
                  <span class="modules__close">&times;</span>
                  <h3 class="modules__title">Изменить файл 📤</h3>
                  <form id="changeFormModal" class="modules__form-block-input modules__form">
                    <div class="form__wrapper">
                        <label class="label" for="text">Введите новое имя:</label>
                        <input type="text" id="changeModalModalFileInput" class="modules__form-input" required>
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
            <!-- модальное окно поделиться -->
            <div id="shareAccessModal" class="modules">
                <div class="modules__content">
                  <span class="modules__close">&times;</span>
                  <h3 class="modules__title">поделиться файлом 📤</h3>
                  <form id="shareAccessFormModal" class="modules__form-block-input modules__form">
                    <div class="form__wrapper">
                        <label class="label" for="text">Введите email пользователя:</label>
                        <input type="text" id="shareAccessModalFileInput" class="modules__form-input" required>
                    </div>
                    <div class="modules__form-group-btn-save-cansel">
                      <button type="submit" class="modules__form-button-save-contact">Сохранить</button>
                    </div>
                  </form>
                </div>
            </div>
            <!-- Модальное окно подтверждения передачи файла польщователю-->
            <div id="confirmModal" class="modules">
              <div class="modules__content">
                <!-- <h3 class="modules__title">Предоставление доступа</h3> -->
                <p id="confirmMessage" class="confirm-message">Подтверждение</p>
                <div class="modules__form-group-btn-save-cansel">
                  <button id="confirmYes" class="modules__form-button-save-contact">Да</button>
                  <button id="confirmNo" class="modules__form-button-cansel">Нет</button>
                </div>
              </div>
            </div>
        </div>
        <!-- Модальное окно для списка доступа -->
        <div id="accessListModal" class="modules">
          <div class="modules__content">
            <span class="modules__close">&times;</span>
            <h3 class="modules__title">Список доступа</h3>
            <div id="accessListContent"></div>
          </div>
        </div>
    </main>
    <footer class="footer flex">
        <div class="footer__wrapper">
            <p>© 2025 Облачное хранилище</p>
        </div>
    </footer>
    <script defer src="./js/dashboard.js"></script>
</body>
</html>