document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("sugerencia-form");
  const overlay = document.getElementById("overlay");

  form.addEventListener("submit", async (event) => {
      event.preventDefault(); // Evita el envío tradicional del formulario

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      const submitButton = form.querySelector('button[type="submit"]');

      // Validación adicional
      const validationError = validateFields(data);
      if (validationError) {
          displayMessage(validationError, "error");
          return;
      }

      try {
        // Muestra el loader y deshabilita el botón de enviar
        overlay.style.display = "flex";
        submitButton.disabled = true;
    
        // Envía los datos al servidor
        const response = await fetch("/api/sugerencias.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        });
    
        // Oculta el loader y habilita el botón de enviar
        overlay.style.display = "none";
        submitButton.disabled = false;
    
        if (response.ok) {
            try {
                const result = await response.json();
                displayMessage(
                    `¡Formulario enviado con éxito! Tu sugerencia ha sido registrada con el código: <strong>${result.codigo_seguimiento}</strong>`,
                    "success"
                );
                form.reset();
            } catch (jsonError) {
              const errorText = await response.text();
              console.error("Error al parsear JSON:", errorText);
              throw new Error("Respuesta inesperada del servidor.");
            }
        } else {
            const errorText = await response.text();
            try {
                const error = JSON.parse(errorText);
                const errorMessage = error.message || "Ocurrió un error al enviar el formulario.";
                const errorDetail = error.error ? `<br><small>${error.error}</small>` : "";
                displayMessage(`${errorMessage}${errorDetail}`, "error");
            } catch (parseError) {
                displayMessage("Error inesperado: El servidor devolvió una respuesta no válida.", "error");
                console.error("Error al parsear JSON:", errorText);
            }
        }
    } catch (error) {
        // Oculta el overlay y habilita el botón de enviar en caso de error inesperado
        overlay.style.display = "none";
        submitButton.disabled = false;
    
        // Maneja errores de red o excepciones
        displayMessage("No se pudo enviar el formulario. Inténtalo más tarde.", "error");
        console.error("Error inesperado:", error);
    }
  });

  function validateFields(data) {
      if (!data.nombre) return "El campo Nombre es obligatorio.";
      if (!data.apellidos) return "El campo Apellidos es obligatorio.";
      if (!data.rut) return "El campo RUT es obligatorio.";
      if (!data.email) return "El campo Email es obligatorio.";
      if (!data.telefono) return "El campo Teléfono es obligatorio.";
      if (!data.motivo) return "El campo Motivo es obligatorio.";
      return null;
  }

  function displayMessage(message, type) {
      const messageDiv = document.getElementById("form-message") || createMessageDiv();
      if (type === "success") {
          form.style.display = "none";
          messageDiv.innerHTML = `<p>${message}</p>`;
          messageDiv.style.color = "green";
          messageDiv.style.display = "block";
      } else {
          messageDiv.innerHTML = `<p>${message}</p>`;
          messageDiv.style.color = "red";
          messageDiv.style.display = "block";
      }
  }

  function createMessageDiv() {
      const div = document.createElement("div");
      div.id = "form-message";
      div.style.marginBottom = "1em";
      form.insertBefore(div, form.firstChild);
      return div;
  }
});
