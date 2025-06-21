
import Vue from 'vue'
import ProjectsWidget from './components/ProjectsWidget.vue'
import TasksWidget from './components/TasksWidget.vue';
import { APP_ID } from "./macros/app-id.js";

document.addEventListener('DOMContentLoaded', () => {
    OCA.Dashboard.register(APP_ID  + 'projects', (el) => {
        const View = Vue.extend(ProjectsWidget);
        const vm = new View({ propsData: {} }).$mount(el);
    });

    OCA.Dashboard.register(APP_ID + 'tasks', (el) => {
        const View = Vue.extend(TasksWidget);
        const vm = new View({ propsData: {} }).$mount(el);
    });
}); 