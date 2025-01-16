document.addEventListener("DOMContentLoaded", () => {
  const interactionItems = document.querySelectorAll(".interaction-item");
  const filterButtons = document.querySelectorAll(".filter-btn");
  const startDateInput = document.getElementById("start-date");
  const endDateInput = document.getElementById("end-date");

  // Abrir y cerrar detalles de interacción
  interactionItems.forEach((item) => {
    item.addEventListener("click", (event) => {
      const detailsContent = item.querySelector(".details-content");

      if (item.classList.contains("clicked")) {
        detailsContent.style.display = "none";
        item.classList.remove("clicked");
      } else {
        interactionItems.forEach((otherItem) => {
          const otherDetails = otherItem.querySelector(".details-content");
          if (otherDetails) {
            otherDetails.style.display = "none";
            otherItem.classList.remove("clicked");
          }
        });
        detailsContent.style.display = "block";
        item.classList.add("clicked");
      }
      event.stopPropagation();
    });
  });

  // Cerrar detalles al hacer clic fuera de la lista
  document.addEventListener("click", () => {
    interactionItems.forEach((item) => {
      const detailsContent = item.querySelector(".details-content");
      if (detailsContent) {
        detailsContent.style.display = "none";
        item.classList.remove("clicked");
      }
    });
  });

  // Filtrar interacciones por estado
  const stateFilter = document.getElementById("state-filter");
  if (stateFilter) {
    stateFilter.addEventListener("change", () => {
      const selectedValue = stateFilter.value;

      interactionItems.forEach((item) => {
        const statusElement = item.querySelector(".interaction-status");
        const statusText = statusElement ? statusElement.textContent.trim() : "";

        if (selectedValue === "todos" || statusText === selectedValue) {
          item.style.display = "flex";
        } else {
          item.style.display = "none";
        }
      });
    });
  }

  // Inicializar Flatpickr
  const datePickerButton = document.getElementById("date-picker-button");
  if (datePickerButton) {
    const flatpickrInstance = flatpickr(datePickerButton, {
      mode: "range",
      dateFormat: "Y-m-d",
      locale: "es",
      clickOpens: false,
    });

    datePickerButton.addEventListener("click", function () {
      flatpickrInstance.open();
    });
  }

  // Procesar interacción (botón "En Proceso")
  const processButtons = document.querySelectorAll(".process-button");
  processButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const interactionId = button.getAttribute("data-id");

      fetch(`/api/procesar.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: interactionId, action: "en_proceso" }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            window.location.reload();
          } else {
            alert(`Error: ${data.error}`);
          }
        })
        .catch((error) => console.error("Error en fetch:", error));
    });
  });

  // Resolver interacción con confirmación
  const resolveButtons = document.querySelectorAll(".resolve-button");
  resolveButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const interactionId = button.getAttribute("data-id");
      const userConfirmed = confirm(
        "¿Está seguro de resolver?, se enviará correo indicando resolución del caso"
      );

      if (userConfirmed) {
        fetch(`/api/procesar.php`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ id: interactionId, action: "resuelto" }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(`Interacción ${interactionId} marcada como resuelta.`);
              button.parentElement.parentElement.remove();
            } else {
              alert(`Error: ${data.error}`);
            }
          })
          .catch((error) => console.error("Error en fetch:", error));
      }
    });
  });

  // Modal para "Responder"
  const emailModal = document.getElementById("emailModal");
  const emailRecipient = document.getElementById("emailRecipient");
  const emailMessage = document.getElementById("emailMessage");
  const closeModal = document.getElementById("closeModal");
  const sendEmailButton = document.getElementById("sendEmailButton");
  const cancelEmailButton = document.getElementById("cancelEmailButton");

  const respondButtons = document.querySelectorAll(".respond-button");
  respondButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const interactionId = button.getAttribute("data-id");
      const recipientEmail = button.getAttribute("data-email");

      emailRecipient.textContent = recipientEmail; // Mostrar destinatario
      emailMessage.value = ""; // Limpiar mensaje previo
      emailModal.style.display = "block"; // Mostrar modal

      // Enviar correo
      sendEmailButton.onclick = () => {
        const message = emailMessage.value.trim();
        if (!message) {
          alert("El mensaje no puede estar vacío.");
          return;
        }

        fetch(`/api/mailer.php`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ id: interactionId, email: recipientEmail, message }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert("Correo enviado con éxito.");
              emailModal.style.display = "none";
            } else {
              alert(`Error al enviar el correo: ${data.error}`);
            }
          })
          .catch((error) => console.error("Error al enviar el correo:", error));
      };
    });
  });

  // Cerrar modal
  closeModal.onclick = cancelEmailButton.onclick = () => {
    emailModal.style.display = "none";
  };
});
