import { createRouter, createWebHistory } from 'vue-router'

// Formularios que deben renderizarse dentro del overlay del DesignShell
import LoginForm from '@/ui/forms/LoginForm.vue'
import RegisterPacienteForm from '@/ui/forms/RegisterPacienteForm.vue'
import RegisterMedicoForm from '@/ui/forms/RegisterMedicoForm.vue'

// Páginas (puedes tener otras, dejo estos ejemplos)
import MedicoHome from '@/pages/medico/Home.vue'
import MePage from '@/ui/pages/MePage.vue'

// El DesignShell controla el overlay. Aquí SOLO definimos qué componente
// se debe renderizar en <RouterView/> para cada ruta.
const routes = [
  { path: '/', name: 'home', component: { template: '<div />' } },

  // Rutas que abrirán tu modal (DesignShell detecta la URL y muestra overlay)
  { path: '/login',             name: 'login',             component: LoginForm },
  { path: '/register/paciente', name: 'register.paciente', component: RegisterPacienteForm },
  { path: '/register/medico',   name: 'register.medico',   component: RegisterMedicoForm },

  // Ejemplos de páginas protegidas (si luego quieres bloquear por token/rol)
  { path: '/medico', name: 'medico.home', component: MedicoHome },
  { path: '/me',     name: 'me',          component: MePage },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.afterEach(() => window.scrollTo(0, 0))

export default router
