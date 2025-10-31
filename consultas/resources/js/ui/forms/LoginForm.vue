<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2 class="auth-title">Bienvenido de nuevo</h2>
      <p class="auth-sub">Ingresa a tu cuenta para continuar</p>
    </div>

    <div class="auth-tabs">
      <button class="tab-btn active">Iniciar Sesión</button>
      <button class="tab-btn" @click="router.push('/register/paciente')">Registrarse</button>
    </div>

    <div class="field">
      <label class="label">Correo Electrónico</label>
      <input class="input" v-model="email" type="email" placeholder="tu@email.com" />
    </div>

    <div class="field">
      <label class="label">Contraseña</label>
      <input class="input" v-model="password" type="password" placeholder="••••••••" />
    </div>

    <div style="text-align:right;margin-top:4px">
      <a class="link" href="#">¿Olvidaste tu contraseña?</a>
    </div>

    <button class="btn-xl" :disabled="loading" @click="doLogin">
      {{ loading ? 'Ingresando…' : 'Iniciar Sesión' }}
    </button>

    <p v-if="error" class="err">{{ error }}</p>

    <div class="btn-row">
      <div class="hr"></div><div class="hrtext">O continúa con</div><div class="hr"></div>
    </div>
    <div class="sso-row">
      <div class="sso">🌐</div>
      <div class="sso">📱</div>
      <div class="sso">G</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import api from '../../auth/api'          // tu axios con baseURL '/api'
import './auth.css'
import { auth } from '../../auth/store'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function doLogin () {
  loading.value = true
  error.value = ''
  try {
    // 1) Login
    const { data } = await api.post('/auth/login', {
      email: email.value,
      password: password.value
    })

    // 2) Validar y persistir credenciales
    const token = data?.token
    if (!token) throw new Error('Sin token del servidor')
    localStorage.setItem('token', token)
    localStorage.setItem('roles', JSON.stringify(data?.roles || []))

    // (opcional) Deja el header en tu instancia axios "api"
    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${token}`

    // 3) Redirección “a prueba de todo” SIN router
    const roles = data?.roles || []
    const dest = roles.includes('medico') ? '/medico' : '/me'
    window.location.replace(dest)   // <- sin router, no hay forma de que “no abra”
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || 'No pudimos iniciar sesión.'
  } finally {
    loading.value = false
  }
}


</script>
