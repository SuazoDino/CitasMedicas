// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import { auth } from '../auth/store'

// Imports RELATIVOS (como tienes)
import DesignShell   from '../ui/DesignShell.vue'
import AuthLogin     from '../ui/pages/Auth/Login.vue'
import AuthRegister  from '../ui/pages/Auth/Register.vue'
import MedicoHome    from '../ui/pages/MedicoHome.vue'
import PacienteHome  from '../ui/pages/PacienteHome.vue'
import ReservarCita  from '../ui/pages/ReservarCita.vue'
import ForgotPass    from '../ui/forms/ForgotPasswordForm.vue'
import ResetPass     from '../ui/forms/ResetPasswordForm.vue'
import LandingRoute  from '../ui/pages/LandingRoute.vue' 
import MedicoHorarios from '../ui/pages/MedicoHorarios.vue'
import MedicoPerfil from '../ui/pages/MedicoPerfil.vue'
import MedicoPerfilPublico from '../ui/pages/MedicoPerfilPublico.vue'
import PacientePerfil from '../ui/pages/PacientePerfil.vue'
// --- rutas (mismas que enviaste) ---
const routes = [
  {
    path: '/',
    component: DesignShell, // aquí están tus 2 <RouterView/> (overlay + page-slot)
    children: [
      { path: '', name :'landing', component : LandingRoute },
      { path: 'login', name: 'login', component: AuthLogin },
      { path: 'forgot-password', name: 'forgot-password', component: ForgotPass },
      { path: 'reset-password', name: 'reset-password', component: ResetPass },
      {
        path: 'register/paciente',
        name: 'register.paciente',
        component: AuthRegister,
        props: { defaultRole: 'paciente' },
      },
      {
        path: 'register/medico',
        name: 'register.medico',
        component: AuthRegister,
        props: { defaultRole: 'medico' },
      },
      {
        path: 'me/reservar',
        name: 'paciente.reservar',
        component: ReservarCita,
        meta: { requiresAuth: true, role: 'paciente' }
      },
      {
        path: 'doctor/:id',
        name: 'medico.perfil.publico',
        component: MedicoPerfilPublico,
        meta: { requiresAuth: false }, // Ruta pública explícita
      },
    ],
  },
  {
    path: '/medico',
    name: 'medico.home',
    component: MedicoHome,
    meta: { requiresAuth: true, role: 'medico' }
  },
  {
    path: '/medico/horarios',
    name: 'medico.horarios',
    component: MedicoHorarios,
    meta: { requiresAuth: true, role: 'medico' }
  },
  {
    path: '/medico/perfil',
    name: 'medico.perfil',
    component: MedicoPerfil,
    meta: { requiresAuth: true, role: 'medico' }
  },
  {
    path: '/me',
    component: DesignShell,
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: { name: 'paciente.home' } },
      {
        path: 'paciente',
        name: 'paciente.home',
        component: PacienteHome,
        meta: { requiresAuth: true, role: 'paciente' }
      },
      {
        path: 'perfil',
        name: 'paciente.perfil',
        component: PacientePerfil,
        meta: { requiresAuth: true, role: 'paciente' }
      },
    ]
  },
  // Catch-all debe estar al final para no interferir con otras rutas
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// --------- Helpers de sesión ----------
const getToken = () => {
  // Leer del mismo lugar donde se guarda el cache
  // Primero intentar sessionStorage (más reciente), luego localStorage, luego auth.token
  const sessionToken = sessionStorage.getItem('token') || sessionStorage.getItem('auth_token')
  const localToken = localStorage.getItem('token') || localStorage.getItem('auth_token')
  return sessionToken || localToken || auth.token
}

const setAuthHeader = (token) => {
  if (!axios.defaults.headers) axios.defaults.headers = {}
  if (!axios.defaults.headers.common) axios.defaults.headers.common = {}
  if (token) axios.defaults.headers.common.Authorization = `Bearer ${token}`
  else delete axios.defaults.headers.common.Authorization
}

const clearSession = () => {
  setAuthHeader(null)
  auth.clear()
  // Limpiar también las claves antiguas por si acaso
  ;['token', 'auth_token', 'access_token', 'user_name', 'roles'].forEach(k => {
    localStorage.removeItem(k)
    sessionStorage.removeItem(k)
  })
  sessionStorage.removeItem('whoami_ok')
  sessionStorage.removeItem('whoami_token')
}

// Si ya hay token desde antes, ponlo en axios
const t0 = getToken()
if (t0) setAuthHeader(t0)

// --------- Guard global ----------
router.beforeEach(async (to, from, next) => {
  const needsAuth = to.matched.some(r => r.meta?.requiresAuth)
  
  // Para rutas públicas (sin requiresAuth), no hacer validación de autenticación
  if (!needsAuth) {
    // Solo configurar header si hay token, pero no validar
    const token = getToken()
    if (token) {
      setAuthHeader(token)
      // Verificar si el token cambió y limpiar cache si es necesario
      const cachedTokenForWhoami = sessionStorage.getItem('whoami_token')
      const normalizeToken = (t) => t ? String(t).trim() : ''
      if (cachedTokenForWhoami && normalizeToken(cachedTokenForWhoami) !== normalizeToken(token)) {
        console.warn('⚠️ Token cambió en ruta pública, invalidando cache')
        sessionStorage.removeItem('whoami_ok')
        sessionStorage.removeItem('whoami_token')
        auth.name = ''
        auth.roles = []
      }
    }
    // Permitir navegación sin validación
    return next()
  }

  const token = getToken()
  if (!token) {
    clearSession()
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  setAuthHeader(token)

  try {
    // Obtener el token actual de forma consistente
    const currentToken = getToken()
    
    // Si no hay token, ya se manejó arriba
    if (!currentToken) {
      clearSession()
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }
    
    const cachedWhoami = sessionStorage.getItem('whoami_ok')
    const cachedTokenForWhoami = sessionStorage.getItem('whoami_token')
    
    // Normalizar tokens para comparación (eliminar espacios, etc)
    const normalizeToken = (t) => t ? String(t).trim() : ''
    const normalizedCurrent = normalizeToken(currentToken)
    const normalizedCached = normalizeToken(cachedTokenForWhoami)
    
    // Si el token cambió REALMENTE, invalidar TODO el cache y limpiar datos
    if (cachedTokenForWhoami && normalizedCached !== normalizedCurrent) {
      console.warn('⚠️ Token cambió, invalidando cache de usuario', {
        cached: normalizedCached.substring(0, 20) + '...',
        current: normalizedCurrent.substring(0, 20) + '...'
      })
      sessionStorage.removeItem('whoami_ok')
      sessionStorage.removeItem('whoami_token')
      // Limpiar datos del usuario anterior del store centralizado
      auth.name = ''
      auth.roles = []
    }
    
    // Solo llamar a /api/auth/me si NO hay cache válido O el token cambió REALMENTE
    // También forzar si el store está vacío (datos inconsistentes)
    const storeIsEmpty = !auth.name && !auth.roles.length
    const needsWhoami = !cachedWhoami || !cachedTokenForWhoami || normalizedCached !== normalizedCurrent || storeIsEmpty
    
    if (needsWhoami) {
      console.log('🔍 Obteniendo información del usuario desde el servidor...')
      
      // Importar api aquí para evitar dependencias circulares
      const { default: api } = await import('../services/api')
      
      try {
        // Asegurarse de que el header esté configurado con el token actual
        setAuthHeader(currentToken)
        
        const { data } = await api.get('/auth/me')
        const name  = data?.user?.name || data?.name || ''
        const roles = data?.roles || data?.user?.roles || []
        
        console.log('👤 Usuario obtenido:', name)
        console.log('👤 Roles obtenidos:', roles)
        
        // IMPORTANTE: Actualizar el store centralizado ANTES de validar roles
        auth.name = String(name)
        auth.roles = Array.isArray(roles) ? roles : []
        
        // Guardar el cache de validación con el token NORMALIZADO
        sessionStorage.setItem('whoami_ok', '1')
        sessionStorage.setItem('whoami_token', normalizedCurrent)

        // Validar rol si la ruta lo requiere
        const needRole = to.meta?.role
        if (needRole) { 
          const hasRole = Array.isArray(roles)
            ? roles.some(r => (r?.name ?? r) === needRole)
            : (data?.user?.role === needRole)
          if (!hasRole) {
            console.warn(`⚠️ Usuario no tiene el rol requerido: ${needRole}`, {
              usuario: name,
              roles: roles,
              requerido: needRole
            })
            return next(needRole === 'medico' ? { name: 'paciente.home' } : { name: 'medico.home' })
          }
        }
      } catch (err) {
        console.error('Error al obtener información del usuario:', err)
        // Si falla, limpiar cache y redirigir a login
        if (err?.response?.status === 401) {
          clearSession()
          return next({ name: 'login', query: { redirect: to.fullPath } })
        }
        // Si hay otro error, no permitir navegación
        throw err
      }
    } else {
      // Si hay cache válido y el token coincide, usar los datos del store
      console.log('✅ Usando cache de usuario válido', {
        nombre: auth.name,
        roles: auth.roles
      })
      
      // Asegurarse de que el store tenga los datos correctos
      // Si el store está vacío pero hay cache, algo está mal - forzar re-obtención
      if (!auth.name && !auth.roles.length) {
        console.warn('⚠️ Cache válido pero store vacío, re-obteniendo datos...')
        // Forzar re-obtención eliminando el cache
        sessionStorage.removeItem('whoami_ok')
        sessionStorage.removeItem('whoami_token')
        // Continuar con la lógica de needsWhoami = true
        // Esto se hará en la próxima iteración del guard
      }
      
      // Solo validar rol si la ruta lo requiere
      const needRole = to.meta?.role
      if (needRole) {
        const hasRole = auth.roles.some(r => {
          const roleName = typeof r === 'string' ? r : (r?.name ?? r)
          return roleName === needRole
        })
        if (!hasRole) {
          console.warn(`⚠️ Usuario no tiene el rol requerido: ${needRole}`, {
            usuario: auth.name,
            roles: auth.roles,
            requerido: needRole
          })
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
    console.error('Error en router guard:', e)
    return next()
  }
})

export default router
