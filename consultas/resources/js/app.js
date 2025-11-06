import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import '../css/auth.css' 

axios.defaults.withCredentials = true
/* axios.get('/sanctum/csrf-cookie') */
createApp(App).use(router).mount('#app')

