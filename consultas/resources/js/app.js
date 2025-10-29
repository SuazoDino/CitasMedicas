import { createApp } from 'vue'
import router from './router'
import DesignShell from './ui/DesignShell.vue'

createApp(DesignShell).use(router).mount('#app')

