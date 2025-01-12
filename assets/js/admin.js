document.addEventListener("DOMContentLoaded", () => {
  const interactionItems = document.querySelectorAll(".interaction-item");

  interactionItems.forEach(item => {
      item.addEventListener("click", (event) => {
          const detailsContent = item.querySelector(".details-content"); // Busca el .details-content dentro del elemento

          // Alternar la expansión del elemento actual
          if (detailsContent.style.display === "none" || !detailsContent.style.display) {
              // Contraer todos los demás elementos
              interactionItems.forEach(otherItem => {
                  const otherDetails = otherItem.querySelector(".details-content");
                  if (otherDetails && otherDetails !== detailsContent) {
                      otherDetails.style.display = "none";
                      otherItem.classList.remove("expanded");
                  }
              });

              // Expandir el elemento actual
              detailsContent.style.display = "block";
              item.classList.add("expanded");
          } else {
              // Contraer el elemento actual
              detailsContent.style.display = "none";
              item.classList.remove("expanded");
          }

          event.stopPropagation(); // Evitar que el clic cierre el contenido por el listener del documento
      });
  });

  // Cerrar todos los detalles al hacer clic fuera de la lista
  document.addEventListener("click", () => {
      interactionItems.forEach(item => {
          const detailsContent = item.querySelector(".details-content");
          if (detailsContent) {
              detailsContent.style.display = "none";
              item.classList.remove("expanded");
          }
      });
  });
});
