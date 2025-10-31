<template>
  <section class="max-w-xl mx-auto px-6 py-10 text-white">
    <h1 class="text-3xl font-black mb-1">🗓️ Reservar cita</h1>
    <p class="text-sm text-white/60 mb-6">Completa los datos y confirma.</p>

    <article class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-4">
      <div>
        <label class="block text-sm text-white/70 mb-1">Especialidad</label>
        <select v-model="form.especialidad_id"
                class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10"
                @change="loadMedicos">
          <option value="" disabled>Selecciona…</option>
          <option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm text-white/70 mb-1">Médico</label>
        <select v-model="form.medico_id"
                class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10"
                :disabled="!medicos.length">
          <option value="" disabled>Selecciona…</option>
          <option v-for="m in medicos" :key="m.medico_id" :value="m.medico_id">{{ m.medico }}</option>
        </select>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-white/70 mb-1">Fecha</label>
          <input type="date" v-model="fecha"
                 class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10" />
        </div>
        <div>
          <label class="block text-sm text-white/70 mb-1">Hora</label>
          <input type="time" v-model="hora"
                 class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10" />
        </div>
      </div>

      <div>
        <label class="block text-sm text-white/70 mb-1">Motivo (opcional)</label>
        <input type="text" v-model="form.motivo" placeholder="Chequeo, control, etc."
               class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10" />
      </div>

      <div class="flex items-center gap-3 pt-2">
        <button class="px-4 py-2 rounded-full bg-gradient-to-r from-pink-500 to-violet-600 disabled:opacity-60"
                :disabled="loading || !isCompleto"
                @click="reservar">
          {{ loading ? 'Guardando…' : 'Confirmar reserva' }}
        </button>
        <span v-if="error" class="text-rose-300 text-sm">{{ error }}</span>
        <span v-if="ok" class="px-3 py-1 rounded-full bg-gradient-to-r from-pink-500 to-violet-600 text-sm">
          ¡Reservada!
        </span>
      </div>
    </article>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// Cliente local; NO toca nada global del proyecto
const api = axios.create({ baseURL: '/api' })
const t = localStorage.getItem('token') || localStorage.getItem('auth_token')
if (t) api.defaults.headers.common.Authorization = `Bearer ${t}`

const especialidades = ref([])
const medicos = ref([])
const loading = ref(false)
const ok = ref(false)
const error = ref('')

const form = ref({
  especialidad_id: '',
  medico_id: '',
  motivo: ''
})

const fecha = ref(new Date().toISOString().slice(0,10))
const hora  = ref('10:30')

const isCompleto = computed(() =>
  form.value.especialidad_id && form.value.medico_id && fecha.value && hora.value
)

onMounted(async () => {
  const { data } = await api.get('catalogo/especialidades')
  especialidades.value = data ?? []
})

async function loadMedicos(){
  medicos.value = []
  if (!form.value.especialidad_id) return
  const { data } = await api.get(`catalogo/medicos/${form.value.especialidad_id}`)
  medicos.value = data ?? []
}

function buildStartsAt(){ return `${fecha.value} ${hora.value}:00` }

async function reservar(){
  error.value = ''; ok.value = false; loading.value = true
  try {
    await api.post('citas', {
      medico_id: form.value.medico_id,
      especialidad_id: form.value.especialidad_id,
      starts_at: buildStartsAt(),
      motivo: form.value.motivo || null
    })
    ok.value = true
  } catch(e){
    error.value = e.response?.data?.message || 'Error al reservar'
  } finally { loading.value = false }
}
</script>
