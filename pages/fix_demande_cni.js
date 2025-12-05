document.addEventListener("DOMContentLoaded", function () {
  // Correction pour la sélection du type de demande
  const typeCards = document.querySelectorAll(".form-check.card");
  const typeRadios = document.querySelectorAll('input[name="type_demande"]');

  // S'assurer que les cartes sont cliquables
  typeCards.forEach((card) => {
    card.addEventListener("click", function (e) {
      // Trouver le radio à l'intérieur de cette carte
      const radio = this.querySelector('input[type="radio"]');
      if (radio) {
        // Cocher le radio
        radio.checked = true;

        // Mettre à jour visuellement toutes les cartes
        typeCards.forEach((c) => {
          if (c.contains(radio)) {
            c.classList.add("selected-card");
            c.style.borderColor = "#1774df";
            c.style.backgroundColor = "rgba(23, 116, 223, 0.1)";
          } else {
            c.classList.remove("selected-card");
            c.style.borderColor = "#e9ecef";
            c.style.backgroundColor = "";
          }
        });

        // Déclencher l'événement change manuellement
        const event = new Event("change");
        radio.dispatchEvent(event);
      }
    });
  });

  // Correction pour les radios directement
  typeRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      const selectedType = this.value;
      const numeroContainer = document.querySelector(".numero-cni-container");
      const premiereDoc = document.querySelector(".premiere-doc");
      const renouvellementDoc = document.querySelector(".renouvellement-doc");
      const perteDoc = document.querySelector(".perte-doc");

      // Mettre à jour l'affichage du numéro CNI
      if (selectedType === "renouvellement") {
        numeroContainer.classList.remove("d-none");
      } else {
        numeroContainer.classList.add("d-none");
      }

      // Mettre à jour les documents requis
      premiereDoc.classList.add("d-none");
      renouvellementDoc.classList.add("d-none");
      perteDoc.classList.add("d-none");

      if (selectedType === "premiere") {
        premiereDoc.classList.remove("d-none");
      } else if (selectedType === "renouvellement") {
        renouvellementDoc.classList.remove("d-none");
      } else if (selectedType === "perte") {
        perteDoc.classList.remove("d-none");
      }

      // Mettre à jour visuellement la carte correspondante
      typeCards.forEach((card) => {
        const cardRadio = card.querySelector('input[type="radio"]');
        if (cardRadio && cardRadio.value === selectedType) {
          card.classList.add("selected-card");
          card.style.borderColor = "#1774df";
          card.style.backgroundColor = "rgba(23, 116, 223, 0.1)";
        } else {
          card.classList.remove("selected-card");
          card.style.borderColor = "#e9ecef";
          card.style.backgroundColor = "";
        }
      });
    });
  });
});
