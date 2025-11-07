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
          :class="{ 'has-slots': grouped[day.value].length > 0, 'selected-day': form.dia_semana === day.value }"
        >
          <div class="day-header">
            <span class="day-icon">{{ getDayIcon(day.value) }}</span>
            <span>{{ day.label }}</span>
            <span v-if="grouped[day.value].length > 0" class="day-count">
              {{ grouped[day.value].length }}
            </span>
          </div>
          <div class="slots">
            <div
              v-for="slot in grouped[day.value]"
              :key="slot.id"
              class="slot-card"
              :class="{ 
                inactive: !slot.activo,
                editing: editingId === slot.id
              }"
            >
              <div class="slot-status" :class="{ active: slot.activo }">
                <span class="status-dot"></span>
                <span class="status-text">{{ slot.activo ? 'Activo' : 'Inactivo' }}</span>
              </div>
              <div class="slot-time">
                <span class="time-icon">🕐</span>
                <span>{{ slot.hora_inicio }} – {{ slot.hora_fin }}</span>
              </div>
              <div class="slot-meta">
                <span class="meta-icon">⏱️</span>
                <span>{{ slot.slot_min }} min por cita</span>
              </div>
              <div class="slot-actions">
                <button class="btn-edit" @click="editar(slot)" title="Editar horario">
                  ✏️ Editar
                </button>
                <button 
                  class="btn-toggle" 
                  :class="{ 'btn-activate': !slot.activo }"
                  @click="toggle(slot)"
                  :title="slot.activo ? 'Desactivar horario' : 'Activar horario'"
                >
                  {{ slot.activo ? '⏸️ Desactivar' : '▶️ Activar' }}
                </button>
              </div>
            </div>
            <div v-if="!grouped[day.value].length" class="slot-empty">
              <span class="empty-icon">📅</span>
              <span>Sin horarios</span>
            </div>
          </div>
        </div>
      </div>

      <div class="editor-card" :class="{ editing: editingId }">
        <div class="editor-header">
          <h2>
            <span class="editor-icon">{{ editingId ? '✏️' : '➕' }}</span>
            {{ editingId ? 'Editar franja horaria' : 'Nueva franja horaria' }}
          </h2>
          <p v-if="!editingId" class="editor-hint">
            Completa los campos para crear un nuevo horario de atención
          </p>
        </div>
        <form @submit.prevent="guardar">
          <div class="form-grid">
            <label class="form-field">
              <span class="field-label">
                <span class="field-icon">📅</span>
                Día de la semana
              </span>
              <select v-model.number="form.dia_semana" class="form-input">
                <option v-for="day in days" :key="'opt-'+day.value" :value="day.value">
                  {{ getDayIcon(day.value) }} {{ day.label }}
                </option>
              </select>
            </label>

            <label class="form-field">
              <span class="field-label">
                <span class="field-icon">🕐</span>
                Hora de inicio
              </span>
              <input 
                type="time" 
                v-model="form.hora_inicio" 
                class="form-input"
                required 
              />
            </label>

            <label class="form-field">
              <span class="field-label">
                <span class="field-icon">🕐</span>
                Hora de fin
              </span>
              <input 
                type="time" 
                v-model="form.hora_fin" 
                class="form-input"
                required 
              />
            </label>

            <label class="form-field">
              <span class="field-label">
                <span class="field-icon">⏱️</span>
                Duración por cita (minutos)
              </span>
              <input 
                type="number" 
                min="5" 
                max="480"
                step="5" 
                v-model.number="form.slot_min" 
                class="form-input"
                required 
              />
              <span class="field-hint">Mínimo 5, máximo 480 minutos</span>
            </label>

            <label class="form-field checkbox-field">
              <div class="checkbox-wrapper">
                <input 
                  type="checkbox" 
                  v-model="form.activo" 
                  class="checkbox-input"
                  id="activo-checkbox"
                />
                <label for="activo-checkbox" class="checkbox-label">
                  <span class="checkbox-custom"></span>
                  <span class="checkbox-text">
                    <span class="field-icon">✅</span>
                    Franja activa (visible para pacientes)
                  </span>
                </label>
              </div>
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="saving || loading">
              <span v-if="saving" class="btn-spinner">⏳</span>
              <span v-else>{{ editingId ? '💾 Actualizar franja' : '✨ Crear franja' }}</span>
            </button>
            <button 
              v-if="editingId" 
              type="button" 
              class="btn-soft" 
              @click="resetForm"
              :disabled="saving"
            >
              ❌ Cancelar edición
            </button>
          </div>

          <div v-if="formError" class="form-message error">
            <span class="message-icon">⚠️</span>
            <span>{{ formError }}</span>
          </div>
          <div v-if="feedback" class="form-message success">
            <span class="message-icon">✅</span>
            <span>{{ feedback }}</span>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import api from '../../services/api'
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

// La instancia 'api' ya maneja la autenticación automáticamente

function getDayIcon (dayValue) {
  const icons = {
    0: '☀️', // Domingo
    1: '📅', // Lunes
    2: '📅', // Martes
    3: '📅', // Miércoles
    4: '📅', // Jueves
    5: '📅', // Viernes
    6: '🎉', // Sábado
  }
  return icons[dayValue] || '📅'
}

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
  feedback.value = ''
  // Scroll suave al formulario
  setTimeout(() => {
    const editor = document.querySelector('.editor-card')
    if (editor) {
      editor.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }
  }, 100)
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
    const { data } = await api.get('/medico/horarios')
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
  // Scroll suave al formulario
  setTimeout(() => {
    const editor = document.querySelector('.editor-card')
    if (editor) {
      editor.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }
  }, 100)
}

async function toggle (slot) {
  if (!slot.id) return
  formError.value = ''
  feedback.value = ''
  try {
    if (slot.activo) {
      await api.delete(`/medico/horarios/${slot.id}`)
      feedback.value = 'Franja desactivada.'
    } else {
      await api.put(`/medico/horarios/${slot.id}`, { activo: true })
      feedback.value = 'Franja activada.'
    }
    await cargarHorarios(true)
    setTimeout(() => { feedback.value = '' }, 3000)
  } catch (e) {
    formError.value = e?.response?.data?.message || 'No se pudo actualizar la franja.'
    setTimeout(() => { formError.value = '' }, 5000)
  }
}

async function guardar () {
  if (!form.hora_inicio || !form.hora_fin) return
  if (form.hora_fin <= form.hora_inicio) {
    formError.value = 'La hora de fin debe ser posterior a la hora de inicio.'
    setTimeout(() => { formError.value = '' }, 5000)
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
      await api.put(`/medico/horarios/${editingId.value}`, payload)
      feedback.value = 'Franja actualizada correctamente.'
    } else {
      await api.post('/medico/horarios', payload)
      feedback.value = 'Franja creada correctamente.'
    }
    await cargarHorarios(true)
    if (!formError.value) {
      // Limpiar feedback después de 3 segundos
      setTimeout(() => { feedback.value = '' }, 3000)
      resetForm()
    }
  } catch (e) {
    formError.value = e?.response?.data?.message || 'No se pudo guardar la franja.'
    setTimeout(() => { formError.value = '' }, 5000)
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
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0118 0%, #1a0a2e 100%);
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px 20px 40px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 32px;
}

.page-header h1 {
  font-size: 32px;
  margin-bottom: 8px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-header p {
  margin: 0;
  color: rgba(255,255,255,0.75);
  font-size: 15px;
}

.btn-soft {
  border: 1px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.08);
  color: #fff;
  padding: 12px 18px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 600;
}

.btn-soft:hover {
  background: rgba(255,255,255,0.12);
  border-color: rgba(255,255,255,0.3);
}

.horarios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 32px;
}

.horarios-grid.loading {
  opacity: 0.6;
  pointer-events: none;
}

.day-column {
  background: rgba(255,255,255,0.06);
  border: 2px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: all 0.3s ease;
  min-height: 200px;
}

.day-column.has-slots {
  border-color: rgba(127,59,243,0.4);
  background: rgba(127,59,243,0.08);
}

.day-column.selected-day {
  border-color: rgba(255,42,136,0.6);
  background: rgba(255,42,136,0.1);
  box-shadow: 0 8px 24px rgba(255,42,136,0.2);
}

.day-header {
  font-weight: 800;
  font-size: 18px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(255,255,255,0.1);
}

.day-icon {
  font-size: 20px;
}

.day-count {
  margin-left: auto;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.slots {
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}

.slot-card {
  background: rgba(15,15,30,0.7);
  border: 2px solid rgba(255,255,255,0.1);
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.slot-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  opacity: 0;
  transition: opacity 0.3s;
}

.slot-card:not(.inactive)::before {
  opacity: 1;
}

.slot-card:hover {
  transform: translateY(-2px);
  border-color: rgba(127,59,243,0.4);
  box-shadow: 0 8px 20px rgba(127,59,243,0.2);
}

.slot-card.inactive {
  opacity: 0.5;
  border-color: rgba(255,255,255,0.05);
}

.slot-card.inactive::before {
  background: rgba(255,255,255,0.2);
}

.slot-card.editing {
  border-color: rgba(255,42,136,0.8);
  box-shadow: 0 0 0 3px rgba(255,42,136,0.2);
  background: rgba(255,42,136,0.1);
}

.slot-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 4px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,0.3);
  transition: all 0.3s;
}

.slot-status.active .status-dot {
  background: #86efac;
  box-shadow: 0 0 8px rgba(134,239,172,0.6);
}

.status-text {
  color: rgba(255,255,255,0.7);
}

.slot-status.active .status-text {
  color: #86efac;
}

.slot-time {
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.time-icon {
  font-size: 18px;
}

.slot-meta {
  font-size: 13px;
  color: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  gap: 6px;
}

.meta-icon {
  font-size: 14px;
}

.slot-actions {
  display: flex;
  gap: 8px;
  margin-top: 4px;
  flex-wrap: wrap;
}

.btn-edit,
.btn-toggle {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  color: #fff;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s;
  flex: 1;
  min-width: 80px;
}

.btn-edit:hover {
  background: rgba(127,59,243,0.3);
  border-color: rgba(127,59,243,0.5);
}

.btn-toggle:hover {
  background: rgba(255,255,255,0.12);
}

.btn-toggle.btn-activate:hover {
  background: rgba(134,239,172,0.2);
  border-color: rgba(134,239,172,0.4);
}

.slot-empty {
  font-size: 13px;
  color: rgba(255,255,255,0.5);
  text-align: center;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.empty-icon {
  font-size: 32px;
  opacity: 0.5;
}

.editor-card {
  background: rgba(255,255,255,0.06);
  border: 2px solid rgba(255,255,255,0.12);
  border-radius: 20px;
  padding: 24px;
  transition: all 0.3s;
}

.editor-card.editing {
  border-color: rgba(255,42,136,0.5);
  box-shadow: 0 0 0 3px rgba(255,42,136,0.1);
}

.editor-header {
  margin-bottom: 20px;
}

.editor-header h2 {
  margin: 0 0 8px;
  font-size: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.editor-icon {
  font-size: 28px;
}

.editor-hint {
  margin: 0;
  color: rgba(255,255,255,0.65);
  font-size: 14px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 14px;
  color: rgba(255,255,255,0.9);
}

.field-icon {
  font-size: 16px;
}

.form-input {
  background: rgba(255,255,255,0.08);
  border: 2px solid rgba(255,255,255,0.15);
  color: #fff;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 15px;
  transition: all 0.2s;
  width: 100%;
}

.form-input:focus {
  outline: none;
  border-color: rgba(127,59,243,0.6);
  background: rgba(255,255,255,0.1);
  box-shadow: 0 0 0 3px rgba(127,59,243,0.1);
}

.field-hint {
  font-size: 12px;
  color: rgba(255,255,255,0.5);
  margin-top: -4px;
}

.checkbox-field {
  grid-column: 1 / -1;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
}

.checkbox-input {
  display: none;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  user-select: none;
}

.checkbox-custom {
  width: 22px;
  height: 22px;
  border: 2px solid rgba(255,255,255,0.3);
  border-radius: 6px;
  background: rgba(255,255,255,0.05);
  transition: all 0.2s;
  position: relative;
}

.checkbox-input:checked + .checkbox-label .checkbox-custom {
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  border-color: transparent;
}

.checkbox-input:checked + .checkbox-label .checkbox-custom::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #fff;
  font-size: 14px;
  font-weight: bold;
}

.checkbox-text {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.form-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 24px;
}

.btn-primary {
  padding: 14px 24px;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  color: #fff;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  box-shadow: 0 8px 24px rgba(127,59,243,0.4);
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(127,59,243,0.5);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.form-message {
  margin-top: 16px;
  padding: 12px 16px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-message.error {
  background: rgba(220,38,38,0.15);
  border: 1px solid rgba(220,38,38,0.4);
  color: #fca5a5;
}

.form-message.success {
  background: rgba(34,197,94,0.15);
  border: 1px solid rgba(34,197,94,0.4);
  color: #86efac;
}

.message-icon {
  font-size: 18px;
}

.alert.error {
  background: rgba(220,38,38,0.18);
  border: 2px solid rgba(220,38,38,0.45);
  padding: 14px 18px;
  border-radius: 12px;
  margin-bottom: 24px;
  color: #fca5a5;
  font-weight: 600;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .horarios-grid {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .slot-actions {
    flex-direction: column;
  }
  
  .btn-edit,
  .btn-toggle {
    width: 100%;
  }
}
</style>
