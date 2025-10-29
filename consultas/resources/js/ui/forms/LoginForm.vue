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

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function doLogin () {
  loading.value = true
  error.value = ''
  try {
    // 1) Login (devuelve token y user)
    const { data } = await api.post('/auth/login', {
      email: email.value,
      password: password.value
    })

    // 2) Guarda token y configúralo en axios y en tu instancia api
    const token = data?.token
    if (!token) throw new Error('No se recibió token')

    localStorage.setItem('token', token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    if (api?.defaults) api.defaults.headers.common['Authorization'] = `Bearer ${token}`

    // 3) Consulta /me para obtener roles y decidir el destino
    const { data: meRes } = await api.get('/auth/me')
    const roles = meRes?.roles ?? []

    if (roles.includes('medico')) {
      router.push('/medico')
    } else if (roles.includes('paciente')) {
      router.push('/me')         // o tu página de paciente si ya la tienes
    } else {
      router.push('/')           // fallback seguro
    }
  } catch (e) {
    error.value = e?.response?.data?.error || 'No pudimos iniciar sesión.'
  } finally {
    loading.value = false
  }
}
</script>
