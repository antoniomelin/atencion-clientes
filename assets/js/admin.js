document.addEventListener("DOMContentLoaded", () => {
  const interactionItems = document.querySelectorAll(".interaction-item");

  interactionItems.forEach(item => {
    item.addEventListener("click", () => {
      // Contraer todos los demás elementos
      interactionItems.forEach(otherItem => {
        otherItem.classList.remove("clicked");
        const details = otherItem.querySelector(".details-content");
        if (details) details.style.display = "none";
      });

      // Expandir el elemento actual
      item.classList.toggle("clicked");
      const detailsContent = item.querySelector(".details-content");
      if (detailsContent) {
        detailsContent.style.display = item.classList.contains("clicked") ? "block" : "none";
      }
    });
  });

  // Cerrar todos los detalles al hacer clic fuera de la lista
  document.addEventListener("click", (event) => {
    if (!event.target.closest(".interaction-item")) {
      interactionItems.forEach(item => {
        item.classList.remove("clicked");
        const details = item.querySelector(".details-content");
        if (details) details.style.display = "none";
      });
    }
  });
});
