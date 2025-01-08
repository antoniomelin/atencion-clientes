document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contact-form");
    const messageDiv = document.getElementById("form-message");

    form.addEventListener("submit", async (event) => {
        event.preventDefault(); // Evita el envío tradicional del formulario

        // Recopila los datos del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const loader = document.getElementById("loader");
        const submitButton = form.querySelector('button[type="submit"]');

        // Validación adicional
        const validationError = validateFields(data);
        if (validationError) {
            displayMessage(validationError, "error");
            return;
        }

        try {
            // Muestra el loader y deshabilita el botón de enviar
            loader.style.display = "block";
            submitButton.disabled = true;
            // Envía los datos al servidor
            const response = await fetch("/api/contact.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });

            // Oculta el loader
            loader.style.display = "none";
            submitButton.disabled = false;

            if (response.ok) {
                const result = await response.json();
    
                // Muestra el código de seguimiento al usuario
                displayMessage(
                    `¡Formulario enviado con éxito! Tu código de seguimiento es: <strong>${result.codigo_seguimiento}</strong>`,
                    "success"
                );
    
                // Limpia el formulario
                form.reset();
            } else {
                const error = await response.json();    
                const errorMessage = error.message || "Ocurrió un error al enviar el formulario.";
                const errorDetail = error.error ? `<br><small>${error.error}</small>` : "";
                displayMessage(`${errorMessage}${errorDetail}`, "error");
                }
        } catch (error) {
            // Oculta el loader
            loader.style.display = "none";
            submitButton.disabled = false;
            
            displayMessage("No se pudo enviar el formulario. Inténtalo más tarde.", "error");
        }
    });

    function validateFields(data) {
        if (!data.nombre || data.nombre.trim() === "") {
            return "El campo 'Nombre' es obligatorio.";
        }
        if (!data.apellidos || data.apellidos.trim() === "") {
            return "El campo 'Apellidos' es obligatorio.";
        }
        if (!data.email || !isValidEmail(data.email)) {
            return "Por favor, ingrese un correo electrónico válido.";
        }
        if (!data.telefono || isNaN(data.telefono)) {
            return "El campo 'Teléfono' debe contener solo números.";
        }
        if (!isValidRUT(data.rut)) {
            return "Por favor, ingrese un RUT válido.";
        }
        if (!data.canal || data.canal === "") {
            return "Por favor, seleccione al menos un canal.";
        }
        return null; // Sin errores
    }

    function displayMessage(message, type) {
        const form = document.getElementById("contact-form");
        const messageCard = document.getElementById("message-card");
        const cardMessage = document.getElementById("card-message");
    
        if (type === "success") {
            // Oculta el formulario en caso de éxito
            form.style.display = "none";
    
            // Muestra la tarjeta con el mensaje de éxito
            cardMessage.innerHTML = message;
            messageCard.style.display = "flex"; // Mostrar la tarjeta
            cardMessage.style.color = "green"; // Color para éxito
        } else {
            // Muestra el mensaje de error en el formulario sin ocultarlo
            const formMessage = document.getElementById("form-message");
            formMessage.innerHTML = message;
            formMessage.style.display = "block";
            formMessage.style.color = "red"; // Color para error
        }
    }

    function isValidRUT(rut) {
        // Limpia el RUT y agrega el guion si es necesario
        let cleanRUT = rut.replace(/\./g, "").replace(/\s+/g, "").toUpperCase();
        if (!cleanRUT.includes("-")) {
            const cuerpo = cleanRUT.slice(0, -1);
            const dv = cleanRUT.slice(-1);
            cleanRUT = `${cuerpo}-${dv}`;
        }

        // Valida formato del RUT
        const match = cleanRUT.match(/^(\d+)-([\dkK])$/);
        if (!match) {
            return false;
        }

        const cuerpo = match[1];
        const dv = match[2];

        if (cuerpo.length < 7 || cuerpo.length > 8) {
            return false;
        }

        // Cálculo del dígito verificador
        let suma = 0;
        let multiplicador = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo[i]) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }

        const resto = 11 - (suma % 11);
        const dvEsperado = resto === 11 ? "0" : resto === 10 ? "K" : resto.toString();

        return dv === dvEsperado;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
