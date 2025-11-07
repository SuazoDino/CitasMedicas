<template>
  <div class="doctoralia-auth">
    <!-- Logo centrado arriba -->
    <div class="auth-logo">
      <span class="auth-logo__icon">⚡</span>
      <span class="auth-logo__text">MediReserva</span>
    </div>

    <!-- Formulario centrado -->
    <div class="auth-container">
      <form @submit.prevent="submit" class="auth-form">
        <h1 class="auth-title">Restablecer contraseña</h1>
        <p class="auth-subtitle">Ingresa una nueva contraseña segura</p>

        <div class="auth-field">
          <input
            v-model="email"
            type="email"
            autocomplete="email"
            class="auth-input"
            placeholder="Correo electrónico"
            required
          />
        </div>

        <div class="auth-field">
          <input
            v-model="password"
            type="password"
            autocomplete="new-password"
            class="auth-input"
            placeholder="Nueva contraseña"
            required
          />
        </div>

        <div class="auth-field">
          <input
            v-model="passwordConfirmation"
            type="password"
            autocomplete="new-password"
            class="auth-input"
            placeholder="Confirmar contraseña"
            required
          />
        </div>

        <button class="auth-submit" type="submit" :disabled="loading">
          <span v-if="loading">Guardando…</span>
          <span v-else>Actualizar contraseña</span>
        </button>

        <p v-if="message" class="auth-message auth-message--success">{{ message }}</p>
        <p v-if="error" class="auth-message auth-message--error">{{ error }}</p>

        <p class="auth-footer">
          <RouterLink class="auth-link" :to="{ name: 'login' }">
            Volver al inicio de sesión
          </RouterLink>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '../../auth/api'

const route = useRoute()

// Función helper para obtener valores seguros de la query
const getQueryString = (key, defaultValue = '') => {
  const value = route.query[key]
  if (typeof value === 'string') return value
  if (value == null) return defaultValue
  return String(value)
}

const token = computed(() => getQueryString('token', ''))
const email = ref(getQueryString('email', ''))
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const message = ref('')
const error = ref('')

async function submit() {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    if (!token.value) {
      throw new Error('Falta el token de restablecimiento. Vuelve a solicitar el enlace.')
    }

    await api.post('/auth/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    message.value = 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.'
    password.value = ''
    passwordConfirmation.value = ''
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || 'No pudimos restablecer tu contraseña. Revisa el enlace e inténtalo otra vez.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Contenedor principal - estilo Doctoralia */
.doctoralia-auth {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background: #ffffff;
}

/* Logo */
.auth-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
  cursor: pointer;
}

.auth-logo__icon {
  font-size: 1.75rem;
}

.auth-logo__text {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00f5ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Contenedor del formulario */
.auth-container {
  width: 100%;
  max-width: 420px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.auth-title {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  text-align: center;
}

.auth-subtitle {
  margin: 0;
  font-size: 0.95rem;
  color: #6b7280;
  text-align: center;
  line-height: 1.5;
}

/* Campos */
.auth-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.auth-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  font-size: 1rem;
  color: #111827;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.auth-input::placeholder {
  color: #9ca3af;
}

.auth-input:focus {
  outline: none;
  border-color: #8338ec;
  box-shadow: 0 0 0 3px rgba(131, 56, 236, 0.1);
}

/* Botón de envío */
.auth-submit {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  color: #ffffff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.auth-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(131, 56, 236, 0.4);
}

.auth-submit:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Mensajes */
.auth-message {
  margin: 0;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  text-align: center;
}

.auth-message--success {
  background: rgba(22, 163, 74, 0.1);
  color: #16a34a;
  border: 1px solid rgba(22, 163, 74, 0.2);
}

.auth-message--error {
  background: rgba(220, 38, 38, 0.1);
  color: #dc2626;
  border: 1px solid rgba(220, 38, 38, 0.2);
}

/* Footer */
.auth-footer {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.auth-link {
  color: #8338ec;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}

.auth-link:hover {
  color: #ff006e;
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 600px) {
  .doctoralia-auth {
    padding: 1.5rem 1rem;
  }

  .auth-logo {
    margin-bottom: 1.5rem;
  }

  .auth-title {
    font-size: 1.5rem;
  }
}
</style>
