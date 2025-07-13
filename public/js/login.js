document.addEventListener("DOMContentLoaded", () => {
    const SERVER_URL = "http://cloud-storage.local";

    // Логин
    async function login() {
        const loginForm = document.getElementById('loginForm');
        const loginError = document.getElementById("loginError");

        if (loginForm) {
            loginForm.addEventListener("submit", async (e) => {
                e.preventDefault();

                const email = document.getElementById("email").value;
                const password = document.getElementById("password").value;

                try {
                    const response = await fetch(SERVER_URL + "/login", {
                        method: "POST",
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({email, password}),
                        credentials: "include", // чтобы передавались куки (сессионный идентификатор)
                        cache: "no-store" // гарантирует, что запрос всегда будет выполнен заново, и мы получите корректный и полный JSON-ответ
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    if (data.success) {
                      const meResponse = await fetch(SERVER_URL + "/user/me", {
                        credentials: "include"
                      });
                      
                      const meData = await meResponse.json();

                      // console.log("meData:", meData);
                      if (meData.role === "admin") {
                        window.location.href = "/adminPanel";
                      } else {
                        window.location.href = "/dashboard";
                      }
                    } else {
                      loginError.textContent = data.error;
                    }
                  } catch (error) {
                    console.error("Ошибка при входе:", error);
                    loginError.textContent = "Произошла ошибка при входе, пожалуйста, попробуйте позже.";
                  }
                });
        }
    }

    login();
});