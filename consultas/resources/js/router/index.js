// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

// Imports RELATIVOS (como tienes)
import DesignShell   from '../ui/DesignShell.vue'
import Login         from '../ui/forms/LoginForm.vue'
import RegPaciente   from '../ui/forms/RegisterPacienteForm.vue'
import RegMedico     from '../ui/forms/RegisterMedicoForm.vue'
import MedicoHome    from '../ui/pages/MedicoHome.vue'
import PacienteHome  from '../ui/pages/PacienteHome.vue'
import ReservarCita  from '../ui/pages/ReservarCita.vue'

// --- rutas (mismas que enviaste) ---
const routes = [
  {
    path: '/',
    name: 'landing',
    component: DesignShell, // aquí están tus 2 <RouterView/> (overlay + page-slot)
    children: [
      { path: 'login', component: Login },
      { path: 'register/paciente', component: RegPaciente },
      { path: 'register/medico', component: RegMedico },
      { path: 'me/reservar', component: ReservarCita, meta: { requiresAuth: true, role: 'paciente' } },
    ],
  },
  {
    path: '/medico',
    name: 'medico.home',
    component: MedicoHome,
    meta: { requiresAuth: true, role: 'medico' }
  },
  { path: '/me', redirect: { name: 'paciente.home' } },
  {
    path: '/me/paciente',
    name: 'paciente.home',
    component: PacienteHome,
    meta: { requiresAuth: true, role: 'paciente' }
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// --------- Helpers de sesión ----------
const getToken = () =>
  localStorage.getItem('token') ||
  localStorage.getItem('auth_token') ||
  localStorage.getItem('access_token') ||
  sessionStorage.getItem('token') || null

const setAuthHeader = (token) => {
  if (!axios.defaults.headers) axios.defaults.headers = {}
  if (!axios.defaults.headers.common) axios.defaults.headers.common = {}
  if (token) axios.defaults.headers.common.Authorization = `Bearer ${token}`
  else delete axios.defaults.headers.common.Authorization
}

const clearSession = () => {
  setAuthHeader(null)
  ;['token', 'auth_token', 'access_token', 'user_name', 'roles'].forEach(k => localStorage.removeItem(k))
  sessionStorage.removeItem('token')
  sessionStorage.removeItem('whoami_ok')
}

// Si ya hay token desde antes, ponlo en axios
const t0 = getToken()
if (t0) setAuthHeader(t0)

// --------- Guard global ----------
router.beforeEach(async (to, from, next) => {
  const needsAuth = to.matched.some(r => r.meta?.requiresAuth)
  if (!needsAuth) return next()

  const token = getToken()
  if (!token) {
    clearSession()
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  setAuthHeader(token)

  try {
    // llama /api/auth/me solo una vez por sesión para cachear nombre/roles
    if (!sessionStorage.getItem('whoami_ok')) {
      const { data } = await axios.get('/api/auth/me')
      const name  = data?.user?.name || data?.name
      if (name) localStorage.setItem('user_name', name)
      const roles = data?.roles || data?.user?.roles
      localStorage.setItem('roles', JSON.stringify(roles))
      sessionStorage.setItem('whoami_ok', '1')

      // (opcional) validar rol si la ruta lo pide
      const needRole = to.meta?.role
      if (needRole) {
        const roles = data?.user?.roles || data?.roles || []
        const hasRole = Array.isArray(roles)
          ? roles.some(r => (r?.name ?? r) === needRole)
          : (data?.user?.role === needRole)
        if (!hasRole) {
          // redirige al área “segura” según lo que tenga el usuario
          // ajusta a tu lógica si quieres algo distinto
          return next(needRole === 'medico' ? { name: 'paciente.home' } : { name: 'medico.home' })
        }
      }
    }
    return next()
  } catch (e) {
    if (e?.response?.status === 401) {
      clearSession()
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
    // si el backend falla por otra razón, deja pasar pero con header puesto
    return next()
  }
})

export default router
