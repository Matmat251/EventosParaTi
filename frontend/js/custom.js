
(function ($) {

  "use strict";

  //--------------------- MENÚ ---------------------

  // Ocultar menú en móvil al hacer clic
  $('.navbar-collapse a').on('click', function () {
    $(".navbar-collapse").collapse('hide');
  });

  // CUSTOM LINK
    $('.smoothscroll').click(function(){
      var el = $(this).attr('href');
      var elWrapped = $(el);
      var header_height = $('.navbar').height();
  
      scrollToDiv(elWrapped,header_height);
      return false;
  
      function scrollToDiv(element,navheight){
        var offset = element.offset();
        var offsetTop = offset.top;
        var totalScroll = offsetTop-navheight;
  
        $('body,html').animate({
        scrollTop: totalScroll
        }, 300);
      }
    });
  

  // -------- VALIDACIÓN Y ENVÍO DE REGISTRO ------- //

  document.addEventListener("DOMContentLoaded", function () {

    console.log("custom.js cargado ✔");

    const form = document.getElementById("registerForm");

    if (form) { // si no estamos en register.html, no ejecutar nada

    // Inputs
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const repeatPassword = document.getElementById("repeatPassword");

    // Mensajes
    const emailHelp = document.getElementById("emailHelp");
    const errorMessage = document.getElementById("errorMessage");

    // Regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/;

    // Email en tiempo real
    if (email) {
      email.addEventListener("input", () => {
        if (!emailRegex.test(email.value)) {
          email.classList.add("is-invalid");
        } else {        
          email.classList.remove("is-invalid");
          email.classList.add("is-valid");
       }
      });
    }

    // Contraseña en tiempo real
    if (password) {
      password.addEventListener("input", () => {
        if (!passRegex.test(password.value)) {
          password.classList.add("is-invalid");
        } else {
          password.classList.remove("is-invalid");
          password.classList.add("is-valid");
        }
      });
    }

    // Repetir contraseña en tiempo real
    if (repeatPassword) {
        repeatPassword.addEventListener("input", () => {
          if (repeatPassword.value !== password.value) {
            repeatPassword.classList.add("is-invalid");
          } else {
            repeatPassword.classList.remove("is-invalid");
            repeatPassword.classList.add("is-valid");
          }
        });
      }

    // -------- ENVÍO DEL FORMULARIO -------- //

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      errorMessage.style.display = "none";

      // Validaciones finales
      if (!emailRegex.test(email.value)) {
        errorMessage.textContent = "Ingrese un correo válido.";
        errorMessage.style.display = "block";
        return;
      }

      if (!passRegex.test(password.value)) {
        errorMessage.textContent = "La contraseña no cumple los requisitos.";
        errorMessage.style.display = "block";
        return;
      }

      if (password.value !== repeatPassword.value) {
        errorMessage.textContent = "Las contraseñas no coinciden.";
        errorMessage.style.display = "block";
        return;
      }

      // Crear datos para enviar a PHP
      let datos = new FormData(form);

      // Enviar a PHP vía fetch
      fetch("guardarRegister.php", {
        method: "POST",
        body: datos
      })
        .then(res => res.text())
        .then(respuesta => {
          respuesta = respuesta.trim();

          console.log("Respuesta del servidor:", respuesta);

           switch(respuesta) {
                case "OK":
                    // Redirigir al login con mensaje
                    window.location.href = "login.html?registro=ok";
                    break;
                case "EXISTE":
                    alert("Este correo ya está registrado.");
                    break;
                case "FALTAN_DATOS":
                    alert("Por favor completa todos los campos.");
                    break;
                default:
                    alert("Error en la base de datos: " + respuesta);
            }
        })
        .catch(error => {
            console.error("Error en la petición:", error);
            alert("Ocurrió un error al conectar con el servidor.");
        });

    });

    const user = localStorage.getItem("userName");

    const loginLink = document.getElementById("loginLink");
    const userBox = document.getElementById("userBox");
    const logoutLink = document.getElementById("logoutLink");
    const userName = document.getElementById("userName");
    const logoutBtn = document.getElementById("logoutBtn");

    if (user) {
        // Usuario logueado
        if (loginLink) loginLink.classList.add("d-none");
        if (userBox) userBox.classList.remove("d-none");
        if (logoutLink) logoutLink.classList.remove("d-none");
        if (userName) userName.textContent = "Hola, " + user;
    }

// Cerrar sesión
if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
        localStorage.removeItem("userName");
        window.location.reload(); // recarga la página para volver a estado sin login
    });
}


  }

    /* ============================================================
       =======================   LOGIN   ============================
       ============================================================ */

  const loginForm = document.getElementById("loginForm");

  if (loginForm) {
    
    loginForm.addEventListener("submit", function(e) {
        e.preventDefault();

        let datos = new FormData(loginForm);

        fetch("login.php", {
            method: "POST",
            body: datos
        })
        .then(res => res.text())
        .then(r => {
            r = r.trim();
            console.log("Respuesta login:", r);

            if (r.startsWith("Ok|")) {
              const nombre = r.split("|")[1];
              localStorage.setItem("userName", nombre);

              window.location.href = "index.html";
              return;
            }

            switch(r) {
                case "OK":
                    window.location.href = "index.html";
                    break;
                case "NO_EXISTE":
                    alert("El correo no está registrado.");
                    break;
                case "INCORRECTO":
                    alert("Contraseña incorrecta.");
                    break;
                case "FALTAN_DATOS":
                    alert("Completa todos los campos.");
                    break;
                default:
                    alert("Error: " + r);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error de conexión.");
        });

    });
  }

});



})(window.jQuery);

