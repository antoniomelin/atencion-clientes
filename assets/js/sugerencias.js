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

          // Oculta el loader
          overlay.style.display = "none";
          submitButton.disabled = false;

          if (response.ok) {
              const result = await response.json();
              displayMessage(
                  `¡Formulario enviado con éxito! Tu sugerencia ha sido registrada.`,
                  "success"
              );
              form.reset();
          } else {
              const error = await response.json();
              const errorMessage = error.message || "Ocurrió un error al enviar el formulario.";
              displayMessage(errorMessage, "error");
          }
      } catch (error) {
          overlay.style.display = "none";
          submitButton.disabled = false;
          displayMessage("No se pudo enviar el formulario. Inténtalo más tarde.", "error");
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
