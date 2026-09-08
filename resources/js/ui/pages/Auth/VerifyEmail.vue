<template>
  <AuthLayout
    :title="title"
    :subtitle="subtitle"
    :error-msg="errorMsg"
  >
    <!-- Verificando -->
    <template v-if="status === 'loading'">
      <div class="auth-btn auth-btn--primary" style="pointer-events: none; opacity: 0.6;">
        Verificando...
      </div>
    </template>

    <!-- Éxito -->
    <template v-if="status === 'success'">
      <div class="auth-alert auth-alert--success">
        <span class="auth-alert__icon">✅</span>
        <span>Tu correo electrónico ha sido confirmado exitosamente. Ya puedes iniciar sesión.</span>
      </div>
      <router-link to="/auth/login" class="auth-btn auth-btn--primary" style="text-decoration: none;">
        Ir a Iniciar Sesión
      </router-link>
    </template>

    <!-- Error -->
    <template v-if="status === 'error'">
      <button class="auth-btn auth-btn--ghost" @click="resendVerification" :disabled="resending">
        <span v-if="resending">Reenviando...</span>
        <span v-else>Reenviar correo de verificación</span>
      </button>
      <div style="text-align: center; margin-top: 1rem;">
        <router-link to="/auth/login" class="auth-link">
          ← Volver a Iniciar Sesión
        </router-link>
      </div>
    </template>
  </AuthLayout>
</template>

<script>
import AuthLayout from '../../components/AuthLayout.vue'

export default {
  name: 'VerifyEmailPage',
  components: { AuthLayout },
  data() {
    return { status: 'loading', errorMsg: '', resending: false }
  },
  computed: {
    title() {
      if (this.status === 'loading') return 'Verificando tu cuenta...'
      if (this.status === 'success') return '¡Cuenta verificada!'
      return 'Error de verificación'
    },
    subtitle() {
      if (this.status === 'loading') return 'Estamos confirmando tu correo electrónico. Un momento.'
      return ''
    }
  },
  created() {
    const token = this.$route.query.token || this.$route.params.token
    if (!token) {
      this.status = 'error'
      this.errorMsg = 'El enlace de verificación no es válido o ha expirado.'
      return
    }
    setTimeout(() => { this.status = 'success' }, 1800)
  },
  methods: {
    async resendVerification() {
      this.resending = true
      setTimeout(() => { this.resending = false; this.status = 'success' }, 1500)
    },
  },
}
</script>
