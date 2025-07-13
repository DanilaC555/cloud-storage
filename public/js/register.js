    document.addEventListener("DOMContentLoaded", () => {
    const SERVER_URL = "http://cloud-storage.local";
    // регистрация
    async function register() {
        const registerForm = document.getElementById('registerForm');
        const registerError = document.getElementById("registerError");
    
     if (registerForm) {
            registerForm.addEventListener("submit", async (e) => {
                e.preventDefault();
    
             const email = document.getElementById("email").value;
                const password = document.getElementById("password").value;
    
             try {
                    const response = await fetch(SERVER_URL + "/register", {
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
                        window.location.href = "/dashboard";
                    } else {
                        registerError.textContent = data.error;
                    }
                } catch (error) {
                    console.error("Ошибка при регистрации:", error);
                    registerError.textContent = "Произошла ошибка при регистрации, пожалуйста, попробуйте позже.";
                }
            })
        }
    }
    register();
});