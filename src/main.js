import Vue from 'vue'
import App from './App.vue'
import { t, n } from '@nextcloud/l10n'

Vue.mixin({ methods: { t, n } });
const View = Vue.extend(App);
new View().$mount('#projectcreatoraio');