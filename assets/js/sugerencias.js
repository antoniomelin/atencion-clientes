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
            const result = await response.json();

            // Muestra el código de seguimiento al usuario
            displayMessage(
                `¡Formulario enviado con éxito! Tu sugerencia ha sido registrada con el código: <strong>${result.codigo_seguimiento}</strong>`,
                "success"
            );
            form.reset();
        } else {
            const error = await response.json();    
            const errorMessage = error.message || "Ocurrió un error al enviar el formulario.";
            const errorDetail = error.error ? `<br><small>${error.error}</small>` : "";
            displayMessage(`${errorMessage}${errorDetail}`, "error");
            }
      } catch (error) {
          // Oculta el overlay y habilita el botón de enviar en caso de error inesperado
          overlay.style.display = "none";
          submitButton.disabled = false;
               
          const errorMessage = error.message || "Ocurrió un error al enviar el formulario.";
          const errorDetail = error.error ? `<br><small>${error.error}</small>` : "";
          displayMessage(`${errorMessage}${errorDetail}`, "error");
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
    const form = document.getElementById("sugerencia-form");
    const messageCard = document.getElementById("message-card");
    const cardMessage = document.getElementById("card-message");
    const volverButton = document.getElementById("volver-button");

    if (type === "success") {
        // Oculta el formulario en caso de éxito
        form.style.display = "none";

        // Muestra la tarjeta con el mensaje de éxito
        cardMessage.innerHTML = message;
        messageCard.style.display = "flex"; // Mostrar la tarjeta
        cardMessage.style.color = "green"; // Color para éxito

        volverButton.style.display = "inline-block";
    } else {
        // Muestra el mensaje de error en el formulario sin ocultarlo
        const formMessage = document.getElementById("form-message");
        formMessage.innerHTML = message;
        formMessage.style.display = "block";
        formMessage.style.color = "red"; // Color para error
        volverButton.style.display = "inline-block";
    }
}
});