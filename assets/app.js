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
    if (!window.sidebarInitialized) {
        document.querySelectorAll('.sidebar-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('sidebar-backdrop');
                
                if (sidebar) {
                    sidebar.classList.toggle('open');
                    
                    if (backdrop) {
                        if (sidebar.classList.contains('open')) {
                            backdrop.classList.remove('hidden');
                            // Small delay to allow display:block to apply before opacity transition
                            setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                        } else {
                            backdrop.classList.add('opacity-0');
                            setTimeout(() => backdrop.classList.add('hidden'), 300);
                        }
                    }
                }
                
                if(sidebar.classList.contains('open')){
                    document.querySelector('.sidebar-placeholder').classList.add('w-64');
                } else {
                    document.querySelector('.sidebar-placeholder').classList.remove('w-64');
                }
            });
        });

        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        // Close when clicking backdrop
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                if (sidebar && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => backdrop.classList.add('hidden'), 300);
                    document.querySelector('.sidebar-placeholder').classList.remove('w-64');
                }
            });
        }

        if (sidebar) {
            sidebar.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 768 && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        if (backdrop) {
                            backdrop.classList.add('opacity-0');
                            setTimeout(() => backdrop.classList.add('hidden'), 300);
                        }
                        document.querySelector('.sidebar-placeholder').classList.remove('w-64');
                    }
                });
            });
        }
        window.sidebarInitialized = true;
    }
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
