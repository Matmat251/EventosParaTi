document.addEventListener("DOMContentLoaded", async () => {
    try {
         const response = await fetch('auth.php');
        const data = await response.json();

        const loginItem = document.getElementById('loginLogin');
        const logoutItem = document.getElementById('logoutLink');
        const userBox = document.getElementById('userBox');
        const userGreeting = document.getElementById('userGreeting');

        if (data.logged) {
            loginItem.classList.add('d-none');
            logoutItem.classList.remove('d-none');
            userBox.classList.remove('d-none');

            userGreeting.textContent = `Hola, ${data.username}`;
        } else {
            loginItem.classList.remove('d-none');
            logoutItem.classList.add('d-none');
            userBox.classList.add('d-none');
        }
    } catch (error) {
        console.error('Error cargando estado de sesión:', error);
    }

    // -------------------------------- LOGOUT --------------------------------//
    const btnLogout = document.getElementById("btnLogout");

    if (btnLogout) {
        btnLogout.addEventListener("click", async () => {
            try {
                const res = await fetch("logout.php", { method: "POST" });
                const r = await res.text();

                if (r.trim() === "OK") {
                    localStorage.removeItem("userName");
                    window.location.href = "index.html";
                } else {
                    console.error("Error al cerrar sesión:", r);
                }

            } catch (e) {
                console.error("Error de conexión:", e);
            }
        });
    }

});

/* ============================================================
       ====================   REGISTER   ========================
       ============================================================ */
    