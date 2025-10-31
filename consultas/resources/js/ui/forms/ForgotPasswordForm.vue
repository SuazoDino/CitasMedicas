<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2 class="auth-title">Recuperar contraseña</h2>
      <p class="auth-sub">Ingresa tu correo y te enviaremos un enlace</p>
    </div>

    <div class="field">
      <label class="label">Correo electrónico</label>
      <input class="input" v-model="email" type="email" placeholder="tu@email.com" />
    </div>

    <button class="btn-xl" :disabled="loading" @click="submit">
      {{ loading ? 'Enviando…' : 'Enviar instrucciones' }}
    </button>

    <p v-if="message" class="ok">{{ message }}</p>
    <p v-if="error" class="err">{{ error }}</p>

    <p class="hint">
      <RouterLink class="link" :to="{ name: 'login' }">Regresar al inicio de sesión</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../../auth/api'
import './auth.css'

const email = ref('')
const loading = ref(false)
const message = ref('')
const error = ref('')

async function submit () {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    await api.post('/auth/forgot-password', { email: email.value })
    message.value = 'Si el correo existe en nuestro sistema, enviaremos un enlace para restablecer la contraseña.'
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || 'No pudimos procesar tu solicitud. Inténtalo nuevamente.'
  } finally {
    loading.value = false
  }
}
</script>