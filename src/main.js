import Vue from 'vue'
import App from './App.vue'
import { t, n } from '@nextcloud/l10n'
import axios from '@nextcloud/axios'

Vue.prototype.$axios = axios

Vue.mixin({ methods: { t, n } })
const View = Vue.extend(App)
new View().$mount('#projectcreatoraio')