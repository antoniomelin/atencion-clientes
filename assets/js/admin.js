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

  // Filtrar por rango de fechas
  [startDateInput, endDateInput].forEach((input) => {
    input.addEventListener("change", () => {
      const startDate = new Date(startDateInput.value);
      const endDate = new Date(endDateInput.value);

      interactionItems.forEach((item) => {
        const itemDate = new Date(item.getAttribute("data-date")); // Asegúrate de que los datos tengan un atributo `data-date`
        if (
          (!startDate || itemDate >= startDate) &&
          (!endDate || itemDate <= endDate)
        ) {
          item.style.display = "flex";
        } else {
          item.style.display = "none";
        }
      });
    });
  });
  
});
