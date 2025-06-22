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
})


window.initializeComponents = function(){
    window.initLightBox();
    if (!window.sidebarInitialized) {
        document.querySelectorAll('.sidebar-toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('open');
                }
            });
        });
        document.querySelectorAll('.sidebar .menu li').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.remove('open');
                }
            });
        });
        window.sidebarInitialized = true;
    }
}

window.addEventListener('popstate', function(event) {
   
}, false);

window.GLightbox = GLightbox;
window.glightbox = null;
window.initLightBox = function () {
    if (glightbox) {
        glightbox.destroy();
    }
    glightbox = GLightbox();
}