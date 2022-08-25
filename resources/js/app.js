require('./bootstrap');
import "tailwindcss/tailwind.css";
import router from './router/router';
import store from './store/store';
import shared from './shared';
import jsonToHtml from './jsonToHtml';


window.Vue = require('vue');

// Configuración de ViewUI (paquete de diseño de vista);
import ViewUI from 'view-design';
import 'view-design/dist/styles/iview.css';
Vue.use(ViewUI);

// Envoltorio Vue para Editor.js
import Editor from 'vue-editor-js';
Vue.use(Editor);


Vue.mixin(shared);
Vue.mixin(jsonToHtml);

// Vue - componente raíz
Vue.component('App', require('./App.vue').default);
const app = new Vue({
    el: '#app', 
    router, 
    store
})