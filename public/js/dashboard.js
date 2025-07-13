document.addEventListener("DOMContentLoaded", () => {
    const SERVER_URL = "http://cloud-storage.local";

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

    // Элементы модального окна загрузки файла
    const uploadModal = document.getElementById("uploadModal");
    const openUploadModalButton = document.getElementById("openUploadModalButton");
    const uploadCloseButton = uploadModal.querySelector(".modules__close");
    const uploadForm = document.getElementById("uploadFormModal");

    const changeModal = document.getElementById("changeModal");
    const changeCloseButton = changeModal.querySelector(".modules__close");
    const changeFormModal = document.getElementById("changeFormModal");

    const deleteModal = document.getElementById("deleteModal");
    const deleteCloseButton = deleteModal.querySelector(".modules__close");
    const deleteFormModal = document.getElementById("deleteFormModal");

    const shareAccessModal = document.getElementById("shareAccessModal");
    const shareAccessCloseButton = shareAccessModal.querySelector(".modules__close");
    const shareAccessFormModal = document.getElementById("shareAccessFormModal");

    const accessListModal = document.getElementById("accessListModal");
    const accessListClose = accessListModal.querySelector(".modules__close");
    const accessListContent = document.getElementById("accessListContent");

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

    // Инициализируем модальное окно
    handleModal(uploadModal, openUploadModalButton, uploadCloseButton, () => {
      if (uploadForm) uploadForm.reset();
    });
    
    handleModal(changeModal, null, changeCloseButton);
    handleModal(deleteModal, null, deleteCloseButton);
    handleModal(shareAccessModal, null, shareAccessCloseButton);
    handleModal(accessListModal, null, accessListClose);

    // список файлов 
    async function loadFiles() {
        const loadError = document.getElementById("loadError");
        try {
            const response = await fetch(SERVER_URL + "/files/list", {
                method: "GET",
                credentials: "include",
                cache: "no-store"
            });
 
            const data = await response.json();
            const fileList = document.getElementById("fileList");
            fileList.innerHTML = ""; // очищаем список
            
            if(data.error) {
                fileList.innerHTML = `<li>${data.error}</li>`;
                loadError.textContent = "Ошибка загрузки файлов";
            } else {
                data.forEach(file => {
                    
                    let li = document.createElement("li");
                    li.textContent = shortenFileName(file.original_name, 25); // сокращённое имя
                    li.title = file.original_name; // полный текст в tooltip
                    li.classList.add("li-file", "btn");
                
                    let deleteBtn = document.createElement('button');
                    deleteBtn.classList.add("delete-btn", "btn");
                    deleteBtn.textContent = "Удалить";
                    deleteBtn.addEventListener("click", () => openDeleteModal(file.id));
                
                    let renameBtn = document.createElement('button');
                    renameBtn.classList.add("rename-btn", "btn");
                    renameBtn.id = "openChangeModalButton";
                    renameBtn.textContent = "Изменить";
                    renameBtn.addEventListener("click", () => openRenameModal(file.id, file.original_name));

                    let saveBtn = document.createElement('button');
                    saveBtn.classList.add("save-btn", "btn");
                    saveBtn.textContent = "Сохранить";
                    saveBtn.addEventListener("click", () => saveFile(file.id));
                    
                    let accessBtn = document.createElement('button');
                    accessBtn.classList.add("access-btn", "btn");
                    accessBtn.textContent = "поделиться";
                    // accessBtn.addEventListener("click", () => shareAccess(file)); 
                    accessBtn.addEventListener("click", () => openShareAccessModal(file));
                    
                    let showAccessBtn = document.createElement('button');
                    showAccessBtn.classList.add("access-list-btn", "btn");
                    showAccessBtn.textContent = "Список доступа";
                    showAccessBtn.addEventListener("click", () => loadSharedUsers(file.id));

                    li.appendChild(saveBtn);
                    li.appendChild(renameBtn);
                    li.appendChild(deleteBtn);
                    li.appendChild(accessBtn);
                    li.appendChild(showAccessBtn);
                    fileList.appendChild(li);
                });
            }
        } catch(error) {
            console.error("Ошибка загрузки файлов:", error);
        }
    }

    // сокращение имени файла
    function shortenFileName(fileName, maxLength = 50) {
        if (fileName.length <= maxLength) {
            return fileName;
        }
        const ellipsis = "..";
        const dotIndex = fileName.lastIndexOf('.');
        let extension = "";
        let namePart = fileName;
        if (dotIndex !== -1) {
            extension = fileName.substring(dotIndex);
            namePart = fileName.substring(0, dotIndex);
        }
        const available = maxLength - extension.length - ellipsis.length;
        if (available <= 0) {
            return fileName;
        }
        return namePart.substring(0, available) + ellipsis + extension;
    }

    function openDeleteModal(fileId) {
        currentFileIdForDelete = fileId;
        deleteModal.style.display = 'block';
      }

      if (deleteFormModal) {
        deleteFormModal.addEventListener("submit", async (e) => {
          e.preventDefault();
          try {
            const response = await fetch(SERVER_URL + `/files/remove/${currentFileIdForDelete}`, {
                method: 'DELETE',
                credentials: "include"
            });
    
            const data = await response.json();
    
            if(data.success) {
                alert("Файл удален");
                loadFiles();
                deleteModal.style.display = 'none';
            } else {
                alert(data.error);
            }
        } catch(error) {
            console.error("Ошибка удаления файла:", error);
            alert("Ошибка удаления файла.");
        }
        })
    }

    // Функция загрузки файла
    async function uploadFile(e) {
        e.preventDefault();
        const fileInput = document.getElementById("modalFileInput");
        const uploadError = document.getElementById("uploadError");
        if (fileInput.files.length === 0) {
            uploadError.textContent = "Файл не выбран";
            return;
        }
        const formData = new FormData();
        formData.append("file", fileInput.files[0]);
        try {
            const response = await fetch(SERVER_URL + "/files/add", {
                method: 'POST',
                credentials: "include",
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                uploadError.textContent = "";
                loadFiles();
                uploadModal.style.display = 'none';
                uploadForm.reset();
            } else {
                uploadError.textContent = data.error;
            }
        } catch (error) {
            console.error("Ошибка загрузки файла:", error);
            uploadError.textContent = "Ошибка загрузки файла, попробуйте позже.";
        }
    }

    // вызов загрузки файла
    if (uploadForm) {
        uploadForm.addEventListener("submit", uploadFile);
    }

    // открытия модального окна изменения файла
    function openRenameModal(fileId, currentName) {
      currentFileIdForRename = fileId;

      changeModal.style.display = 'block';
      // Заполняем поле ввода текущим именем
      const input = document.getElementById("changeModalModalFileInput");
      input.value = currentName || "";
    }

    // Обработчик отправки формы изменения имени файла
    if (changeFormModal) {
      changeFormModal.addEventListener("submit", async (e) => {
        e.preventDefault();
        const newName = document.getElementById("changeModalModalFileInput").value.trim();
        if (!newName) {
          alert("Введите новое имя файла");
          return;
        }
        try {
          const response = await fetch(SERVER_URL + "/files/rename", {
            method: 'PUT',
            credentials: "include",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentFileIdForRename, new_name: newName })
          });
          const data = await response.json();
          if (data.success) {
            alert("Файл переименован");
            changeModal.style.display = 'none';
            changeFormModal.reset();
            loadFiles();
          } else {
            alert("Ошибка: " + data.error);
          }
        } catch (error) {
          console.error("Ошибка изменения файла:", error);
          alert("Ошибка изменения файла.");
        }
      });
    }

    // скачивание файла
    function saveFile(id) {
        window.location.href = SERVER_URL + `/files/download/${id}`;
    }

    // открытия модального окна добавление доступа польщователю
    function openShareAccessModal(file) {
        currentFileForShare = file; 
        shareAccessModal.style.display = 'block';

        document.getElementById("shareAccessModalFileInput").value = "";
    }

    if(shareAccessFormModal) {
        shareAccessFormModal.addEventListener("submit", async (e) => {
            e.preventDefault();
            const email = document.getElementById("shareAccessModalFileInput").value.trim();
            if (!email) {
              alert("Введите почту пользователя:");
              return;
            }
            try {
              // Ищем пользователя по email
              const searchResponse = await fetch(`${SERVER_URL}/user/search/${encodeURIComponent(email)}`, {
                method: 'GET',
                credentials: 'include'
              });
              const userData = await searchResponse.json();
              if (userData.error) {
                alert("Пользователь не найден");
                return;
              }

            // иницилизация модального окна подтверждения доступа
              function showConfirmModal(message) {
                return new Promise((resolve) => {
                  const confirmModal = document.getElementById("confirmModal");
                  const confirmMessage = document.getElementById("confirmMessage");
                  const confirmYes = document.getElementById("confirmYes");
                  const confirmNo = document.getElementById("confirmNo");
              
                  // Устанавливаем сообщение
                  confirmMessage.innerHTML = message;

                  confirmModal.style.display = "block";
              
                  function cleanUp() {
                    confirmModal.style.display = "none";
                    confirmYes.removeEventListener("click", onYes);
                    confirmNo.removeEventListener("click", onNo);
                  }
              
                  function onYes() {
                    cleanUp();
                    alert("Доступ предоставлен");
                    resolve(true);
                  }
              
                  function onNo() {
                    cleanUp();
                    resolve(false);
                  }
              
                    confirmYes.addEventListener("click", onYes);
                    confirmNo.addEventListener("click", onNo);
                });
              }

              // сохранённый объект currentFileForShare
              if (showConfirmModal(`<div class="access-confirmations">
                                        <div class="access-confirmations-box-one">
                                            Предоставить доступ файлу:
                                            <span class="file-name">${currentFileForShare.original_name}</span> 
                                        </div>
                                        <div class="access-confirmations-box-two">
                                            пользователю:
                                            <span class="user-email">${userData.email}</span>
                                        </div>
                                    <div>`)) {
                const shareResponse = await fetch(`${SERVER_URL}/files/share/${currentFileForShare.id}/${userData.id}`, {
                  method: 'PUT',
                  credentials: 'include',
                  headers: { 'Content-Type': 'application/json' }
                });
                const shareResult = await shareResponse.json();
                if (shareResult.success) {
                  shareAccessModal.style.display = 'none';
                  shareAccessFormModal.reset();
                } else {
                  alert("Ошибка предоставления доступа: " + shareResult.error);
                }
              }
            } catch (error) {
              console.error("Ошибка предоставления доступа:", error);
              alert("Ошибка предоставления доступа");
            }
          });
    }

    // список пользователей имеющих доступ к файлам
    async function loadSharedUsers(fileId) {
        try {
            const response = await fetch(`${SERVER_URL}/files/share/${fileId}`, {
                method: 'GET',
                credentials: 'include'
            });
            const users = await response.json();
          
            if (users.length === 0) {
              listHtml = '<p class="shared__item">Список доступа пуст</p>';
            } else {
              listHtml = '<ul class="shared__list list__files list-reset">';
              users.forEach(user => {
                listHtml += `
                  <li class="shared__item">
                    <p class="shared__desrc">${user.email}</p>
                    <button class="delete-btn-access btn" onclick="removeUserAccess(${fileId}, ${user.id})">
                      Удалить доступ
                    </button>
                  </li>`;
              });
              listHtml += '</ul>';
            }
            
            accessListContent.innerHTML = listHtml;

            accessListModal.style.display = 'block';
        } catch (error) {
            console.error("Ошибка получения списка пользователей с доступом:", error);
        }
    }
    
    // удалить досутп к файлу
    async function removeUserAccess(fileId, userId) {
        if (!confirm("Вы уверены, что хотите удалить доступ этому пользователю?")) return;
        try {
            const response = await fetch(`${SERVER_URL}/files/share/${fileId}/${userId}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            const data = await response.json();
            if (data.success) {
                alert("Доступ удален");
                // Обновляем список пользователей с доступом
                loadSharedUsers(fileId);
            } else {
                alert("Ошибка удаления доступа: " + data.error);
            }
        } catch (error) {
            console.error("Ошибка удаления доступа:", error);
        }
    }

    window.removeUserAccess = removeUserAccess;
    
    loadCurrentUser();
    logout();
    loadFiles();
});