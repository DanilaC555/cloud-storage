document.addEventListener("DOMContentLoaded", () => {
    const SERVER_URL = "http://cloud-storage.local";

    const deleteModal = document.getElementById("deleteModal");
    const deleteCloseButton = deleteModal.querySelector(".modules__close");
    const deleteFormModal = document.getElementById("deleteFormModal");

    const changeModal = document.getElementById("changeModal");
    const changeCloseButton = changeModal.querySelector(".modules__close");
    const changeFormModal = document.getElementById("changeFormModal");

    // Глобальные переменные, чтобы хранить ID пользователя
    let currentUserIdForDelete = null;
    let currentUserIdForChange = null;

        // Универсальная функция для управления модальными окнами
    function handleModal(modal, openButton, closeButton, additionalCloseFunction = null) {
        if (openButton) {
          openButton.addEventListener('click', () => {
            modal.style.display = 'block';
          });
        }
  
        closeButton.addEventListener('click', () => {
          modal.style.display = 'none';
          if (additionalCloseFunction) additionalCloseFunction();
        });
  
        window.addEventListener('click', (event) => {
          if (event.target === modal) {
            modal.style.display = 'none';
            if (additionalCloseFunction) additionalCloseFunction();
          }
        });
      }

    // Инициализируем модалки
    handleModal(deleteModal, null, deleteCloseButton);
    handleModal(changeModal, null, changeCloseButton);

    // Вывод имени и роли
    async function loadCurrentUser() {
        const userName = document.getElementById("user");
        const userRole = document.getElementById("role");

        if (!userName && !userRole) return;

        try {
            const response = await fetch(SERVER_URL + "/user/me", {
                credentials: "include"
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            console.log("Ответ сервера:", data);

            if (data.error) {
                userName.textContent = "Гость";
            } else {
                userName.textContent = data.email;
                userRole.textContent = '[ '+ data.role +' ]';
                userName.addEventListener("click", () => {
                    window.location.href = "profile.html";
                }); 
                userRole.addEventListener("click", () => {
                    window.location.href = "profile.html";
                }); 
            }
        } catch (error) {
            console.error("Ошибка загрузки пользователя:", error);
            userName.textContent = "Гость";
        }
    }

    // Выход
    async function logout() {
        const logoutBtn = document.getElementById("logoutBtn");

        if(!logoutBtn) return;

        logoutBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            try{
                const response = await fetch(SERVER_URL + "/logout", {
                    method: "GET",
                    credentials: "include"
                })
                const data = await response.json();
                if(data.success) {
                    window.location.href = "/";
                } else {
                    alert("Ошибка при выходе");
                }
            } catch(error) {
                console.error("Ошибка при выходе:", error);
            }
        })
    }

    // загрузки списка пользователей
    async function loadUsers() {
      try {
        const response = await fetch(SERVER_URL + "/admin/users/list", {
          method: "GET",
          credentials: "include",
          cache: "no-store"
        });
        const data = await response.json();
        const usersContainer = document.getElementById("usersContainer");
        usersContainer.innerHTML = "";
        if (data.error) {
          usersContainer.innerHTML = `<p>${data.error}</p>`;
        } else {
          // Создаем таблицу для отображения пользователей
          const table = document.createElement("table");
          table.classList.add("users-table");
          const headerRow = document.createElement("tr");
          headerRow.innerHTML = `
            <th>ID</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Действия</th>
          `;
          table.appendChild(headerRow);
          data.forEach(user => {
            const row = document.createElement("tr");
            row.innerHTML = `
              <td>${user.id}</td>
              <td>${user.email}</td>
              <td>${user.role}</td>
            `;
            const actionsCell = document.createElement("td");
            actionsCell.classList.add("actions-cell");

            // Кнопка удаления
            const deleteBtn = document.createElement("button");
            deleteBtn.classList.add("delete-btn", "btn");
            deleteBtn.textContent = "Удалить";
            deleteBtn.addEventListener("click", () => openDeleteModal(user.id));

            // Кнопка обновления
            const updateBtn = document.createElement("button");
            updateBtn.classList.add("rename-btn", "btn");
            updateBtn.textContent = "Обновить";
            updateBtn.addEventListener("click", () => openChangeModal(user.id, user.email));

            actionsCell.appendChild(deleteBtn);
            actionsCell.appendChild(updateBtn);
            row.appendChild(actionsCell);
            table.appendChild(row);
          });
          usersContainer.appendChild(table);
        }
      } catch (error) {
        console.error("Ошибка загрузки пользователей:", error);
        document.getElementById("adminError").textContent = "Ошибка загрузки пользователей.";
      }
    }
    
    // Открытие модального окна удаления
    function openDeleteModal(userId) {
        currentUserIdForDelete = userId;
        deleteModal.style.display = 'block';
    }
    
    // Обработка формы удаление пользователя
    if (deleteFormModal) {
      deleteFormModal.addEventListener("submit", async (e) => {
        e.preventDefault();
        try {
          const response = await fetch(SERVER_URL + `/admin/users/delete/${currentUserIdForDelete}`, {
            method: 'DELETE',
            credentials: "include"
          });
          const data = await response.json();
          if (data.success) {
            alert("Пользователь удален");
            loadUsers();
            deleteModal.style.display = 'none';
          } else {
            alert(data.error);
          }
        } catch(error) {
          console.error("Ошибка удаления пользователя:", error);
          alert("Ошибка удаления пользователя.");
        }
      });
    }

    // Открытие модального окна
    function openChangeModal(userId, currentEmail) {
        currentUserIdForChange = userId;
        changeModal.style.display = 'block';
  
        // Заполняем поле Email
        document.getElementById("changeModalEmailInput").value = currentEmail;
        // Очищаем поле пароля
        document.getElementById("changeModalPasswordInput").value = "";
      }
  
      // Обработка формы изменения пользователя 
      if (changeFormModal) {
        changeFormModal.addEventListener("submit", async (e) => {
          e.preventDefault();
          const newEmail = document.getElementById("changeModalEmailInput").value.trim();
          const newPassword = document.getElementById("changeModalPasswordInput").value.trim();
  
          if (!newEmail) {
            alert("Введите новый email");
            return;
          }
  
          try {
            const response = await fetch(`${SERVER_URL}/admin/users/update/${currentUserIdForChange}`, {
              method: 'PUT',
              credentials: "include",
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({ email: newEmail, password: newPassword })
            });
  
            const data = await response.json();
  
            if (data.success) {
              alert("Пользователь обновлён");
              changeModal.style.display = 'none';
              changeFormModal.reset();
              loadUsers();
            } else {
              alert(data.error);
            }
          } catch(error) {
            console.error("Ошибка изменения пользователя:", error);
            alert("Ошибка изменения пользователя.");
          }
        });
      }

    loadCurrentUser()
    logout();
    loadUsers();
});