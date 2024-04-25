import './bootstrap.js';
import './styles/app.css';
import 'flowbite';
import GLightbox from 'glightbox';
import "glightbox/dist/css/glightbox.css"


document.addEventListener('turbo:load', () => {
    initFlowbite();
    if(document.querySelector("#sidebar").classList.contains("transform-none")){
        document.querySelector("#sidebar").classList.remove("transform-none");
        document.querySelector("#sidebar").classList.add("-translate-x-full");
    }
})

document.addEventListener('turbo:render', () => {
    window.initLightBox();
})

document.addEventListener('turbo:before-stream-render', () => {
    setTimeout(() => {
        window.initLightBox();
        initFlowbite();
    }, 1000);
})

document.addEventListener('DOMContentLoaded', () => {
    window.initDarkMode();
    window.initLightBox();
})

document.addEventListener('live-component:update', () => {
    window.initDarkMode();
    window.initLightBox();
})

window.initDarkMode = function() {
    var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

    // Change the icons inside the button based on previous settings
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        themeToggleLightIcon?.classList.remove('hidden');
        document.documentElement.classList.add('dark');
    } else {
        themeToggleDarkIcon?.classList.remove('hidden');
    }

    var themeToggleBtn = document.getElementById('theme-toggle');

    themeToggleBtn?.addEventListener('click', function () {

        // toggle icons inside button
        themeToggleDarkIcon.classList.toggle('hidden');
        themeToggleLightIcon.classList.toggle('hidden');

        // if set via local storage previously
        if (localStorage.getItem('color-theme')) {
            if (localStorage.getItem('color-theme') === 'light') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }

            // if NOT set via local storage previously
        } else {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
        }

    });
}
window.GLightbox = GLightbox;
window.glightbox = null;
window.initLightBox = function(){
    if(glightbox){
        glightbox.destroy();
    }
    glightbox = GLightbox();
}