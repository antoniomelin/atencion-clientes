document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("contact-form");
  const messageDiv = document.getElementById("form-message");

  form.addEventListener("submit", async (event) => {
      event.preventDefault(); // Evita el envío tradicional del formulario

      // Recopila los datos del formulario
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      // Validación adicional
      if (!isValidRUT(data.rut)) {
          displayMessage("Por favor, ingrese un RUT válido.", "error");
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

  function displayMessage(message, type) {
      messageDiv.textContent = message;
      messageDiv.style.display = "block";
      messageDiv.style.color = type === "success" ? "green" : "red";
  }

  function isValidRUT(rut) {
      // Lógica de validación de RUT (puedes implementar una función más avanzada aquí)
      return /^[0-9]+-[0-9kK]$/.test(rut);
  }
});
