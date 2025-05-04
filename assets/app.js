import './bootstrap.js';
import './styles/app.css';
import './theme/core/index.ts';
import GLightbox from 'glightbox';
import "glightbox/dist/css/glightbox.css"

// Keen Icons
import './theme/vendors/keenicons/duotone/style.css';
import './theme/vendors/keenicons/filled/style.css';
import './theme/vendors/keenicons/outline/style.css';
import './theme/vendors/keenicons/solid/style.css';

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
    KTModal.hide();
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
    KTModal.hide();
})


window.initializeComponents = function(){
    KTComponents.init();
    window.initLightBox();
}

window.addEventListener('popstate', function(event) {
    window.KT_DRAWER_INITIALIZED = false;
    window.KT_DROPDOWN_INITIALIZED = false;
    window.KT_MENU_INITIALIZED = false;
    window.KT_MODAL_INITIALIZED = false;
    window.KT_REPARENT_INITIALIZED = false;
    window.KT_SCROLL_INITIALIZED = false;
    window.KT_TABS_INITIALIZED = false;
    window.KT_TOOLTIP_INITIALIZED = false;
}, false);

window.GLightbox = GLightbox;
window.glightbox = null;
window.initLightBox = function () {
    if (glightbox) {
        glightbox.destroy();
    }
    glightbox = GLightbox();
}