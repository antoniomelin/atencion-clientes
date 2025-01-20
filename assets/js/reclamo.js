document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("reclamo-form");
  const overlay = document.getElementById("overlay");

  form.addEventListener("submit", async (event) => {
      event.preventDefault(); // Evita el envío tradicional del formulario

      const formData = new FormData(form); // Captura todos los datos del formulario
      const submitButton = form.querySelector('button[type="submit"]');

      // Validación adicional
      const validationError = validateFields(formData);
      if (validationError) {
          displayMessage(validationError, "error");
          return;
      }

      try {
          // Muestra el loader y deshabilita el botón de enviar
          overlay.style.display = "flex";
          submitButton.disabled = true;

        //   const formData = new FormData(form);
        //   for (let pair of formData.entries()) {
        //       console.log(pair[0] + ": " + pair[1]);
        //   }

          // Envía los datos al servidor
          const response = await fetch("/api/reclamo.php", {
              method: "POST",
              body: formData, // FormData ya maneja los archivos
          });

          // Oculta el loader y habilita el botón de enviar
          overlay.style.display = "none";
          submitButton.disabled = false;

          const resultText = await response.text(); // Leer la respuesta como texto
          console.log("Respuesta bruta del servidor:", resultText);

          if (response.ok) {
              const result = await response.json();

              // Muestra el código de seguimiento al usuario
              displayMessage(
                  `¡Formulario enviado con éxito! Tu reclamo ha sido registrado con el código: <strong>${result.codigo_seguimiento}</strong>`,
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

  function validateFields(formData) {
      if (!formData.get("nombre")) return "El campo Nombre es obligatorio.";
      if (!formData.get("apellidos")) return "El campo Apellidos es obligatorio.";
      if (!formData.get("rut")) return "El campo RUT es obligatorio.";
      if (!formData.get("email")) return "El campo Email es obligatorio.";
      if (!formData.get("telefono")) return "El campo Teléfono es obligatorio.";
      if (!formData.get("boleta")) return "El campo Boleta / Factura es obligatorio.";
      if (!formData.get("foto-boleta")) return "La Foto Boleta es obligatoria.";
      if (!formData.get("lugar-compra")) return "El campo Lugar de compra es obligatorio.";
      if (!formData.get("foto-producto")) return "La Foto Producto(s) es obligatoria.";
      return null; // Sin errores
  }

  function displayMessage(message, type) {
      const form = document.getElementById("reclamo-form");
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
