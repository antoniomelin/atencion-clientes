document.addEventListener("DOMContentLoaded", () => {
  const detailButtons = document.querySelectorAll(".toggle-details");

  detailButtons.forEach(button => {
      button.addEventListener("click", () => {
          const detailsContent = button.nextElementSibling; // Elemento .details-content justo después del botón
          if (detailsContent.style.display === "none" || !detailsContent.style.display) {
              detailsContent.style.display = "block";
              button.textContent = "Ocultar";
          } else {
              detailsContent.style.display = "none";
              button.textContent = "Detalles";
          }
      });
  });
});
