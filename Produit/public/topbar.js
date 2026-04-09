(() => {
  const timeEl = document.getElementById("time");

  function updateTime() {
    if (!timeEl) {
      return;
    }
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    timeEl.textContent = `${hours}:${minutes}`;
  }

  updateTime();

  if (window.__topbarTimeInterval) {
    clearInterval(window.__topbarTimeInterval);
  }
  window.__topbarTimeInterval = setInterval(updateTime, 1000);

  document.addEventListener("click", (event) => {
    const link = event.target.closest("[data-account-link]");
    if (!link) {
      return;
    }
    if (window.__accountNavInProgress) {
      event.preventDefault();
      return;
    }
    const href = link.getAttribute("href");
    if (!href) {
      return;
    }
    if (typeof window.animateAccountCamera !== "function") {
      return;
    }
    event.preventDefault();
    window.__accountNavInProgress = true;
    const result = window.animateAccountCamera();
    if (result && typeof result.then === "function") {
      result.finally(() => {
        window.location.href = href;
      });
    } else {
      setTimeout(() => {
        window.location.href = href;
      }, 650);
    }
  });

  document.addEventListener("click", (event) => {
    const link = event.target.closest("[data-command-link]");
    if (!link) {
      return;
    }
    if (window.__commandNavInProgress) {
      event.preventDefault();
      return;
    }
    const href = link.getAttribute("href");
    if (!href) {
      return;
    }
    if (typeof window.animateCommandCamera !== "function") {
      return;
    }
    event.preventDefault();
    window.__commandNavInProgress = true;
    const result = window.animateCommandCamera();
    if (result && typeof result.then === "function") {
      result.finally(() => {
        window.location.href = href;
      });
    } else {
      setTimeout(() => {
        window.location.href = href;
      }, 800);
    }
  });
})();
