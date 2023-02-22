document.addEventListener("DOMContentLoaded", () => {
  const btnEcossistema = document.querySelector(".btn-ecossistema");
  const ecossistemaAma = document.querySelector(".ecossistema-ama");

  if (btnEcossistema) {
    btnEcossistema.addEventListener("click", () => {
      btnEcossistema.classList.toggle("open");
      if ( btnEcossistema.classList.contains("open")){
        btnEcossistema.ariaExpanded = "true";
      }
      else {
        btnEcossistema.ariaExpanded = "false";
      }
      ecossistemaAma.classList.toggle("show");
      if ( ecossistemaAma.classList.contains("show")){
        ecossistemaAma.ariaHidden = "false";
      }
      else {
        ecossistemaAma.ariaHidden = "true";
      }
      document.body.classList.toggle("no-scroll");
    });
  }

  const openMobileMenuBtn = document.querySelector(".open-mobile-menu");
  const closeMobileMenuBtn = document.querySelector(".close-mobile-menu");
  const mobileOverlay = document.querySelector(".mobile-overlay");

  if (openMobileMenuBtn) {
    openMobileMenuBtn.addEventListener("click", () => {
      mobileOverlay.style.visibility = "visible";
      document.body.classList.add("no-scroll");
    });
    closeMobileMenuBtn.addEventListener("click", () => {
      mobileOverlay.style.visibility = "hidden";
      document.body.classList.remove("no-scroll");
    });
  }

  const darkMode = localStorage.getItem("darkMode");
  const darkModeBtn = document.getElementById("darkModeBtn");
  const darkModeLabel = document.getElementById("darkModeLabel");

  if (darkMode === "true") {
    if (!document.body.classList.contains("dark-theme")) {
      document.body.classList.toggle("dark-theme");
      darkModeLabel.innerHTML = "Modo Claro";
    }
  }

  if (darkModeBtn) {
    darkModeBtn.addEventListener(
      "click",
      () => {
        document.body.classList.toggle("dark-theme");
        if (document.body.classList.contains("dark-theme")) {
          darkModeLabel.innerHTML = "Modo Claro";
          localStorage.setItem("darkMode", true);
        } else {
          darkModeLabel.innerHTML = "Modo Escuro";
          localStorage.setItem("darkMode", false);
        }
      },
      false
    );
  }

  //FACEBOOK
  jQuery("#fb-icon span").mouseover(() => {
    document
      .querySelector("#fb-icon span")
      .classList.remove("icon-share-facebook");
    document
      .querySelector("#fb-icon span")
      .classList.add("icon-share-facebook-over");
  });

  jQuery("#fb-icon span").mouseout(() => {
    document
      .querySelector("#fb-icon span")
      .classList.add("icon-share-facebook");
    document
      .querySelector("#fb-icon span")
      .classList.remove("icon-share-facebook-over");
  });

  //TWITTER
  jQuery("#tw-icon span").mouseover(() => {
    document
      .querySelector("#tw-icon span")
      .classList.remove("icon-share-twitter");
    document
      .querySelector("#tw-icon span")
      .classList.add("icon-share-twitter-over");
  });

  jQuery("#tw-icon span").mouseout(() => {
    document.querySelector("#tw-icon span").classList.add("icon-share-twitter");
    document
      .querySelector("#tw-icon span")
      .classList.remove("icon-share-twitter-over");
  });

  //LINKEDIN
  jQuery("#ld-icon span").mouseover(() => {
    document
      .querySelector("#ld-icon span")
      .classList.remove("icon-share-linkedin");
    document
      .querySelector("#ld-icon span")
      .classList.add("icon-share-linkedin-over");
  });

  jQuery("#ld-icon span").mouseout(() => {
    document
      .querySelector("#ld-icon span")
      .classList.add("icon-share-linkedin");
    document
      .querySelector("#ld-icon span")
      .classList.remove("icon-share-linkedin-over");
  });

  //FACEBOOK
  jQuery("#fb-icon").click((e) => {
    e.preventDefault();
    var local = window.location.href;
    var url = "https://www.facebook.com/sharer/sharer.php?u=" + local;
    window.open(url, "_blank");
  });
  //TWITTER
  jQuery("#tw-icon").click((e) => {
    e.preventDefault();
    var local = window.location.href;
    var url = "https://twitter.com/intent/tweet?url=" + local;
    window.open(url, "_blank");
  });
  //LINKEDIN
  jQuery("#ld-icon").click((e) => {
    e.preventDefault();
    var local = window.location.href;
    var url = "https://www.linkedin.com/shareArticle?mini=true&url=" + local;
    window.open(url, "_blank");
  });
});
