let deferredInstallPrompt = null;

if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("./service-worker.js").catch(() => {
      // Registrace service workeru je volitelna; pri chybe zustane web funkcni.
    });
  });
}

window.addEventListener("beforeinstallprompt", (event) => {
  event.preventDefault();
  deferredInstallPrompt = event;

  const installButton = document.getElementById("installAppBtn");
  if (installButton) {
    installButton.style.display = "inline-block";
  }
});

window.addEventListener("appinstalled", () => {
  deferredInstallPrompt = null;
  const installButton = document.getElementById("installAppBtn");
  if (installButton) {
    installButton.style.display = "none";
  }
});

document.addEventListener("click", async (event) => {
  const target = event.target;

  if (!target || target.id !== "installAppBtn" || !deferredInstallPrompt) {
    return;
  }

  deferredInstallPrompt.prompt();
  await deferredInstallPrompt.userChoice;
  deferredInstallPrompt = null;
  target.style.display = "none";
});
