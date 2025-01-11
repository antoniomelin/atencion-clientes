document.addEventListener("DOMContentLoaded", () => {
  const trackingButton = document.querySelector(".tracking-button");
  const trackingInput = document.querySelector(".tracking-input");
  const trackingResult = document.getElementById("tracking-result");

  trackingButton.addEventListener("click", async () => {
      const trackingCode = trackingInput.value.trim();

      // Validación del código de seguimiento
      if (!trackingCode) {
          showTrackingResult("Por favor, ingresa un código de seguimiento.", "red");
          return;
      }

      if (trackingCode.length !== 6 || !/^[A-Za-z0-9]{6}$/.test(trackingCode)) {
          showTrackingResult("El código debe tener 6 caracteres alfanuméricos (ej: A98F46).", "red");
          return;
      }

      // Limpia resultados anteriores y muestra mensaje de carga
      showTrackingResult("Buscando...", "black");

      try {
          // Llama al servidor para buscar el código
          const response = await fetch(`/api/track.php?code=${encodeURIComponent(trackingCode)}`);
          if (response.ok) {
              const result = await response.json();
              console.log(result)
              showTrackingResult(
                  `Estado: <strong>${result.estado}</strong>. <br> Desde: ${result.fecha}`,
                  "green"
              );
          } else {
              const errorText = await response.text();
              console.error("Error de seguimiento:", errorText);
              showTrackingResult("No se encontró ningún seguimiento para este código.", "red");
          }
      } catch (error) {
          showTrackingResult("Error al buscar el código de seguimiento. Inténtalo más tarde.", "red");
          console.error("Error en la búsqueda de seguimiento:", error);
      }
  });
  
  // Escucha el evento de entrada para transformar el valor a mayúsculas
  trackingInput.addEventListener("input", () => {
    trackingInput.value = trackingInput.value.toUpperCase();
  });
  // Función para mostrar resultados en el contenedor
  function showTrackingResult(message, color) {
      trackingResult.innerHTML = message;
      trackingResult.style.color = color;
      trackingResult.style.display = "block";
  }
});
