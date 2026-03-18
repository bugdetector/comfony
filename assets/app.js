import './bootstrap.js';
import './styles/app.css';
import 'daisyui';
import GLightbox from 'glightbox';
import "glightbox/dist/css/glightbox.css"

document.addEventListener('turbo:load', (e) => {
    console.log(e.type);
    initializeComponents();
})

document.addEventListener('turbo:render', (e) => {
    console.log(e.type);
    initializeComponents();
})

document.addEventListener('turbo:before-stream-render', (event) => {
    console.log(event.type);
    const fallbackToDefaultActions = event.detail.render;

    event.detail.render = function (streamElement) {
        fallbackToDefaultActions(streamElement);
        initializeComponents();
    }

})

document.addEventListener('turbo:before-frame-render', (e) => {
    console.log(e.type);
    setTimeout(() => {
        initializeComponents();
    }, 200);
})

document.addEventListener('DOMContentLoaded', (e) => {
    console.log(e.type);
    initializeComponents();
})

document.addEventListener('live-component:update', (e) => {
    console.log(e.type);
    initializeComponents();
})

document.addEventListener('app-hide-modal', (e) => {
    document.querySelectorAll('dialog[open]').forEach((dialog) => dialog.close())
})


window.initializeComponents = function () {
    window.initLightBox();
    window.initThemeSelector();
    window.initSidebarDrawerState();
    window.initSidebarSubmenus();
}

window.addEventListener('popstate', function (event) {

}, false);

window.GLightbox = GLightbox;
window.glightbox = null;
window.initLightBox = function () {
    if (glightbox) {
        glightbox.destroy();
    }
    glightbox = GLightbox();
}

window.initThemeSelector = function () {
    const themeInputs = document.querySelectorAll('input[name="theme"]');
    const themeKey = "theme";

    const activeTheme = localStorage.getItem(themeKey) || 'system';
    if (activeTheme === "system") {
        const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        document.documentElement.setAttribute("data-theme", prefersDark ? "dark" : "light");
    } else {
        document.documentElement.setAttribute("data-theme", activeTheme);
    }

    const activeInput = document.querySelector(`input[name="theme"][value="${activeTheme}"]`);
    if (activeInput) activeInput.checked = true;

    themeInputs.forEach(input => {
        input.addEventListener("click", (e) => {
            const newTheme = e.target.value;

            if (newTheme === "system") {
                const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
                document.documentElement.setAttribute("data-theme", prefersDark ? "dark" : "light");
            } else {
                document.documentElement.setAttribute("data-theme", newTheme);
            }

            localStorage.setItem(themeKey, newTheme);
        });
    });
}

window.initSidebarDrawerState = function () {
    const drawerToggle = document.getElementById('app-drawer');

    if (!drawerToggle) {
        return;
    }

    const desktopBreakpoint = window.matchMedia('(min-width: 1024px)');
    const storageKey = 'base-sidebar-open';

    const syncDrawerState = () => {
        if (desktopBreakpoint.matches) {
            const storedState = localStorage.getItem(storageKey);
            drawerToggle.checked = storedState === null ? true : storedState === 'true';
            return;
        }

        drawerToggle.checked = false;
    };

    syncDrawerState();

    if (window.__sidebarDrawerStateInitialized) {
        return;
    }

    drawerToggle.addEventListener('change', () => {
        if (!desktopBreakpoint.matches) {
            return;
        }

        localStorage.setItem(storageKey, String(drawerToggle.checked));
    });

    const handleBreakpointChange = () => {
        syncDrawerState();
    };

    if (typeof desktopBreakpoint.addEventListener === 'function') {
        desktopBreakpoint.addEventListener('change', handleBreakpointChange);
    } else {
        desktopBreakpoint.addListener(handleBreakpointChange);
    }

    window.__sidebarDrawerStateInitialized = true;
}

window.initSidebarSubmenus = function () {
    if (window.__sidebarSubmenuListenersInitialized) {
        return;
    }

    const mobileBreakpoint = window.matchMedia('(max-width: 1023px)');
    const desktopBreakpoint = window.matchMedia('(min-width: 1024px)');

    const closeOpenSidebarSubmenus = (eventTarget = null) => {
        const drawerToggle = document.getElementById('app-drawer');
        if (drawerToggle?.checked) {
            return;
        }
        document.querySelectorAll('.sidebar-details[open]').forEach((details) => {
            if (eventTarget && details.contains(eventTarget)) {
                return;
            }

            details.open = false;
        });
    };

    const closeMobileSidebarDrawer = () => {
        if (!mobileBreakpoint.matches) {
            return;
        }

        const drawerToggle = document.getElementById('app-drawer');
        if (drawerToggle) {
            drawerToggle.checked = false;
        }
    };

    const shouldKeepSubmenuOpen = () => {
        const drawerToggle = document.getElementById('app-drawer');
        return drawerToggle?.checked;
    };

    document.addEventListener('click', (event) => {
        const clickedSidebarLink = event.target.closest('.sidebar-submenu a, .sidebar-menu > li > a');

        if (clickedSidebarLink) {
            const parentDetails = clickedSidebarLink.closest('.sidebar-details');
            if (parentDetails && !shouldKeepSubmenuOpen()) {
                parentDetails.open = false;
            }

            closeMobileSidebarDrawer();
        }

        closeOpenSidebarSubmenus(event.target);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeOpenSidebarSubmenus();
            closeMobileSidebarDrawer();
        }
    });

    window.__sidebarSubmenuListenersInitialized = true;
}