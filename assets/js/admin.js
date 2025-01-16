document.addEventListener("DOMContentLoaded", () => {
  const emailModal = document.getElementById("emailModal");
  const emailRecipient = document.getElementById("emailRecipient");
  const emailMessage = document.getElementById("emailMessage");
  const closeModal = document.getElementById("closeModal");
  const sendEmailButton = document.getElementById("sendEmailButton");
  const cancelEmailButton = document.getElementById("cancelEmailButton");

  const interactionItems = document.querySelectorAll(".interaction-item");
  const processButtons = document.querySelectorAll(".process-button");
  const filterButtons = document.querySelectorAll(".filter-btn");

  // Abrir el modal "Responder"
  const respondButtons = document.querySelectorAll(".respond-button");
  respondButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const interactionId = button.getAttribute("data-id");
      const recipientEmail = button.getAttribute("data-email");

      // Mostrar el destinatario y limpiar el mensaje previo
      emailRecipient.textContent = recipientEmail;
      emailMessage.value = "";
      emailModal.style.display = "block";

      // Acción del botón "Enviar"
      sendEmailButton.onclick = () => {
        const message = emailMessage.value.trim();
        if (!message) {
          alert("El mensaje no puede estar vacío.");
          return;
        }

        // Enviar el mensaje al backend
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
              emailModal.style.display = "none"; // Cerrar el modal
            } else {
              alert(`Error al enviar el correo: ${data.error}`);
            }
          })
          .catch((error) => console.error("Error al enviar el correo:", error));
      };
    });
  });

  // Cerrar el modal
  closeModal.onclick = cancelEmailButton.onclick = () => {
    emailModal.style.display = "none";
  };

  // Manejo de botones "Procesar"
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

  // Botones "Resolver"
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

  // Filtros de estado
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

  // Manejo de Flatpickr
  const datePickerButton = document.getElementById("date-picker-button");
  if (datePickerButton) {
    flatpickr(datePickerButton, {
      mode: "range",
      dateFormat: "Y-m-d",
      locale: "es",
      clickOpens: false,
    });
    datePickerButton.addEventListener("click", function () {
      flatpickrInstance.open();
    });
  }
});
