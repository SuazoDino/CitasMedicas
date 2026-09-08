import { createRouter, createWebHistory } from 'vue-router'

// Auth pages
import Login from '../ui/pages/Auth/Login.vue'
import Register from '../ui/pages/Auth/Register.vue'
import ForgotPassword from '../ui/pages/Auth/ForgotPassword.vue'
import ResetPassword from '../ui/pages/Auth/ResetPassword.vue'
import VerifyEmail from '../ui/pages/Auth/VerifyEmail.vue'

const routes = [
  // Redirigir la raíz al login
  {
    path: '/',
    redirect: '/auth/login',
  },

  // Rutas de autenticación
  {
    path: '/auth/login',
    name: 'login',
    component: Login,
    meta: { title: 'Iniciar Sesión — mediReserva' },
  },
  {
    path: '/auth/register',
    name: 'register',
    component: Register,
    meta: { title: 'Crear Cuenta — mediReserva' },
  },
  {
    path: '/auth/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { title: 'Recuperar Contraseña — mediReserva' },
  },
  {
    path: '/auth/reset-password',
    name: 'reset-password',
    component: ResetPassword,
    meta: { title: 'Nueva Contraseña — mediReserva' },
  },
  {
    path: '/auth/verify-email',
    name: 'verify-email',
    component: VerifyEmail,
    meta: { title: 'Verificar Correo — mediReserva' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Actualizar título de la pestaña según la ruta
router.afterEach((to) => {
  document.title = to.meta.title || 'mediReserva'
})

export default router
