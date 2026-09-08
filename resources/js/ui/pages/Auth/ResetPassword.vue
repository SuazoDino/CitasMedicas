<template>
  <AuthLayout
    title="Nueva Contraseña"
    subtitle="Ingresa y confirma tu nueva contraseña para recuperar el acceso."
    :error-msg="errorMsg"
  >
    <div v-if="success" class="auth-alert auth-alert--success">
      <span class="auth-alert__icon">✅</span>
      <span>Tu contraseña ha sido actualizada exitosamente.</span>
    </div>

    <form v-if="!success" class="auth-form" @submit.prevent="handleSubmit" id="reset-password-form">
      <div class="auth-field">
        <div class="auth-field__header">
          <label class="auth-field__label" for="reset-password">Nueva contraseña</label>
          <label class="auth-field__toggle">
            <input type="checkbox" v-model="showPassword" />
            Mostrar
          </label>
        </div>
        <input id="reset-password" v-model="form.password" :type="showPassword ? 'text' : 'password'" class="auth-field__input" placeholder="Mínimo 8 caracteres" required />
        <span v-if="errors.password" class="auth-field__error">{{ errors.password }}</span>
      </div>
      <div class="auth-field">
        <label class="auth-field__label" for="reset-confirm">Confirmar contraseña</label>
        <input id="reset-confirm" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" class="auth-field__input" placeholder="Repite tu nueva contraseña" required />
        <span v-if="errors.password_confirmation" class="auth-field__error">{{ errors.password_confirmation }}</span>
      </div>
      <button type="submit" class="auth-btn auth-btn--primary" :disabled="loading" id="reset-submit">
        <span v-if="loading">Restableciendo...</span>
        <span v-else>Restablecer Contraseña</span>
      </button>
    </form>

    <router-link v-if="success" to="/auth/login" class="auth-btn auth-btn--primary" style="text-decoration: none;">
      Ir a Iniciar Sesión
    </router-link>
  </AuthLayout>
</template>

<script>
import AuthLayout from '../../components/AuthLayout.vue'

export default {
  name: 'ResetPasswordPage',
  components: { AuthLayout },
  data() {
    return {
      form: { password: '', password_confirmation: '' },
      token: '', errors: {}, errorMsg: '', loading: false, success: false, showPassword: false,
    }
  },
  created() {
    this.token = this.$route.query.token || this.$route.params.token || ''
    if (!this.token) this.errorMsg = 'Token de recuperación no válido o expirado.'
  },
  methods: {
    async handleSubmit() {
      this.errors = {}; this.errorMsg = ''
      if (!this.form.password) { this.errors.password = 'Obligatorio.'; return }
      if (this.form.password.length < 8) { this.errors.password = 'Mínimo 8 caracteres.'; return }
      if (this.form.password !== this.form.password_confirmation) { this.errors.password_confirmation = 'No coinciden.'; return }
      this.loading = true
      setTimeout(() => { this.loading = false; this.success = true }, 1200)
    },
  },
}
</script>
