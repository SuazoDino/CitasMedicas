<template>
  <div class="auth-page">
    <div class="auth-card" :class="{'auth-card--wide': isWide}">
      <!-- Logo -->
      <div class="auth-brand">
        <img src="/img/logo_icon.png" alt="mediReserva" class="auth-brand__logo" />
        <div class="auth-brand__text">
          <span class="auth-brand__name"><span>medi</span>Reserva</span>
          <span class="auth-brand__slogan">Tu salud, a tiempo</span>
        </div>
      </div>

      <!-- Heading -->
      <div class="auth-heading">
        <h1 class="auth-heading__title">{{ title }}</h1>
        <p v-if="subtitle" class="auth-heading__subtitle">
          {{ subtitle }}
        </p>
      </div>

      <!-- Alertas -->
      <div v-if="errorMsg" class="auth-alert auth-alert--danger">
        <span class="auth-alert__icon">⚠️</span>
        <span>{{ errorMsg }}</span>
      </div>

      <div v-if="successMsg" class="auth-alert auth-alert--success">
        <span class="auth-alert__icon">✅</span>
        <span>{{ successMsg }}</span>
      </div>

      <!-- Contenido principal (Formulario) -->
      <slot></slot>

      <hr v-if="$slots.footer" class="auth-divider" />

      <!-- Footer -->
      <div v-if="$slots.footer" class="auth-footer">
        <slot name="footer"></slot>
      </div>

      <!-- Copyright -->
      <div class="auth-copyright">
        © {{ new Date().getFullYear() }} mediReserva — Todos los derechos reservados.
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AuthLayout',
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      default: ''
    },
    isWide: {
      type: Boolean,
      default: false
    },
    errorMsg: {
      type: String,
      default: ''
    },
    successMsg: {
      type: String,
      default: ''
    }
  }
}
</script>
