import './bootstrap.js';
import './styles/app.css';
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