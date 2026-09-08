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
      // TODO: Conectar con backend
      setTimeout(() => {
        this.loading = false
        this.errorMsg = 'Funcionalidad de backend pendiente de implementar.'
      }, 1200)
    },
  },
}
</script>
