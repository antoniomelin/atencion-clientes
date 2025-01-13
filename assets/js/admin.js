document.addEventListener("DOMContentLoaded", () => {
  const interactionItems = document.querySelectorAll(".interaction-item");

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
});
