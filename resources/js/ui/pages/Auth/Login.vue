<template>
  <AuthLayout
    title="Iniciar Sesión"
    subtitle="Ingresa tus credenciales para acceder a tu cuenta."
    :error-msg="errorMsg"
    :success-msg="successMsg"
  >
    <!-- Formulario -->
    <form class="auth-form" @submit.prevent="handleLogin" id="login-form">
      <div class="auth-field">
        <label class="auth-field__label" for="login-email">Correo electrónico</label>
        <input
          id="login-email"
          v-model="form.email"
          type="email"
          class="auth-field__input"
          placeholder="tu@correo.com"
          required
          autocomplete="email"
        />
        <span v-if="errors.email" class="auth-field__error">{{ errors.email }}</span>
      </div>

      <div class="auth-field">
        <div class="auth-field__header">
          <label class="auth-field__label" for="login-password">Contraseña</label>
          <label class="auth-field__toggle">
            <input type="checkbox" v-model="showPassword" />
            Mostrar
          </label>
        </div>
        <input
          id="login-password"
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          class="auth-field__input"
          placeholder="••••••••"
          required
          autocomplete="current-password"
        />
        <span v-if="errors.password" class="auth-field__error">{{ errors.password }}</span>
      </div>

      <!-- Opciones -->
      <div class="auth-options">
        <label class="auth-options__remember">
          <input type="checkbox" v-model="form.remember" />
          Recordarme
        </label>
        <router-link to="/auth/forgot-password" class="auth-link">
          ¿Olvidaste tu contraseña?
        </router-link>
      </div>

      <!-- Botón -->
      <button type="submit" class="auth-btn auth-btn--primary" :disabled="loading" id="login-submit">
        <span v-if="loading">Iniciando sesión...</span>
        <span v-else>Iniciar Sesión</span>
      </button>
    </form>

    <template #footer>
      <span>¿No tienes una cuenta?</span>
      <router-link to="/auth/register" class="auth-link">Regístrate aquí</router-link>
    </template>
  </AuthLayout>
</template>

<script>
import AuthLayout from '../../components/AuthLayout.vue'
import axios from '../../../axios'
import { useAuth } from '../../../composables/useAuth'

export default {
  name: 'LoginPage',
  components: {
    AuthLayout
  },
  data() {
    return {
      form: { email: '', password: '', remember: false },
      errors: {},
      errorMsg: '',
      successMsg: '',
      loading: false,
      showPassword: false,
      fetchUser: useAuth().fetchUser,
    }
  },
  created() {
    const msg = this.$route.query.msg
    if (msg) this.successMsg = msg
  },
  methods: {
    validate() {
      this.errors = {}
      if (!this.form.email) this.errors.email = 'El correo es obligatorio.'
      if (!this.form.password) this.errors.password = 'La contraseña es obligatoria.'
      return Object.keys(this.errors).length === 0
    },
    async handleLogin() {
      this.errorMsg = ''
      this.successMsg = ''
      if (!this.validate()) return
      this.loading = true
      
      try {
        const response = await axios.post('/login', this.form);
        const { access_token } = response.data;
        
        // Guardar token
        localStorage.setItem('jwt_token', access_token);
        
        // Cargar información del usuario
        const user = await this.fetchUser();
        
        if (user) {
          if (user.rol_id === 1) {
            this.$router.push('/admin/dashboard');
          } else if (user.rol_id === 2) {
            this.$router.push('/paciente/dashboard');
          } else {
            this.$router.push('/');
          }
        }
      } catch (error) {
        if (error.response) {
          if (error.response.status === 401) {
            this.errorMsg = 'Credenciales incorrectas.';
          } else if (error.response.status === 403) {
            this.errorMsg = 'Tu cuenta aún no está verificada. Revisa tu correo.';
          } else {
            this.errorMsg = 'Error al iniciar sesión.';
          }
        } else {
          this.errorMsg = 'Problema de conexión con el servidor.';
        }
      } finally {
        this.loading = false
      }
    },
  },
}
</script>
