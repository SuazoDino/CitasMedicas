<template>
  <div class="auth-card">
    <div class="auth-header">
      <h2 class="auth-title">Crear cuenta (Médico)</h2>
      <p class="auth-sub">Unete para gestionar tus citas</p>
    </div>

    <div class="auth-tabs">
      <RouterLink
        class="tab-btn"
        :to="{ name: 'login', query: { as: 'medico' } }"
      >
        Iniciar Sesión
      </RouterLink>
      <button class="tab-btn active" type="button">Registrarse</button>
    </div>

    <div class="field">
      <label class="label">Nombre completo</label>
      <input class="input" v-model="f.name" placeholder="Dra. Ana Torres" />
    </div>

    <div class="field">
      <label class="label">Correo</label>
      <input class="input" type="email" v-model="f.email" placeholder="ana@ejemplo.com" />
    </div>

    <div class="field">
      <label class="label">Contraseña</label>
      <input class="input" type="password" v-model="f.password" placeholder="••••••••" />
    </div>

    <div class="field">
      <label class="label">Tipo de Documento</label>
      <input class="input" v-model="f.id_doc_tipo" placeholder="DNI" />
    </div>

    <div class="field">
      <label class="label">Numero de Documento</label>
      <input class="input" v-model="f.id_doc_numero" placeholder="12345678" />
    </div>

    <div class="field">
      <label class="label">Tipo de Licencia</label>
      <input class="input" v-model="f.lic_tipo" placeholder="CMP" />
    </div>

    <div class="field">
      <label class="label">Numero de Licencia</label>
      <input class="input" v-model="f.lic_numero" placeholder="123456" />
    </div>

    <div class="field">
      <label class="label">País de Lincencia</label>
      <input class="input" v-model="f.lic_pais" placeholder="PE" />
    </div>

    <button class="btn-xl" :disabled="loading" @click="submit">
      {{ loading ? 'Creando…' : 'Crear cuenta' }}
    </button>

    <p v-if="error" class="err">{{ error }}</p>
    <div class="btn-row" style="margin-top:12px">
      <div class="hr"></div><div class="hrtext">¿Eres paciente?</div><div class="hr"></div>
    </div>
    <div style="text-align:center;margin-top:10px">
      <a class="link" @click.prevent="router.push({ name: 'register.paciente' })">Regístrate como paciente</a>
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
const f = ref({
  name: '',
  email: '',
  password: '',
  id_doc_tipo: '',
  id_doc_numero: '',
  lic_tipo: '',
  lic_numero: '',
  lic_pais: '',
})
const loading = ref(false)
const error = ref('')

async function submit () {
  loading.value = true; error.value = ''
  try {
    const { data } = await api.post('/auth/register/medico', f.value)
    auth.token = data.token
    auth.roles = data.roles ?? []
    auth.name = data?.user?.name ?? ''

    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null

    if (redirect) {
      router.replace(redirect)
    } else if (auth.roles.includes('medico')) {
      router.replace({ name: 'medico.home' })
    } else if (auth.roles.includes('paciente')) {
      router.replace({ name: 'paciente.home' })
    } else {
      router.replace({ name: 'login' })
    }
  } catch (e) {
    error.value = e?.response?.data?.message || 'No pudimos registrar.'
  } finally {
    loading.value = false
  }
}
</script>
