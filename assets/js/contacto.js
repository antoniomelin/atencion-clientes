document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contact-form");
    const messageDiv = document.getElementById("form-message");

    form.addEventListener("submit", async (event) => {
        event.preventDefault(); // Evita el envío tradicional del formulario

        // Recopila los datos del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validación adicional
        const validationError = validateFields(data);
        if (validationError) {
            displayMessage(validationError, "error");
            return;
        }

        try {
            // Envía los datos al servidor
            const response = await fetch("/api/contact", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });

            if (response.ok) {
                displayMessage("¡Formulario enviado con éxito!", "success");
                form.reset();
            } else {
                const error = await response.json();
                displayMessage(error.message || "Ocurrió un error al enviar el formulario.", "error");
            }
        } catch (error) {
            displayMessage("No se pudo enviar el formulario. Inténtalo más tarde.", "error");
        }
    });

    function validateFields(data) {
        if (!data.nombre || data.nombre.trim() === "") {
            return "El campo 'Nombre' es obligatorio.";
        }
        if (!data.apellido1 || data.apellido1.trim() === "") {
            return "El campo 'Apellido 1' es obligatorio.";
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
        return null; // Sin errores
    }

    function displayMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.style.display = "block";
        messageDiv.style.color = type === "success" ? "green" : "red";
    }

    function isValidRUT(rut) {
        // Elimina puntos y convierte todo a mayúsculas
        const cleanRUT = rut.replace(/\./g, "").toUpperCase();
    
        // Divide el RUT en número y dígito verificador
        const match = cleanRUT.match(/^(\d+)-([\dkK])$/);
        if (!match) {
            return false; // Formato inválido
        }
    
        const cuerpo = match[1]; // Números del RUT
        const dv = match[2]; // Dígito verificador
    
        // Validación de largo del cuerpo
        if (cuerpo.length < 7 || cuerpo.length > 8) {
            return false;
        }
    
        // Calcula el dígito verificador esperado usando módulo 11
        let suma = 0;
        let multiplicador = 2;
    
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo[i]) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1; // Rota entre 2 y 7
        }
    
        const resto = 11 - (suma % 11);
        const dvEsperado = resto === 11 ? "0" : resto === 10 ? "K" : resto.toString();
    
        // Verifica si el dígito verificador es correcto
        return dv === dvEsperado;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
