
import ProjectWidget from './components/ProjectWidget.vue'
import Vue from 'vue'

const APP_ID = 'projectcreatoraio';

document.addEventListener('DOMContentLoaded', () => {
    OCA.Dashboard.register(APP_ID, (el) => {
        const View = Vue.extend(ProjectWidget);
        const vm = new View({
            propsData: {},
        }).$mount(el);
    });
});