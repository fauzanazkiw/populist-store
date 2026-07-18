// Toast popup auto-dismiss
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toast-popup").forEach((toast) => {
        // Auto-dismiss after 5 seconds
        const timer = setTimeout(() => {
            dismissToast(toast);
        }, 5000);

        // Store timer on element so we can clear it on manual close
        toast._dismissTimer = timer;
    });
});

/**
 * Dismiss a toast with slide-out animation, then remove it.
 */
function dismissToast(toast) {
    if (toast._dismissed) return;
    toast._dismissed = true;

    // Clear the auto-dismiss timer if manually closed
    if (toast._dismissTimer) {
        clearTimeout(toast._dismissTimer);
    }

    // Add leave animation class
    toast.classList.add("toast-leave");

    // Remove from DOM after animation completes
    toast.addEventListener(
        "animationend",
        () => {
            toast.remove();
        },
        { once: true },
    );
}
