<template>
  <div v-if="visible" class="modal-overlay" @click="cerrar">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Información del Paciente 👤</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>
      
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando información...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <span class="error-icon">⚠️</span>
        <h3>Error al cargar</h3>
        <p>{{ error }}</p>
        <button class="btn-primary" @click="cargarDetalle">Reintentar</button>
      </div>

      <div v-else-if="paciente" class="modal-body">
        <!-- Información Personal -->
        <div class="detail-section">
          <h3>
            <span class="section-icon">👤</span>
            Información Personal
          </h3>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Nombre completo</span>
              <span class="detail-value">{{ paciente.nombre }}</span>
            </div>
            <div class="detail-item" v-if="paciente.email">
              <span class="detail-label">Email</span>
              <span class="detail-value">{{ paciente.email }}</span>
            </div>
            <div class="detail-item" v-if="paciente.phone">
              <span class="detail-label">Teléfono</span>
              <span class="detail-value">{{ paciente.phone }}</span>
            </div>
            <div class="detail-item" v-if="paciente.birthdate">
              <span class="detail-label">Fecha de nacimiento</span>
              <span class="detail-value">
                {{ formatDate(paciente.birthdate) }}
                <span v-if="paciente.edad" class="edad-badge">({{ paciente.edad }} años)</span>
              </span>
            </div>
            <div class="detail-item" v-if="paciente.gender">
              <span class="detail-label">Género</span>
              <span class="detail-value">{{ formatGender(paciente.gender) }}</span>
            </div>
            <div class="detail-item" v-if="paciente.doc_tipo && paciente.doc_numero">
              <span class="detail-label">Documento</span>
              <span class="detail-value">{{ paciente.doc_tipo }}: {{ paciente.doc_numero }}</span>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="detail-section" v-if="stats">
          <h3>
            <span class="section-icon">📊</span>
            Estadísticas con este médico
          </h3>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-icon">📅</div>
              <div class="stat-value">{{ stats.total_citas || 0 }}</div>
              <div class="stat-label">Total de citas</div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">✅</div>
              <div class="stat-value">{{ stats.citas_completadas || 0 }}</div>
              <div class="stat-label">Completadas</div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">⏳</div>
              <div class="stat-value">{{ stats.citas_pendientes || 0 }}</div>
              <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">❌</div>
              <div class="stat-value">{{ stats.citas_canceladas || 0 }}</div>
              <div class="stat-label">Canceladas</div>
            </div>
          </div>
          <div v-if="stats.primera_cita || stats.ultima_cita" class="stats-meta">
            <div v-if="stats.primera_cita" class="meta-item">
              <span class="meta-icon">📅</span>
              <span>Primera cita: {{ formatDate(stats.primera_cita) }}</span>
            </div>
            <div v-if="stats.ultima_cita" class="meta-item">
              <span class="meta-icon">📅</span>
              <span>Última cita: {{ formatDate(stats.ultima_cita) }}</span>
            </div>
          </div>
        </div>

        <!-- Próximas Citas -->
        <div class="detail-section" v-if="proximasCitas && proximasCitas.length > 0">
          <h3>
            <span class="section-icon">📅</span>
            Próximas Citas ({{ proximasCitas.length }})
          </h3>
          <div class="citas-list">
            <div
              v-for="cita in proximasCitas"
              :key="cita.id"
              class="cita-item"
            >
              <div class="cita-info">
                <div class="cita-fecha">
                  <span class="cita-dia">{{ cita.fecha }}</span>
                  <span class="cita-hora">{{ cita.hora }}</span>
                </div>
                <div class="cita-details">
                  <span class="cita-especialidad">{{ cita.especialidad_nombre }}</span>
                  <span v-if="cita.motivo" class="cita-motivo">{{ cita.motivo }}</span>
                </div>
              </div>
              <span class="cita-estado" :class="getEstadoClass(cita.estado)">
                {{ formatEstado(cita.estado) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Historial -->
        <div class="detail-section" v-if="historial && historial.length > 0">
          <h3>
            <span class="section-icon">📋</span>
            Historial de Citas ({{ historial.length }})
          </h3>
          <div class="citas-list historial-list">
            <div
              v-for="cita in historial"
              :key="cita.id"
              class="cita-item historial-item"
            >
              <div class="cita-info">
                <div class="cita-fecha">
                  <span class="cita-dia">{{ cita.fecha }}</span>
                  <span class="cita-hora">{{ cita.hora }}</span>
                </div>
                <div class="cita-details">
                  <span class="cita-especialidad">{{ cita.especialidad_nombre }}</span>
                  <span v-if="cita.motivo" class="cita-motivo">{{ cita.motivo }}</span>
                  <span v-if="cita.notas" class="cita-notas">{{ cita.notas }}</span>
                </div>
                <div v-if="cita.rating" class="cita-rating">
                  <span class="rating-stars">{{ getStars(cita.rating) }}</span>
                  <span v-if="cita.review" class="rating-review">"{{ cita.review }}"</span>
                </div>
              </div>
              <span class="cita-estado" :class="getEstadoClass(cita.estado)">
                {{ formatEstado(cita.estado) }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="!proximasCitas?.length && !historial?.length && stats?.total_citas === 0" class="empty-section">
          <span class="empty-icon">📋</span>
          <p>Este paciente aún no tiene citas con usted.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '../../services/api'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  pacienteId: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['close'])

const loading = ref(false)
const error = ref(null)
const paciente = ref(null)
const stats = ref(null)
const proximasCitas = ref([])
const historial = ref([])

watch(() => props.visible, (newVal) => {
  if (newVal && props.pacienteId) {
    cargarDetalle()
  } else {
    resetData()
  }
})

function resetData() {
  paciente.value = null
  stats.value = null
  proximasCitas.value = []
  historial.value = []
  error.value = null
}

async function cargarDetalle() {
  if (!props.pacienteId) return

  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/medico/pacientes/${props.pacienteId}`)
    paciente.value = data.paciente
    stats.value = data.stats
    proximasCitas.value = data.proximas_citas || []
    historial.value = data.historial || []
  } catch (err) {
    console.error('Error al cargar detalle del paciente:', err)
    error.value = err?.response?.data?.message || 'No se pudo cargar la información del paciente'
  } finally {
    loading.value = false
  }
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function formatGender(gender) {
  const map = {
    'M': 'Masculino',
    'F': 'Femenino',
    'O': 'Otro',
    'masculino': 'Masculino',
    'femenino': 'Femenino',
    'otro': 'Otro',
  }
  return map[gender] || gender
}

function formatEstado(estado) {
  const map = {
    'pendiente': 'Pendiente',
    'confirmada': 'Confirmada',
    'completada': 'Completada',
    'cancelada': 'Cancelada',
  }
  return map[estado] || estado
}

function getEstadoClass(estado) {
  const e = estado?.toLowerCase() || ''
  if (e === 'confirmada') return 'status-confirmed'
  if (e === 'completada') return 'status-done'
  if (e === 'cancelada') return 'status-cancel'
  return 'status-pending'
}

function getStars(rating) {
  if (!rating) return ''
  const full = Math.floor(rating)
  const hasHalf = rating % 1 >= 0.5
  return '⭐'.repeat(full) + (hasHalf ? '✨' : '') + '☆'.repeat(5 - full - (hasHalf ? 1 : 0))
}

function cerrar() {
  emit('close')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: rgba(20,20,35,0.98);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 30px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  position: sticky;
  top: 0;
  background: rgba(20,20,35,0.98);
  z-index: 10;
}

.modal-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin: 0;
}

.btn-close {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.1);
  color: rgba(234,246,255,0.9);
  font-size: 20px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: rgba(255,255,255,0.2);
  transform: rotate(90deg);
}

.modal-body {
  padding: 30px;
}

.detail-section {
  margin-bottom: 32px;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-section h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(127,59,243,0.3);
}

.section-icon {
  font-size: 24px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 14px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
}

.detail-label {
  font-size: 12px;
  color: rgba(234,246,255,0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.detail-value {
  font-size: 15px;
  color: rgba(234,246,255,0.98);
  font-weight: 600;
}

.edad-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 6px;
  background: rgba(127,59,243,0.15);
  color: #a78bfa;
  font-size: 12px;
  font-weight: 600;
  margin-left: 8px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.stat-card {
  padding: 20px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  text-align: center;
  transition: all 0.3s;
}

.stat-card:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(127,59,243,0.3);
  transform: translateY(-2px);
}

.stat-icon {
  font-size: 28px;
  margin-bottom: 8px;
  display: block;
}

.stat-value {
  font-size: 32px;
  font-weight: 900;
  color: rgba(234,246,255,0.98);
  margin-bottom: 4px;
  display: block;
}

.stat-label {
  font-size: 13px;
  color: rgba(234,246,255,0.6);
  font-weight: 600;
}

.stats-meta {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  padding: 16px;
  background: rgba(255,255,255,0.03);
  border-radius: 12px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: rgba(234,246,255,0.8);
}

.meta-icon {
  font-size: 16px;
}

.citas-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cita-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  transition: all 0.2s;
}

.cita-item:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(127,59,243,0.3);
}

.cita-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.cita-fecha {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cita-dia {
  font-size: 16px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
}

.cita-hora {
  font-size: 14px;
  color: rgba(234,246,255,0.7);
  padding: 4px 10px;
  background: rgba(255,255,255,0.05);
  border-radius: 8px;
}

.cita-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.cita-especialidad {
  font-size: 14px;
  color: rgba(234,246,255,0.8);
  font-weight: 600;
}

.cita-motivo,
.cita-notas {
  font-size: 13px;
  color: rgba(234,246,255,0.6);
  font-style: italic;
}

.cita-rating {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(255,255,255,0.08);
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rating-stars {
  font-size: 16px;
}

.rating-review {
  font-size: 13px;
  color: rgba(234,246,255,0.7);
  font-style: italic;
}

.cita-estado {
  padding: 8px 14px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  flex-shrink: 0;
}

.status-pending {
  background: rgba(251,191,36,0.15);
  color: #fde68a;
  border: 1px solid rgba(251,191,36,0.3);
}

.status-confirmed {
  background: rgba(34,197,94,0.15);
  color: #86efac;
  border: 1px solid rgba(34,197,94,0.3);
}

.status-done {
  background: rgba(16,185,129,0.15);
  color: #bbf7d0;
  border: 1px solid rgba(16,185,129,0.3);
}

.status-cancel {
  background: rgba(239,68,68,0.15);
  color: #fca5a5;
  border: 1px solid rgba(239,68,68,0.3);
}

.empty-section {
  text-align: center;
  padding: 40px 20px;
  color: rgba(234,246,255,0.6);
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
  opacity: 0.5;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(127,59,243,0.2);
  border-top-color: #7f3bf3;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 20px;
  opacity: 0.5;
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #7f3bf3, #ff2a88);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 16px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127,59,243,0.4);
}
</style>