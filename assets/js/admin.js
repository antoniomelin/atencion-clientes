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