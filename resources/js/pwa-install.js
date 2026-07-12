const PwaInstallGuide = (() => {
    let deferredPrompt;

    const getUserAgentData = () => {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        const isAndroid = /android/i.test(ua);
        const isIos = /iPad|iPhone|iPod/.test(ua) && !(window.MSStream);
        const isWindows = /Win/.test(ua);
        const isChrome = /Chrome/.test(ua) && /Google Inc/.test(ua);
        const isEdge = /Edg/.test(ua);
        const isSafari = /Safari/.test(ua) && /Apple Computer/.test(ua);

        return { isAndroid, isIos, isWindows, isChrome, isEdge, isSafari };
    };

    const showSection = (id) => {
        const el = document.getElementById(id);
        if (el) el.classList.remove("hidden");
    };

    const hideSection = (id) => {
        const el = document.getElementById(id);
        if (el) el.classList.add("hidden");
    };

    const hideAllSections = () => {
        const sections = [
            "install-btn-container",
            "android-chrome-prompt",
            "android-chrome-manual",
            "windows-edge-prompt",
            "windows-edge-manual",
            "ios-safari",
            "general-browser"
        ];
        sections.forEach(id => hideSection(id));
    };

    const setButtonLoading = (btn, html, bgClass) => {
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML = html;
        if (bgClass) {
            btn.classList.remove("bg-red-600");
            btn.classList.add(bgClass);
        }
    };

    const renderInstructions = () => {
        hideAllSections();

        const { isAndroid, isIos, isWindows, isChrome, isEdge, isSafari } = getUserAgentData();
        const hasPrompt = !!deferredPrompt;

        if (isAndroid && (isChrome || isEdge)) {
            if (hasPrompt) {
                showSection("android-chrome-prompt");
            } else {
                showSection("android-chrome-manual");
            }
        } else if (isWindows && (isChrome || isEdge)) {
            if (hasPrompt) {
                showSection("windows-edge-prompt");
            } else {
                showSection("windows-edge-manual");
            }
        } else if (isIos && isSafari) {
            showSection("ios-safari");
        } else {
            if (hasPrompt) {
                showSection("install-btn-container");
            }
            showSection("general-browser");
        }
    };

    const triggerInstall = async (event) => {
        if (!deferredPrompt) return;

        event.preventDefault();
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;

        setButtonLoading(btn, '<i class="ti ti-loader-2 ti-spin mr-2"></i> Memproses...');

        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;

        if (result.outcome === "accepted") {
            setButtonLoading(btn, '<i class="ti ti-check ti-lg mr-2"></i> Terinstall!', "bg-green-600");
            showSection("install-success");
        } else {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }

        deferredPrompt = null;
        renderInstructions();
    };

    const init = () => {
        renderInstructions();

        window.addEventListener("beforeinstallprompt", (event) => {
            event.preventDefault();
            deferredPrompt = event;
            renderInstructions();
        });

        window.addEventListener("appinstalled", () => {
            deferredPrompt = null;
            showSection("install-success");
            renderInstructions();
        });

        document.getElementById("install-btn")?.addEventListener("click", triggerInstall);
        document.getElementById("install-btn-android")?.addEventListener("click", triggerInstall);
        document.getElementById("install-btn-windows")?.addEventListener("click", triggerInstall);
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    return { getUserAgentData };
})();
