<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2 class="auth-title">Restablecer contraseña</h2>
      <p class="auth-sub">Ingresa una nueva contraseña segura</p>
    </div>

    <div class="field">
      <label class="label">Correo electrónico</label>
      <input class="input" v-model="email" type="email" placeholder="tu@email.com" />
    </div>

    <div class="field">
      <label class="label">Nueva contraseña</label>
      <input class="input" v-model="password" type="password" placeholder="••••••••" />
    </div>

    <div class="field">
      <label class="label">Confirmar contraseña</label>
      <input class="input" v-model="passwordConfirmation" type="password" placeholder="••••••••" />
    </div>

    <button class="btn-xl" :disabled="loading" @click="submit">
      {{ loading ? 'Guardando…' : 'Actualizar contraseña' }}
    </button>

    <p v-if="message" class="ok">{{ message }}</p>
    <p v-if="error" class="err">{{ error }}</p>

    <p class="hint">
      <RouterLink class="link" :to="{ name: 'login' }">Volver al inicio de sesión</RouterLink>
    </p>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../auth/api'
import './auth.css'

const route = useRoute()

const token = computed(() => (typeof route.query.token === 'string' ? route.query.token : ''))
const email = ref(typeof route.query.email === 'string' ? route.query.email : '')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const message = ref('')
const error = ref('')

async function submit () {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    if (!token.value) {
      throw new Error('Falta el token de restablecimiento. Vuelve a solicitar el enlace.');
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