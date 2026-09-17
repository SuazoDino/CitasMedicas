import { createRouter, createWebHistory } from 'vue-router'

// Auth pages
import Login from '../ui/pages/Auth/Login.vue'
import Register from '../ui/pages/Auth/Register.vue'
import ForgotPassword from '../ui/pages/Auth/ForgotPassword.vue'
import ResetPassword from '../ui/pages/Auth/ResetPassword.vue'
import VerifyEmail from '../ui/pages/Auth/VerifyEmail.vue'

// Admin pages
import AdminLayout from '../ui/components/AdminLayout.vue'
import AdminDashboard from '../ui/pages/Admin/Dashboard.vue'
import AdminUsuarios from '../ui/pages/Admin/Usuarios.vue'
import AdminCitas from '../ui/pages/Admin/Citas.vue'

// Paciente pages
import PacienteLayout from '../ui/components/PacienteLayout.vue'
import PacienteBuscar from '../ui/pages/Paciente/Buscar.vue'
import PacienteMedicoPerfil from '../ui/pages/Paciente/MedicoPerfil.vue'
import PacienteMisCitas from '../ui/pages/Paciente/MisCitas.vue'

// API composable for auth checking
import { useAuth } from '../composables/useAuth'

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
    meta: { title: 'Verificar Correo — mediReserva', requiresGuest: true },
  },

  // Admin routes
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 1 }, // Solo administradores (rol_id = 1)
    children: [
      {
        path: '',
        redirect: '/admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { title: 'Panel de Control — Admin' },
      },
      {
        path: 'usuarios',
        name: 'admin-usuarios',
        component: AdminUsuarios,
        meta: { title: 'Gestión de Usuarios — Admin' },
      },
      {
        path: 'appointments',
        name: 'admin-citas',
        component: AdminCitas,
        meta: { title: 'Gestión de Citas Médicas — Admin' },
      }
    ]
  },
  
  // Paciente routes
  {
    path: '/paciente',
    component: PacienteLayout,
    meta: { requiresAuth: true, role: 2 }, // Solo pacientes (rol_id = 2)
    children: [
      {
        path: '',
        redirect: '/paciente/buscar'
      },
      {
        path: 'buscar',
        name: 'paciente-buscar',
        component: PacienteBuscar,
        meta: { title: 'Buscar Médicos — mediReserva' },
      },
      {
        path: 'medicos/:id',
        name: 'paciente-medico-perfil',
        component: PacienteMedicoPerfil,
        meta: { title: 'Perfil del Médico — mediReserva' },
      },
      {
        path: 'citas',
        name: 'paciente-citas',
        component: PacienteMisCitas,
        meta: { title: 'Mis Citas — mediReserva' },
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation Guards para proteger rutas
router.beforeEach(async (to, from, next) => {
  const { fetchUser, user } = useAuth();
  
  const token = localStorage.getItem('jwt_token');
  const isAuthenticated = !!token;

  if (to.meta.requiresAuth) {
    if (!isAuthenticated) {
      return next('/auth/login');
    }

    // Verificar si ya tenemos el perfil cargado, sino obtenerlo
    if (!user.value) {
      const fetchedUser = await fetchUser();
      if (!fetchedUser) return next('/auth/login');
    }

    // Validar el rol
    if (to.meta.role && user.value.rol_id !== to.meta.role) {
      // Si intenta ir a una ruta que no es de su rol
      if (user.value.rol_id === 1) return next('/admin/dashboard');
      if (user.value.rol_id === 2) return next('/paciente/buscar');
      return next('/auth/login');
    }
  }

  // Prevenir que logueados entren al login
  if (to.path.startsWith('/auth/')) {
    if (isAuthenticated) {
      if (!user.value) await fetchUser();
      if (user.value) {
        if (user.value.rol_id === 1) return next('/admin/dashboard');
        if (user.value.rol_id === 2) return next('/paciente/buscar');
      }
    }
  }

  document.title = to.meta.title || 'mediReserva';
  next();
});

export default router
