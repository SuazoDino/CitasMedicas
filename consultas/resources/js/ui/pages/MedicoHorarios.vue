<template>
  <section class="page-slot horarios-page">
    <div class="container">
      <header class="page-header">
        <div>
          <h1>Configura tus horarios 🗓️</h1>
          <p>Define tu disponibilidad semanal para que los pacientes puedan reservar citas.</p>
        </div>
        <button class="btn-soft" @click="volver">← Volver al panel</button>
      </header>

      <div v-if="pageError" class="alert error">{{ pageError }}</div>

      <div class="horarios-grid" :class="{ loading }">
        <div
          v-for="day in days"
          :key="day.value"
          class="day-column"
        >
          <div class="day-header">{{ day.label }}</div>
          <div class="slots">
            <div
              v-for="slot in grouped[day.value]"
              :key="slot.id"
              class="slot-card"
              :class="{ inactive: !slot.activo }"
            >
              <div class="slot-time">{{ slot.hora_inicio }} – {{ slot.hora_fin }}</div>
              <div class="slot-meta">Duración: {{ slot.slot_min }} min</div>
              <div class="slot-actions">
                <button class="link" @click="editar(slot)">Editar</button>
                <button class="link" @click="toggle(slot)">
                  {{ slot.activo ? 'Desactivar' : 'Activar' }}
                </button>
              </div>
            </div>
            <div v-if="!grouped[day.value].length" class="slot-empty">Sin horarios</div>
          </div>
        </div>
      </div>

      <div class="editor-card">
        <h2>{{ editingId ? 'Editar franja' : 'Nueva franja' }}</h2>
        <form @submit.prevent="guardar">
          <div class="form-grid">
            <label>
              Día de la semana
              <select v-model.number="form.dia_semana">
                <option v-for="day in days" :key="'opt-'+day.value" :value="day.value">
                  {{ day.label }}
                </option>
              </select>
            </label>

            <label>
              Hora inicio
              <input type="time" v-model="form.hora_inicio" required />
            </label>

            <label>
              Hora fin
              <input type="time" v-model="form.hora_fin" required />
            </label>

            <label>
              Duración (minutos)
              <input type="number" min="5" step="5" v-model.number="form.slot_min" required />
            </label>

            <label class="checkbox">
              <input type="checkbox" v-model="form.activo" /> Franja activa
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Guardando…' : (editingId ? 'Actualizar franja' : 'Crear franja') }}
            </button>
            <button v-if="editingId" type="button" class="btn-soft" @click="resetForm">Cancelar edición</button>
          </div>

          <p v-if="formError" class="form-error">{{ formError }}</p>
          <p v-if="feedback" class="form-ok">{{ feedback }}</p>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import axios from 'axios'
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useHorariosStore } from '../stores/horarios'

const router = useRouter()
const horariosStore = useHorariosStore()
const loading = ref(false)
const saving = ref(false)
const horarios = ref([])
const medicoId = ref(null)
const editingId = ref(null)
const pageError = ref('')
const formError = ref('')
const feedback = ref('')

const days = [
  { value: 1, label: 'Lunes' },
  { value: 2, label: 'Martes' },
  { value: 3, label: 'Miércoles' },
  { value: 4, label: 'Jueves' },
  { value: 5, label: 'Viernes' },
  { value: 6, label: 'Sábado' },
  { value: 0, label: 'Domingo' },
]

const form = reactive({
  dia_semana: 1,
  hora_inicio: '09:00',
  hora_fin: '13:00',
  slot_min: 30,
  activo: true,
})

const grouped = computed(() => {
  const map = days.reduce((acc, day) => {
    acc[day.value] = []
    return acc
  }, {})
  horarios.value.forEach(slot => {
    const key = Number(slot.dia_semana)
    if (!map[key]) map[key] = []
    map[key].push(slot)
  })
  Object.values(map).forEach(list => list.sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio)))
  return map
})

const token =
  localStorage.getItem('token') ||
  localStorage.getItem('auth_token') ||
  sessionStorage.getItem('token')
if (token) axios.defaults.headers.common.Authorization = `Bearer ${token}`
axios.defaults.withCredentials = true

function volver () {
  router.push('/medico')
}

function resetForm () {
  editingId.value = null
  Object.assign(form, {
    dia_semana: 1,
    hora_inicio: '09:00',
    hora_fin: '13:00',
    slot_min: 30,
    activo: true,
  })
  formError.value = ''
}

function normalizarSlot (slot) {
  return {
    id: slot.id,
    medico_id: slot.medico_id ?? medicoId.value,
    dia_semana: Number(slot.dia_semana),
    hora_inicio: (slot.hora_inicio || '').toString().slice(0, 5),
    hora_fin: (slot.hora_fin || '').toString().slice(0, 5),
    slot_min: Number(slot.slot_min ?? 30),
    activo: !!slot.activo,
  }
}

async function cargarHorarios (broadcast = false) {
  loading.value = true
  pageError.value = ''
  try {
    const { data } = await axios.get('/api/medico/horarios')
    let items = []
    if (Array.isArray(data)) {
      items = data
    } else {
      medicoId.value = data?.medico_id ?? medicoId.value
      items = Array.isArray(data?.horarios) ? data.horarios : []
    }
    horarios.value = items.map(normalizarSlot)
    if (!medicoId.value && horarios.value.length) {
      medicoId.value = horarios.value[0].medico_id
    }
    if (medicoId.value) {
      horariosStore.setHorarios(medicoId.value, horarios.value)
      if (broadcast) horariosStore.notifyChange(medicoId.value)
    }
  } catch (e) {
    pageError.value = e?.response?.data?.message || 'No se pudo cargar los horarios.'
  } finally {
    loading.value = false
  }
}

function editar (slot) {
  editingId.value = slot.id
  Object.assign(form, {
    dia_semana: Number(slot.dia_semana),
    hora_inicio: slot.hora_inicio,
    hora_fin: slot.hora_fin,
    slot_min: Number(slot.slot_min),
    activo: !!slot.activo,
  })
  formError.value = ''
  feedback.value = ''
}

async function toggle (slot) {
  if (!slot.id) return
  formError.value = ''
  feedback.value = ''
  try {
    if (slot.activo) {
      await axios.delete(`/api/medico/horarios/${slot.id}`)
      feedback.value = 'Franja desactivada.'
    } else {
      await axios.put(`/api/medico/horarios/${slot.id}`, { activo: true })
      feedback.value = 'Franja activada.'
    }
    await cargarHorarios(true)
  } catch (e) {
    formError.value = e?.response?.data?.message || 'No se pudo actualizar la franja.'
  }
}

async function guardar () {
  if (!form.hora_inicio || !form.hora_fin) return
  if (form.hora_fin <= form.hora_inicio) {
    formError.value = 'La hora de fin debe ser posterior a la hora de inicio.'
    return
  }
  saving.value = true
  formError.value = ''
  feedback.value = ''
  const payload = {
    dia_semana: form.dia_semana,
    hora_inicio: form.hora_inicio,
    hora_fin: form.hora_fin,
    slot_min: form.slot_min,
    activo: form.activo,
  }
  try {
    if (editingId.value) {
      await axios.put(`/api/medico/horarios/${editingId.value}`, payload)
      feedback.value = 'Franja actualizada correctamente.'
    } else {
      await axios.post('/api/medico/horarios', payload)
      feedback.value = 'Franja creada correctamente.'
    }
    await cargarHorarios(true)
    if (!formError.value) resetForm()
  } catch (e) {
    formError.value = e?.response?.data?.message || 'No se pudo guardar la franja.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await cargarHorarios(false)
})
</script>

<style scoped>
.horarios-page {
  padding-bottom: 32px;
}
.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 12px 16px 40px;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 18px;
}
.page-header h1 {
  font-size: 26px;
  margin-bottom: 6px;
}
.page-header p {
  margin: 0;
  color: rgba(255,255,255,0.7);
}
.btn-soft {
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.05);
  color: #fff;
  padding: 10px 14px;
  border-radius: 12px;
  cursor: pointer;
}
.horarios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-bottom: 22px;
}
.horarios-grid.loading {
  opacity: 0.6;
}
.day-column {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.day-header {
  font-weight: 800;
  font-size: 16px;
}
.slot-card {
  background: rgba(15,15,30,0.65);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  padding: 10px 12px;
  display: grid;
  gap: 4px;
}
.slot-card.inactive {
  opacity: 0.55;
}
.slot-time {
  font-weight: 700;
}
.slot-meta {
  font-size: 13px;
  color: rgba(255,255,255,0.7);
}
.slot-actions {
  display: flex;
  gap: 10px;
}
.slot-empty {
  font-size: 13px;
  color: rgba(255,255,255,0.55);
}
.link {
  background: none;
  border: none;
  color: #9f7aea;
  cursor: pointer;
  font-weight: 600;
  padding: 0;
}
.editor-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 18px;
  padding: 18px 20px;
}
.editor-card h2 {
  margin: 0 0 12px;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 18px;
}
label {
  display: grid;
  gap: 8px;
  font-weight: 600;
}
select,
input[type="time"],
input[type="number"] {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.18);
  color: #fff;
  padding: 10px 12px;
  border-radius: 12px;
}
.checkbox {
  align-items: center;
  grid-template-columns: auto 1fr;
  gap: 10px;
}
.checkbox input {
  width: 18px;
  height: 18px;
}
.form-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.btn-primary {
  padding: 10px 16px;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  color: #fff;
  background: linear-gradient(135deg,#ff2a88,#7f3bf3);
  box-shadow: 0 12px 26px rgba(127,59,243,0.35);
}
.form-error {
  color: #fca5a5;
  margin-top: 12px;
}
.form-ok {
  color: #86efac;
  margin-top: 12px;
}
.alert.error {
  background: rgba(220,38,38,0.18);
  border: 1px solid rgba(220,38,38,0.45);
  padding: 12px 14px;
  border-radius: 12px;
  margin-bottom: 18px;
}
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  .horarios-grid {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  }
}
</style>
