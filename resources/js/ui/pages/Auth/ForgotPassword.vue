<template>
  <AuthLayout
    title="¿Olvidaste tu contraseña?"
    subtitle="Ingresa tu correo y te enviaremos un enlace para restablecerla."
    :error-msg="errorMsg"
  >
    <div v-if="sent" class="auth-alert auth-alert--success">
      <span class="auth-alert__icon">📧</span>
      <span>Si el correo <strong>{{ form.email }}</strong> está registrado, recibirás un enlace de recuperación en los próximos minutos.</span>
    </div>

    <form v-if="!sent" class="auth-form" @submit.prevent="handleSubmit" id="forgot-password-form">
      <div class="auth-field">
        <label class="auth-field__label" for="forgot-email">Correo electrónico</label>
        <input id="forgot-email" v-model="form.email" type="email" class="auth-field__input" placeholder="tu@correo.com" required autocomplete="email" />
        <span v-if="errors.email" class="auth-field__error">{{ errors.email }}</span>
      </div>
      <button type="submit" class="auth-btn auth-btn--primary" :disabled="loading" id="forgot-submit">
        <span v-if="loading">Enviando...</span>
        <span v-else>Enviar enlace de recuperación</span>
      </button>
    </form>

    <button v-if="sent" class="auth-btn auth-btn--ghost" @click="sent = false; form.email = ''">
      Intentar con otro correo
    </button>

    <template #footer>
      <router-link to="/auth/login" class="auth-link">← Volver a Iniciar Sesión</router-link>
    </template>
  </AuthLayout>
</template>

<script>
import AuthLayout from '../../components/AuthLayout.vue'

export default {
  name: 'ForgotPasswordPage',
  components: { AuthLayout },
  data() {
    return { form: { email: '' }, errors: {}, errorMsg: '', loading: false, sent: false }
  },
  methods: {
    async handleSubmit() {
      this.errors = {}
      this.errorMsg = ''
      if (!this.form.email) { this.errors.email = 'El correo es obligatorio.'; return }
      this.loading = true
      setTimeout(() => { this.loading = false; this.sent = true }, 1200)
    },
  },
}
</script>
