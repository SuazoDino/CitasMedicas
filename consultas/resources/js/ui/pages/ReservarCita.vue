<template>
  <section class="page-slot reserva-page">
    <div class="container reserva">
      <header class="page-header">
        <div>
          <h1 class="page-title">
            <span class="title-icon">🩺</span>
            Reservar Cita
          </h1>
          <p class="page-subtitle">Selecciona tu especialidad, médico y horario disponible</p>
        </div>
      </header>

      <!-- Paso 1: Especialidad -->
      <div class="step-card" :class="{ active: !especialidadId, completed: especialidadId }">
        <div class="step-header">
          <span class="step-number">1</span>
          <h2 class="step-title">Selecciona la especialidad</h2>
        </div>
        <div class="field-wrapper">
          <label class="field-label">
            <span class="field-icon">🏥</span>
            Especialidad médica
          </label>
          <select v-model="especialidadId" @change="cargarMedicos" class="form-select">
            <option value="">Selecciona una especialidad…</option>
            <option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
      </div>

      <!-- Paso 2: Médico -->
      <div class="step-card" v-if="especialidadId" :class="{ active: !medicoId, completed: medicoId }">
        <div class="step-header">
          <span class="step-number">2</span>
          <h2 class="step-title">Elige tu médico</h2>
        </div>
        <div v-if="medicos.length" class="med-grid">
          <button
            v-for="m in medicos"
            :key="m.id"
            class="med-card"
            :class="{ active: m.id === medicoId }"
            @click="selectMedico(m.id)"
          >
            <div class="med-avatar">
              <span class="avatar-icon">👨‍⚕️</span>
            </div>
            <div class="med-info">
            <div class="med-name">{{ m.nombre }}</div>
              <div class="med-badge">Disponible</div>
            </div>
            <div v-if="m.id === medicoId" class="med-check">✓</div>
          </button>
        </div>
        <div v-else class="empty-state">
          <span class="empty-icon">😔</span>
          <p>No hay médicos disponibles para esta especialidad.</p>
        </div>
      </div>

      <!-- Paso 3: Fecha y Horario -->
      <div class="step-card" v-if="medicoId" :class="{ active: !starts_at, completed: starts_at }">
        <div class="step-header">
          <span class="step-number">3</span>
          <h2 class="step-title">Selecciona fecha y horario</h2>
        </div>
        <div class="date-selector">
          <div class="date-field">
            <label class="field-label">
              <span class="field-icon">📅</span>
              Fecha de la cita
            </label>
            <input 
              type="date" 
              v-model="fecha" 
              :min="minDate"
              @change="cargarSlots"
              class="form-input"
            />
          </div>
          <button class="btn-refresh" @click="cargarSlots" title="Actualizar horarios">
            🔄 Actualizar
          </button>
        </div>

        <div v-if="slotsDelDia.length" class="slots-container">
          <div class="slots-header">
            <span class="slots-count">{{ slotsDelDia.length }} horario{{ slotsDelDia.length !== 1 ? 's' : '' }} disponible{{ slotsDelDia.length !== 1 ? 's' : '' }}</span>
          </div>
        <div class="slots-grid">
          <button
            v-for="s in slotsDelDia"
            :key="s.start"
              class="slot-btn"
              :class="{ active: s.start === starts_at }"
            @click="pickSlot(s)"
            >
              <span class="slot-time">{{ toTime(s.start) }}</span>
            </button>
          </div>
        </div>

        <div v-else class="empty-state">
          <span class="empty-icon">📅</span>
          <p>No hay horarios disponibles para esta fecha.</p>
          <p class="empty-hint">Intenta seleccionar otra fecha o contacta al médico.</p>
        </div>
      </div>

      <!-- Confirmación -->
      <div class="step-card confirmation-card" v-if="starts_at">
        <div class="step-header">
          <span class="step-number">✓</span>
          <h2 class="step-title">Confirma tu cita</h2>
        </div>
        <div class="resume-card">
          <div class="resume-item">
            <span class="resume-icon">👨‍⚕️</span>
            <div class="resume-content">
              <span class="resume-label">Médico</span>
              <span class="resume-value">{{ medicos.find(x => x.id === medicoId)?.nombre }}</span>
            </div>
          </div>
          <div class="resume-item">
            <span class="resume-icon">📅</span>
            <div class="resume-content">
              <span class="resume-label">Fecha</span>
              <span class="resume-value">{{ toDate(starts_at) }}</span>
            </div>
          </div>
          <div class="resume-item">
            <span class="resume-icon">🕐</span>
            <div class="resume-content">
              <span class="resume-label">Hora</span>
              <span class="resume-value">{{ toTime(starts_at) }}</span>
            </div>
          </div>
        </div>
        <div class="cta-buttons">
          <button class="btn-primary" :disabled="saving" @click="crearCita">
            <span v-if="saving" class="btn-spinner">⏳</span>
            <span v-else>✨ Confirmar Reserva</span>
          </button>
          <button class="btn-secondary" @click="starts_at = null">← Cambiar horario</button>
        </div>
        <div v-if="error" class="message error">
          <span class="message-icon">⚠️</span>
          <span>{{ error }}</span>
        </div>
        <div v-if="ok" class="message success">
          <span class="message-icon">✅</span>
          <span>¡Cita creada exitosamente! Redirigiendo...</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../auth/api'
import { useHorariosStore } from '../stores/horarios'

const router = useRouter()
const route = useRoute()
const especialidades = ref([])
const especialidadId = ref('')
const medicos = ref([])
const medicoId = ref(null)
const minDate = new Date().toISOString().slice(0,10) // Fecha mínima: hoy
const fecha = ref(new Date().toISOString().slice(0,10))
const slots = ref([])
const starts_at = ref(null)
const saving = ref(false)
const error = ref('')
const ok = ref(false)
const horariosStore = useHorariosStore()

function toTime(iso){ return new Date(iso).toTimeString().slice(0,5) }
function toDate(iso){ return new Date(iso).toLocaleDateString() }

const slotsDelDia = computed(()=>{
  const hoy = new Date().toISOString().slice(0,10)
  const fechaSeleccionada = fecha.value
  // Solo mostrar slots disponibles (no tomados) y que sean para hoy o fechas futuras
  return slots.value.filter(s => {
    const slotDate = s.start.slice(0,10)
    return slotDate === fechaSeleccionada && 
           slotDate >= hoy && 
           !s.taken
  })
})

async function cargarEspecialidades(){
  const { data } = await api.get('public/especialidades')
  especialidades.value = Array.isArray(data) ? data : []
}
async function cargarMedicos(){
  if(!especialidadId.value){
    medicos.value = []
    return
  }
  const { data } = await api.get('public/medicos', { params:{ especialidad_id: especialidadId.value } })
  medicos.value = Array.isArray(data) ? data : []
  medicoId.value = null; starts_at.value = null; slots.value = []
}
function selectMedico(id){ medicoId.value = id; cargarSlots() }

async function cargarSlots(){
  if(!medicoId.value) return
  // Asegurar que la fecha seleccionada no sea pasada
  const hoy = new Date().toISOString().slice(0,10)
  if(fecha.value < hoy) {
    fecha.value = hoy
  }
  const desde = fecha.value
  const d2 = new Date(fecha.value); d2.setDate(d2.getDate()+6)
  const hasta = d2.toISOString().slice(0,10)
  const { data } = await api.get(`public/medicos/${medicoId.value}/slots`, { params:{ desde, hasta } })
  slots.value = Array.isArray(data) ? data : []
  starts_at.value = null
}
function pickSlot(s){ starts_at.value = s.start }

watch(
  () => horariosStore.versionOf(medicoId.value || 0),
  (version, previous) => {
    if (!medicoId.value) return
    if (typeof previous === 'undefined') return
    if (version !== previous) cargarSlots()
  }
)

async function crearCita(){
  if(!medicoId.value || !starts_at.value) return
  saving.value = true; error.value = ''; ok.value = false
  try{
    const payload = {
      medico_id: medicoId.value,
      especialidad_id: especialidadId.value || null,
      starts_at: starts_at.value,
    }
    const { data } = await api.post('paciente/citas', payload)
    if (data?.id) {
      ok.value = true
      // Ir al panel del paciente después de mostrar el mensaje
      setTimeout(()=> { 
        ok.value = false
        router.replace({ name: 'paciente.home' }) 
      }, 2000)
    }else{
      error.value = data?.message || 'No se pudo crear la cita.'
      setTimeout(() => { error.value = '' }, 5000)
    }
  }catch(e){
    error.value = e?.response?.data?.message || 'No se pudo crear la cita.'
    setTimeout(() => { error.value = '' }, 5000)
  }finally{ saving.value = false }
}

onMounted(async ()=>{
  await cargarEspecialidades()
  
  // Preseleccionar desde query params
  if (route.query.medico_id) {
    medicoId.value = Number(route.query.medico_id)
    if (route.query.especialidad_id) {
      especialidadId.value = String(route.query.especialidad_id)
      await cargarMedicos()
    }
    await cargarSlots()
  } else if (route.query.especialidad_id) {
    especialidadId.value = String(route.query.especialidad_id)
    await cargarMedicos()
  }
})
</script>

<style scoped>
.reserva-page {
  padding: 24px 20px 40px;
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0118 0%, #1a0a2e 100%);
}

.container.reserva {
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
  text-align: center;
}

.page-title {
  font-size: 36px;
  font-weight: 800;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.title-icon {
  font-size: 40px;
  -webkit-text-fill-color: initial;
}

.page-subtitle {
  color: rgba(255,255,255,0.75);
  font-size: 16px;
  margin: 0;
}

.step-card {
  background: rgba(255,255,255,0.06);
  border: 2px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  padding: 24px;
  margin-bottom: 20px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.step-card::before {
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

.step-card.active::before,
.step-card.completed::before {
  opacity: 1;
}

.step-card.completed {
  border-color: rgba(127,59,243,0.4);
  background: rgba(127,59,243,0.08);
}

.step-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px solid rgba(255,255,255,0.1);
}

.step-number {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 18px;
  flex-shrink: 0;
}

.step-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
}

.field-wrapper {
  display: flex;
  flex-direction: column;
  gap: 10px;
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
  font-size: 18px;
}

.form-select,
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

.form-select:focus,
.form-input:focus {
  outline: none;
  border-color: rgba(127,59,243,0.6);
  background: rgba(255,255,255,0.1);
  box-shadow: 0 0 0 3px rgba(127,59,243,0.1);
}

.med-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 12px;
}

.med-card {
  display: flex;
  align-items: center;
  gap: 14px;
  text-align: left;
  cursor: pointer;
  background: rgba(15,15,30,0.7);
  border: 2px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  padding: 16px;
  transition: all 0.3s ease;
  position: relative;
}

.med-card:hover {
  transform: translateY(-2px);
  border-color: rgba(127,59,243,0.4);
  box-shadow: 0 8px 20px rgba(127,59,243,0.2);
}

.med-card.active {
  border-color: rgba(255,42,136,0.8);
  background: rgba(255,42,136,0.1);
  box-shadow: 0 0 0 3px rgba(255,42,136,0.2);
}

.med-avatar {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  flex-shrink: 0;
}

.avatar-icon {
  font-size: 24px;
}

.med-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.med-name {
  font-weight: 700;
  font-size: 16px;
}

.med-badge {
  font-size: 12px;
  color: rgba(255,255,255,0.7);
  font-weight: 600;
}

.med-check {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 16px;
  flex-shrink: 0;
}

.date-selector {
  display: flex;
  gap: 12px;
  align-items: flex-end;
  margin-bottom: 20px;
}

.date-field {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-refresh {
  padding: 12px 18px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.08);
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-refresh:hover {
  background: rgba(255,255,255,0.12);
  border-color: rgba(255,255,255,0.3);
}

.slots-container {
  margin-top: 20px;
}

.slots-header {
  margin-bottom: 12px;
}

.slots-count {
  font-size: 14px;
  color: rgba(255,255,255,0.7);
  font-weight: 600;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 10px;
}

.slot-btn {
  padding: 14px 16px;
  border-radius: 12px;
  border: 2px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.06);
  color: #fff;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 600;
  font-size: 15px;
  text-align: center;
}

.slot-btn:hover {
  transform: translateY(-2px);
  border-color: rgba(127,59,243,0.5);
  background: rgba(127,59,243,0.2);
  box-shadow: 0 4px 12px rgba(127,59,243,0.3);
}

.slot-btn.active {
  border-color: rgba(255,42,136,0.8);
  background: linear-gradient(135deg, rgba(255,42,136,0.3), rgba(127,59,243,0.3));
  box-shadow: 0 0 0 3px rgba(255,42,136,0.2);
}

.slot-time {
  display: block;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: rgba(255,255,255,0.6);
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
  opacity: 0.7;
}

.empty-state p {
  margin: 8px 0;
  font-size: 15px;
}

.empty-hint {
  font-size: 13px;
  color: rgba(255,255,255,0.5);
}

.confirmation-card {
  border-color: rgba(134,239,172,0.4);
  background: rgba(34,197,94,0.08);
}

.resume-card {
  background: rgba(15,15,30,0.5);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.resume-item {
  display: flex;
  align-items: center;
  gap: 16px;
}

.resume-icon {
  font-size: 28px;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: rgba(255,255,255,0.08);
  flex-shrink: 0;
}

.resume-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.resume-label {
  font-size: 12px;
  color: rgba(255,255,255,0.6);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.resume-value {
  font-size: 16px;
  font-weight: 700;
  color: #fff;
}

.cta-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
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
  flex: 1;
  min-width: 200px;
  justify-content: center;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(127,59,243,0.5);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 14px 24px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.08);
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: rgba(255,255,255,0.12);
  border-color: rgba(255,255,255,0.3);
}

.btn-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.message {
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

.message.error {
  background: rgba(220,38,38,0.15);
  border: 1px solid rgba(220,38,38,0.4);
  color: #fca5a5;
}

.message.success {
  background: rgba(34,197,94,0.15);
  border: 1px solid rgba(34,197,94,0.4);
  color: #86efac;
}

.message-icon {
  font-size: 18px;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 28px;
  }
  
  .med-grid {
    grid-template-columns: 1fr;
  }
  
  .date-selector {
    flex-direction: column;
    align-items: stretch;
  }
  
  .slots-grid {
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  }
  
  .cta-buttons {
    flex-direction: column;
  }
  
  .btn-primary {
    width: 100%;
  }
}
</style>
