<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2 class="auth-title">Crear cuenta (Paciente)</h2>
      <p class="auth-sub">Regístrate para reservar citas</p>
    </div>

    <div class="auth-tabs">
      <button class="tab-btn" @click="router.push({ name: 'login' })">Iniciar Sesión</button>
      <button class="tab-btn active">Registrarse</button>
    </div>

    <div class="field">
      <label class="label">Nombre completo</label>
      <input class="input" v-model="f.name" placeholder="Juan Pérez" />
    </div>

    <div class="field">
      <label class="label">Correo</label>
      <input class="input" type="email" v-model="f.email" placeholder="juan@ejemplo.com" />
    </div>

    <div class="field">
      <label class="label">Contraseña</label>
      <input class="input" type="password" v-model="f.password" placeholder="••••••••" />
    </div>

    <button class="btn-xl" :disabled="loading" @click="submit">
      {{ loading ? 'Creando…' : 'Crear cuenta' }}
    </button>

    <p v-if="error" class="err">{{ error }}</p>

    <div class="btn-row" style="margin-top:12px">
      <div class="hr"></div><div class="hrtext">¿Eres médico?</div><div class="hr"></div>
    </div>
    <div style="text-align:center;margin-top:10px">
      <a class="link" @click.prevent="router.push({ name: 'register.medico' })">Regístrate como médico</a>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../auth/api'
import './auth.css'
import { auth } from '../../auth/store'

const router = useRouter()
const route = useRoute()
const f = ref({ name: '', email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function submit () {
  loading.value = true; error.value = ''
  try {

    const { data } = await api.post('/auth/register/paciente', f.value)
    auth.token = data.token
    auth.roles = data.roles ?? []
    auth.name = data?.user?.name ?? ''

    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`

    // decide destino
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null

    if (redirect) {
      router.replace(redirect)
    } else if (auth.roles.includes('paciente')) {
      router.replace({ name: 'paciente.home' })
    } else if (auth.roles.includes('medico')) {
      router.replace({ name: 'medico.home' })
    } else {
      router.replace({ name: 'login' }) // fallback
    }
  } catch (e) {
    error.value = e?.response?.data?.message || 'No pudimos registrar.'
  } finally {
    loading.value = false
  }
}

</script>
