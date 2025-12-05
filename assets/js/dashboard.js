document.addEventListener("DOMContentLoaded", function () {
  // Animation des statistiques
  const statsItems = document.querySelectorAll(".stats-item");
  statsItems.forEach((item, index) => {
    item.style.opacity = "0";
    item.style.transform = "translateX(20px)";

    setTimeout(() => {
      item.style.opacity = "1";
      item.style.transform = "translateX(0)";
    }, 100 * index);
  });

  // Effet de survol pour les cartes
  const cards = document.querySelectorAll(".card");
  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)";
      this.style.boxShadow = "0 10px 20px rgba(0, 0, 0, 0.1)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
      this.style.boxShadow = "0 5px 15px rgba(0, 0, 0, 0.05)";
    });
  });

  // Effet de survol pour les éléments de liste
  const listItems = document.querySelectorAll(".list-group-item");
  listItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      if (!this.classList.contains("list-group-item-action")) return;
      this.style.backgroundColor = "rgba(23, 116, 223, 0.05)";
    });

    item.addEventListener("mouseleave", function () {
      if (!this.classList.contains("list-group-item-action")) return;
      this.style.backgroundColor = "";
    });
  });
});
