<template>
  <div class="historial-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-top">
        <button class="btn-back" @click="$router.back()" title="Volver">
          <span class="back-icon">←</span>
          <span>Volver</span>
        </button>
      </div>
      <div class="header-title-section">
        <h1 class="page-title">📋 Mi Historial Médico</h1>
        <p class="page-subtitle">Consulta el historial completo de tus consultas médicas</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <span class="spinner"></span>
      <p>Cargando tu historial médico...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <span class="error-icon">⚠️</span>
      <p class="error-message">{{ error }}</p>
      <button class="btn-retry" @click="cargarHistorial">
        <span>🔄</span>
        <span>Reintentar</span>
      </button>
    </div>

    <!-- Empty State -->
    <div v-else-if="historial.length === 0" class="empty-state">
      <div class="empty-icon">📋</div>
      <h3 class="empty-title">Sin historial médico</h3>
      <p class="empty-text">Aún no tienes registros en tu historial médico.</p>
      <p class="empty-hint">El historial se completa cuando un médico registra información después de una consulta completada.</p>
      <button class="btn-primary" @click="$router.push({ name: 'paciente.home' })">
        <span>🏠</span>
        <span>Ir al Inicio</span>
      </button>
    </div>

    <!-- Content -->
    <div v-else class="content">
      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.total_registros }}</div>
            <div class="stat-label">Registros</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.total_citas_completadas }}</div>
            <div class="stat-label">Citas Completadas</div>
          </div>
        </div>
      </div>

      <!-- Historial List -->
      <div class="historial-list">
        <div 
          v-for="registro in historial" 
          :key="registro.id"
          class="historial-card"
          @click="registroSeleccionado = registro"
        >
          <div class="card-header">
            <div class="header-left">
              <span class="card-icon">👨‍⚕️</span>
              <div>
                <div class="card-title">{{ registro.medico_nombre }}</div>
                <div class="card-meta">{{ registro.especialidad }}</div>
              </div>
            </div>
            <div class="card-date">
              <span class="date-icon">📅</span>
              <span>{{ registro.fecha_completa }}</span>
            </div>
          </div>

          <div class="card-body">
            <!-- Síntomas -->
            <div v-if="registro.sintomas" class="field-section">
              <div class="field-label">
                <span class="field-icon">🩺</span>
                <span>Síntomas</span>
              </div>
              <div class="field-value">{{ registro.sintomas }}</div>
            </div>

            <!-- Diagnóstico -->
            <div v-if="registro.diagnostico" class="field-section">
              <div class="field-label">
                <span class="field-icon">🔬</span>
                <span>Diagnóstico</span>
              </div>
              <div class="field-value">{{ registro.diagnostico }}</div>
            </div>

            <!-- Tratamiento -->
            <div v-if="registro.tratamiento" class="field-section">
              <div class="field-label">
                <span class="field-icon">💊</span>
                <span>Tratamiento</span>
              </div>
              <div class="field-value">{{ registro.tratamiento }}</div>
            </div>

            <!-- Observaciones -->
            <div v-if="registro.observaciones_medicas" class="field-section">
              <div class="field-label">
                <span class="field-icon">📝</span>
                <span>Observaciones</span>
              </div>
              <div class="field-value">{{ registro.observaciones_medicas }}</div>
            </div>
          </div>

          <div class="card-footer">
            <span class="footer-info">
              <span class="footer-icon">ℹ️</span>
              <span>Actualizado: {{ formatearFecha(registro.historial_completado_at) }}</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Detalle (opcional, para ampliar) -->
    <transition name="modal">
      <div v-if="registroSeleccionado" class="modal-overlay" @click.self="registroSeleccionado = null">
        <div class="modal-container">
          <div class="modal-header">
            <h3 class="modal-title">Detalle del Registro</h3>
            <button class="close-btn" @click="registroSeleccionado = null">✕</button>
          </div>
          <div class="modal-body">
            <!-- Contenido completo del registro -->
            <div class="detail-section">
              <strong>Médico:</strong> {{ registroSeleccionado.medico_nombre }}
            </div>
            <div class="detail-section">
              <strong>Especialidad:</strong> {{ registroSeleccionado.especialidad }}
            </div>
            <div class="detail-section">
              <strong>Fecha:</strong> {{ registroSeleccionado.fecha_completa }}
            </div>
            <div v-if="registroSeleccionado.sintomas" class="detail-section">
              <strong>Síntomas:</strong>
              <p>{{ registroSeleccionado.sintomas }}</p>
            </div>
            <div v-if="registroSeleccionado.diagnostico" class="detail-section">
              <strong>Diagnóstico:</strong>
              <p>{{ registroSeleccionado.diagnostico }}</p>
            </div>
            <div v-if="registroSeleccionado.tratamiento" class="detail-section">
              <strong>Tratamiento:</strong>
              <p>{{ registroSeleccionado.tratamiento }}</p>
            </div>
            <div v-if="registroSeleccionado.observaciones_medicas" class="detail-section">
              <strong>Observaciones:</strong>
              <p>{{ registroSeleccionado.observaciones_medicas }}</p>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

const loading = ref(false)
const error = ref(null)
const historial = ref([])
const stats = ref({
  total_registros: 0,
  total_citas_completadas: 0
})
const registroSeleccionado = ref(null)

onMounted(() => {
  cargarHistorial()
})

async function cargarHistorial() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/paciente/historial')
    
    historial.value = data.historial || []
    stats.value = data.stats || { total_registros: 0, total_citas_completadas: 0 }

  } catch (err) {
    console.error('Error al cargar historial:', err)
    error.value = err?.response?.data?.message || 'Error al cargar el historial médico'
  } finally {
    loading.value = false
  }
}

function formatearFecha(fecha) {
  if (!fecha) return ''
  try {
    const date = new Date(fecha)
    return date.toLocaleString('es-ES', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return fecha
  }
}
</script>

<style scoped>
.historial-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a2e 50%, #16213e 100%);
  color: #eaf6ff;
  padding: 40px 24px;
}

.page-header {
  max-width: 1200px;
  margin: 0 auto 40px;
}

.header-top {
  margin-bottom: 20px;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  color: #eaf6ff;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(127, 59, 243, 0.5);
}

.back-icon {
  font-size: 18px;
}

.header-title-section {
  text-align: center;
}

.page-title {
  font-size: 48px;
  font-weight: 800;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 12px 0;
}

.page-subtitle {
  font-size: 18px;
  color: rgba(234, 246, 255, 0.7);
  margin: 0;
}

.loading-state, .error-state, .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.spinner {
  width: 56px;
  height: 56px;
  border: 4px solid rgba(127, 59, 243, 0.3);
  border-top-color: #7f3bf3;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 24px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  font-size: 16px;
  color: rgba(234, 246, 255, 0.8);
}

.error-icon, .empty-icon {
  font-size: 80px;
  margin-bottom: 24px;
}

.error-message, .empty-text {
  font-size: 16px;
  color: rgba(234, 246, 255, 0.8);
  margin-bottom: 20px;
}

.empty-title {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 16px 0;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.empty-hint {
  font-size: 14px;
  color: rgba(234, 246, 255, 0.6);
  margin-bottom: 32px;
}

.btn-retry, .btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 28px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  border: none;
  border-radius: 12px;
  color: #fff;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(127, 59, 243, 0.3);
}

.btn-retry:hover, .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127, 59, 243, 0.4);
}

.content {
  max-width: 1200px;
  margin: 0 auto;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 24px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  transition: all 0.3s;
}

.stat-card:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(127, 59, 243, 0.5);
  transform: translateY(-2px);
}

.stat-icon {
  font-size: 48px;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 36px;
  font-weight: 700;
  color: #fff;
  line-height: 1;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: rgba(234, 246, 255, 0.7);
}

.historial-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.historial-card {
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 24px;
  transition: all 0.3s;
  cursor: pointer;
}

.historial-card:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(127, 59, 243, 0.5);
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(127, 59, 243, 0.2);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.card-icon {
  font-size: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(127, 59, 243, 0.3);
}

.card-title {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 4px;
}

.card-meta {
  font-size: 14px;
  color: rgba(234, 246, 255, 0.7);
}

.card-date {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: rgba(234, 246, 255, 0.8);
}

.date-icon {
  font-size: 16px;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: rgba(234, 246, 255, 0.9);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-icon {
  font-size: 16px;
}

.field-value {
  font-size: 15px;
  color: rgba(234, 246, 255, 0.95);
  line-height: 1.6;
  padding-left: 24px;
}

.card-footer {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid rgba(255, 255, 255, 0.1);
}

.footer-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: rgba(234, 246, 255, 0.6);
}

.footer-icon {
  font-size: 14px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-container {
  background: rgba(20, 20, 35, 0.98);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.modal-title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: #fff;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: rgba(255, 255, 255, 0.7);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.modal-body {
  padding: 24px;
}

.detail-section {
  margin-bottom: 20px;
}

.detail-section strong {
  display: block;
  margin-bottom: 8px;
  color: rgba(234, 246, 255, 0.9);
  font-size: 14px;
}

.detail-section p {
  margin: 0;
  line-height: 1.6;
  color: rgba(234, 246, 255, 0.8);
  font-size: 15px;
}

/* Transiciones */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .page-title {
    font-size: 36px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .field-value {
    padding-left: 0;
  }
}
</style>