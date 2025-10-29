<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2 class="auth-title">Crear cuenta (Médico)</h2>
      <p class="auth-sub">Verificación posterior por administración</p>
    </div>

    <div class="auth-tabs">
      <button class="tab-btn" @click="router.push('/login')">Iniciar Sesión</button>
      <button class="tab-btn active">Registrarse</button>
    </div>

    <div class="field">
      <label class="label">Nombre completo</label>
      <input class="input" v-model="f.name" placeholder="Dra. María Silva" />
    </div>

    <div class="field">
      <label class="label">Correo</label>
      <input class="input" type="email" v-model="f.email" placeholder="medico@ejemplo.com" />
    </div>

    <div class="field">
      <label class="label">Contraseña</label>
      <input class="input" type="password" v-model="f.password" placeholder="••••••••" />
    </div>

    <div class="field">
      <label class="label">Tipo de documento</label>
      <input class="input" v-model="f.id_doc_tipo" placeholder="DNI / CE / PASS" />
    </div>

    <div class="field">
      <label class="label">Nro. documento</label>
      <input class="input" v-model="f.id_doc_numero" placeholder="12345678" />
    </div>

    <div class="field">
      <label class="label">Tipo licencia</label>
      <input class="input" v-model="f.lic_tipo" placeholder="CMP" />
    </div>

    <div class="field">
      <label class="label">Nro. licencia</label>
      <input class="input" v-model="f.lic_numero" placeholder="123456" />
    </div>

    <div class="field">
      <label class="label">País licencia (ISO-2/3)</label>
      <input class="input" v-model="f.lic_pais" placeholder="PE" />
    </div>

    <button class="btn-xl" :disabled="loading" @click="submit">
      {{ loading ? 'Creando…' : 'Crear cuenta' }}
    </button>

    <p v-if="error" class="err">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../auth/api'
import './auth.css'
import { auth } from '../../auth/store'

const router = useRouter()
const f = ref({
  name: '', email: '', password: '',
  id_doc_tipo: 'DNI', id_doc_numero: '',
  lic_tipo: 'CMP', lic_numero: '', lic_pais: 'PE'
})
const loading = ref(false)
const error = ref('')

async function submit () {
  loading.value = true; error.value = ''
  try {
    const { data } = await api.post('/auth/register/medico', f.value)
    auth.token = data.token
    auth.roles = data.roles ?? []
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`

    if (auth.roles.includes('medico')) {
      router.replace({ name: 'medico.home' })
    } else if (auth.roles.includes('paciente')) {
      router.replace({ name: 'paciente.home' })
    } else {
      router.replace('/')
    }
  } catch (e) {
    error.value = e?.response?.data?.message || 'No pudimos registrar.'
  } finally {
    loading.value = false
  }
}
</script>
