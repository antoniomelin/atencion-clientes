document.addEventListener("DOMContentLoaded", () => {
  const interactionItems = document.querySelectorAll(".interaction-item");
  const filterButtons    = document.querySelectorAll(".filter-btn");
  const startDateInput   = document.getElementById("start-date");
  const endDateInput     = document.getElementById("end-date");

  interactionItems.forEach(item => {
    item.addEventListener("click", (event) => {
      const detailsContent = item.querySelector(".details-content");

      if (item.classList.contains("clicked")) {
        // Si ya está expandido, colapsar el elemento
        detailsContent.style.display = "none";
        item.classList.remove("clicked");
      } else {
        // Contraer todos los demás elementos
        interactionItems.forEach(otherItem => {
          const otherDetails = otherItem.querySelector(".details-content");
          if (otherDetails) {
            otherDetails.style.display = "none";
            otherItem.classList.remove("clicked");
          }
        });

        // Expandir el elemento actual
        detailsContent.style.display = "block";
        item.classList.add("clicked");
      }

      // Detener la propagación del evento para evitar conflictos con otros listeners
      event.stopPropagation();
    });
  });

  // Cerrar todos los detalles al hacer clic fuera de la lista
  document.addEventListener("click", () => {
    interactionItems.forEach(item => {
      const detailsContent = item.querySelector(".details-content");
      if (detailsContent) {
        detailsContent.style.display = "none";
        item.classList.remove("clicked");
      }
    });
  });

  // Filtrar por estado
  filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const filter = button.getAttribute("data-filter");
      interactionItems.forEach((item) => {
        if (item.classList.contains(filter)) {
          item.style.display = "flex";
        } else {
          item.style.display = "none";
        }
      });
    });
  });
  
});
// Inicializar Flatpickr para el rango de fechas al hacer clic en el ícono
document.addEventListener("DOMContentLoaded", function () {
  const datePickerButton = document.getElementById("date-picker-button");

  // Configurar Flatpickr
  const flatpickrInstance = flatpickr(datePickerButton, {
    mode: "range", // Selección de rango de fechas
    dateFormat: "Y-m-d", // Formato de las fechas (YYYY-MM-DD)
    locale: "es", // Idioma español
    clickOpens: false, // Evita que se abra automáticamente
    onChange: function (selectedDates, dateStr) {
      console.log("Fechas seleccionadas:", dateStr);
      // Agrega aquí lógica para manejar las fechas seleccionadas
    },
  });

  // Mostrar el picker al hacer clic en el botón
  datePickerButton.addEventListener("click", function () {
    flatpickrInstance.open();
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const stateFilter = document.getElementById("state-filter");

  stateFilter.addEventListener("change", function () {
      const selectedValue = stateFilter.value;

      // Filtrar las interacciones según el valor seleccionado
      const interactionItems = document.querySelectorAll(".interaction-item");

      interactionItems.forEach(item => {
        const statusElement = item.querySelector(".interaction-status");
        const statusText = statusElement ? statusElement.textContent.trim() : "";

        // Mostrar si coincide con el filtro o si se selecciona "Todos"
        if (selectedValue === "todos" || statusText === selectedValue) {
          item.style.display = "flex"; // Mostrar
        } else {
          item.style.display = "none"; // Ocultar
        }
      });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // Obtener todos los botones de procesar
  const processButtons = document.querySelectorAll(".process-button");

  processButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const interactionId = button.getAttribute("data-id");

      // Acción al hacer clic en el botón
      console.log(`Procesando interacción con ID: ${interactionId}`);
      
      // Ejemplo de llamada AJAX
      fetch(`/api/procesar.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: interactionId }),
      })
        .then((response) => response.text()) // Leer como texto primero
        .then((text) => {
          try {
            const data = JSON.parse(text); // Intentar convertir a JSON
            console.log("Datos procesados:", data);
            if (data.success) {
              alert("Interacción procesada con éxito");
              button.disabled = true;
              button.textContent = "Procesado";
            } else {
              alert(`Error: ${data.error}`);
            }
          } catch (e) {
            console.error("Respuesta no válida (no es JSON):", text);
            alert("Error inesperado en el servidor");
          }
        })
        .catch((error) => console.error("Error en fetch:", error));
      
      
    });
  });
});
