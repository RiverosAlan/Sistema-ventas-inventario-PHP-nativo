if (typeof claveEsFuerte === 'undefined') {
    var claveEsFuerte = false;
}

console.log("[Validador] Archivo .js cargado correctamente en la página.");

// Función para conmutar la visibilidad de la contraseña
function conmutarOjo(idInput, boton) {
    const input = document.getElementById(idInput);
    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        boton.style.backgroundColor = "#e4e6ef";
    } else {
        input.type = "password";
        boton.style.backgroundColor = "";
    }
}

// Función genérica para validar entropía de contraseñas
function procesarValidacion(campoInput, contenedorMsg) {
    if (!campoInput || !contenedorMsg) return false;

    const password = campoInput.value;
    const regexFuerte = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&.])[A-Za-z\d@$!%*#?&.]{8,}$/;

    if (password.length === 0) {
        contenedorMsg.innerHTML = "";
        campoInput.className = "form-control";
        return false; 
    }

    if (regexFuerte.test(password)) {
        campoInput.className = "form-control is-valid";
        contenedorMsg.innerHTML = "<span class='text-success'>✅ Contraseña Segura (Cumple con los requisitos)</span>";
        return true;
    } else {
        campoInput.className = "form-control is-invalid";
        contenedorMsg.innerHTML = "<span class='text-danger'>❌ Contraseña Débil. Requiere: Mayúscula, minúscula, número y un símbolo.</span>";
        return false;
    }
}

// Inicializador automático de guardianes para los formularios del sistema
document.addEventListener("DOMContentLoaded", function() {
    
    // GUARDIÁN 1: EL MODAL DEL PERFIL (HEADER.PHP)
    const modalPass = document.getElementById('modal_password_field');
    const modalMsg = document.getElementById('password-strength-msg');
    if (modalPass && modalMsg) {
        procesarValidacion(modalPass, modalMsg);
        modalPass.addEventListener('keyup', () => { claveEsFuerte = procesarValidacion(modalPass, modalMsg); });
        modalPass.addEventListener('change', () => { claveEsFuerte = procesarValidacion(modalPass, modalMsg); });
        const formModal = modalPass.closest('form');
        if (formModal) {
            formModal.addEventListener('submit', function(e) {
                if (!procesarValidacion(modalPass, modalMsg)) { e.preventDefault(); alert("La contraseña del modal no cumple con los requisitos de seguridad."); }
            });
        }
    }

    // GUARDIÁN 2: PANTALLA DE EDICIÓN (EDIT.PHP)
    const editPass = document.getElementById('edit_password_field');
    const editMsg = document.getElementById('edit-password-strength-msg');
    if (editPass && editMsg) {
        procesarValidacion(editPass, editMsg);
        editPass.addEventListener('keyup', () => { claveEsFuerte = procesarValidacion(editPass, editMsg); });
        editPass.addEventListener('change', () => { claveEsFuerte = procesarValidacion(editPass, editMsg); });
        const formEdit = editPass.closest('form');
        if (formEdit) {
            formEdit.addEventListener('submit', function(e) {
                if (!procesarValidacion(editPass, editMsg)) { e.preventDefault(); alert("La contraseña de actualización no cumple con los requisitos de seguridad."); }
            });
        }
    }

    // GUARDIÁN 3: PANTALLA DE CREACIÓN (FORMULARIO NUEVO)
    const createPass = document.getElementById('create_password_field');
    const createMsg = document.getElementById('create-password-strength-msg');
    if (createPass && createMsg) {
        console.log("[Validador] Guardián activado para el formulario de Creación.");
        procesarValidacion(createPass, createMsg);
        createPass.addEventListener('keyup', () => { claveEsFuerte = procesarValidacion(createPass, createMsg); });
        createPass.addEventListener('change', () => { claveEsFuerte = procesarValidacion(createPass, createMsg); });
        const formCreate = createPass.closest('form');
        if (formCreate) {
            formCreate.addEventListener('submit', function(e) {
                if (!procesarValidacion(createPass, createMsg)) { 
                    e.preventDefault(); 
                    alert("No puedes registrar el usuario: La contraseña ingresada no cumple con las directivas de entropía."); 
                }
            });
        }
    }
});


// Añade este bloque al final de la función DOMContentLoaded dentro de validador_password.js
const forzadoPass = document.getElementById('modal_password_field' ? null : 'modal_forzado_field');
const forzadoMsg = document.getElementById('forzado-password-strength-msg');

if (forzadoPass && forzadoMsg) {
    procesarValidacion(forzadoPass, forzadoMsg);
    forzadoPass.addEventListener('keyup', () => { claveEsFuerte = procesarValidacion(forzadoPass, forzadoMsg); });
    forzadoPass.addEventListener('change', () => { claveEsFuerte = procesarValidacion(forzadoPass, forzadoMsg); });

    const formForzado = forzadoPass.closest('form');
    if (formForzado) {
        formForzado.addEventListener('submit', function(e) {
            if (!procesarValidacion(forzadoPass, forzadoMsg)) {
                e.preventDefault();
                alert("La contraseña no cumple con los requisitos mínimos de entropía.");
            }
        });
    }
}
