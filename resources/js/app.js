import "./bootstrap";
import "./pwa-install";

import Alpine from "alpinejs";

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then((registration) => {
                console.log(
                    "Service Worker berhasil didaftarkan:",
                    registration,
                );
            })
            .catch((error) => {
                console.error("Gagal mendaftarkan Service Worker:", error);
            });
    });
}
window.Alpine = Alpine;

Alpine.start();
