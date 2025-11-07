<template>
  <section class="page-slot medico-perfil-page">
    <div class="container perfil-container">
      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando perfil del médico...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-state">
        <span class="error-icon">⚠️</span>
        <h2>Error al cargar el perfil</h2>
        <p>{{ error }}</p>
        <button class="btn-primary" @click="volver">← Volver</button>
      </div>

      <!-- Perfil -->
      <div v-else-if="medico" class="perfil-content">
        <!-- Header del perfil -->
        <div class="perfil-header">
          <div class="perfil-avatar">
            <span class="avatar-icon">👨‍⚕️</span>
            <div v-if="medico.verif_status === 'verificado'" class="verificado-badge">
              <span class="badge-icon">✓</span>
              <span>Verificado</span>
            </div>
          </div>
          <div class="perfil-info">
            <h1 class="perfil-nombre">{{ medico.nombre }}</h1>
            <div class="perfil-meta">
              <div v-if="rating.promedio > 0" class="rating-display">
                <span class="stars">{{ getStars(rating.promedio) }}</span>
                <span class="rating-value">{{ rating.promedio }}</span>
                <span class="rating-count">({{ rating.total_resenas }} reseña{{ rating.total_resenas !== 1 ? 's' : '' }})</span>
              </div>
              <div v-else class="rating-display">
                <span class="no-rating">Sin reseñas aún</span>
              </div>
            </div>
            <div class="especialidades-list">
              <span 
                v-for="esp in especialidades" 
                :key="esp.id" 
                class="especialidad-badge"
              >
                {{ esp.nombre }}
              </span>
            </div>
          </div>
          <div class="perfil-actions">
            <button class="btn-primary btn-reservar" @click="reservarCita">
              <span class="btn-icon">📅</span>
              <span>Reservar Cita</span>
            </button>
            <button class="btn-secondary" @click="volver">
              ← Volver
            </button>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.total_citas }}</div>
              <div class="stat-label">Citas Totales</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.citas_completadas }}</div>
              <div class="stat-label">Citas Completadas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.pacientes_atendidos }}</div>
              <div class="stat-label">Pacientes Atendidos</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.seguidores }}</div>
              <div class="stat-label">Seguidores</div>
            </div>
          </div>
        </div>

        <!-- Reseñas -->
        <div class="resenas-section">
          <h2 class="section-title">
            <span class="title-icon">⭐</span>
            Reseñas y Calificaciones
          </h2>
          
          <div v-if="resenas.length === 0" class="no-resenas">
            <span class="no-resenas-icon">💬</span>
            <p>Este médico aún no tiene reseñas.</p>
            <p class="no-resenas-hint">Sé el primero en dejar una reseña después de tu cita.</p>
          </div>

          <div v-else class="resenas-list">
            <div v-for="resena in resenas" :key="resena.id" class="resena-card">
              <div class="resena-header">
                <div class="resena-paciente">
                  <div class="resena-avatar">{{ getInitials(resena.paciente) }}</div>
                  <div class="resena-info">
                    <div class="resena-nombre">{{ resena.paciente }}</div>
                    <div class="resena-fecha">{{ formatDate(resena.fecha) }}</div>
                  </div>
                </div>
                <div class="resena-rating">
                  <span class="resena-stars">{{ getStars(resena.rating) }}</span>
                  <span class="resena-rating-value">{{ resena.rating }}/5</span>
                </div>
              </div>
              <div class="resena-comentario">
                {{ resena.comentario }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref(null)
const medico = ref(null)
const especialidades = ref([])
const stats = ref({})
const rating = ref({ promedio: 0, total_resenas: 0 })
const resenas = ref([])

onMounted(() => {
  cargarPerfil()
})

async function cargarPerfil() {
  loading.value = true
  error.value = null
  
  try {
    const medicoId = route.params.id
    console.log('🔍 Cargando perfil del médico ID:', medicoId)
    console.log('🔍 Ruta completa:', route.fullPath)
    console.log('🔍 Parámetros de ruta:', route.params)
    
    if (!medicoId) {
      error.value = 'ID de médico no proporcionado'
      loading.value = false
      return
    }
    
    const { data } = await api.get(`/public/medicos/${medicoId}/perfil`)
    console.log('✅ Perfil cargado:', data)
    
    medico.value = data.medico
    especialidades.value = data.especialidades || []
    stats.value = data.stats || {}
    rating.value = data.rating || { promedio: 0, total_resenas: 0 }
    resenas.value = data.resenas || []
  } catch (err) {
    console.error('❌ Error al cargar perfil:', err)
    console.error('❌ Respuesta del error:', err?.response)
    error.value = err?.response?.data?.message || 'No se pudo cargar el perfil del médico'
  } finally {
    loading.value = false
  }
}

function getStars(rating) {
  const full = Math.floor(rating)
  const hasHalf = rating % 1 >= 0.5
  return '⭐'.repeat(full) + (hasHalf ? '✨' : '') + '☆'.repeat(5 - full - (hasHalf ? 1 : 0))
}

function getInitials(name) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
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

function reservarCita() {
  const especialidadId = especialidades.value[0]?.id
  router.push({
    name: 'paciente.reservar',
    query: {
      medico_id: medico.value.id,
      especialidad_id: especialidadId
    }
  })
}

function volver() {
  router.back()
}
</script>

<style scoped>
.medico-perfil-page {
  min-height: 100vh;
  padding: 40px 20px;
}

.perfil-container {
  max-width: 1200px;
  margin: 0 auto;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
  color: rgba(234,246,255,0.9);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(127,59,243,0.2);
  border-top-color: rgba(127,59,243,0.8);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 20px;
}

.perfil-content {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.perfil-header {
  display: flex;
  gap: 24px;
  padding: 32px;
  background: rgba(255,255,255,0.05);
  border: 2px solid rgba(127,59,243,0.2);
  border-radius: 20px;
  backdrop-filter: blur(10px);
}

.perfil-avatar {
  position: relative;
  flex-shrink: 0;
}

.avatar-icon {
  font-size: 80px;
  display: block;
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(127,59,243,0.3);
}

.verificado-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  background: linear-gradient(135deg, #00f5ff, #7fd6ff);
  color: #000;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 4px;
  box-shadow: 0 4px 12px rgba(0,245,255,0.4);
}

.badge-icon {
  font-size: 14px;
}

.perfil-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.perfil-nombre {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
}

.stars {
  font-size: 20px;
}

.rating-value {
  font-weight: 700;
  color: rgba(234,246,255,0.95);
}

.rating-count {
  color: rgba(234,246,255,0.7);
  font-size: 14px;
}

.no-rating {
  color: rgba(234,246,255,0.6);
  font-style: italic;
}

.especialidades-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.especialidad-badge {
  padding: 6px 14px;
  background: rgba(127,59,243,0.2);
  border: 1px solid rgba(127,59,243,0.4);
  border-radius: 20px;
  font-size: 13px;
  color: rgba(234,246,255,0.9);
}

.perfil-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex-shrink: 0;
}

.btn-reservar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  font-size: 16px;
  font-weight: 700;
}

.btn-icon {
  font-size: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(127,59,243,0.4);
  transform: translateY(-4px);
}

.stat-icon {
  font-size: 32px;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: rgba(234,246,255,0.95);
  margin-bottom: 4px;
}

.stat-label {
  font-size: 13px;
  color: rgba(234,246,255,0.7);
}

.resenas-section {
  padding: 32px;
  background: rgba(255,255,255,0.05);
  border: 2px solid rgba(127,59,243,0.2);
  border-radius: 20px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 0 24px 0;
  font-size: 24px;
  font-weight: 700;
  color: rgba(234,246,255,0.95);
}

.title-icon {
  font-size: 28px;
}

.no-resenas {
  text-align: center;
  padding: 60px 20px;
  color: rgba(234,246,255,0.7);
}

.no-resenas-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 16px;
  opacity: 0.6;
}

.no-resenas-hint {
  margin-top: 8px;
  font-size: 14px;
  color: rgba(234,246,255,0.6);
}

.resenas-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.resena-card {
  padding: 20px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.resena-card:hover {
  background: rgba(255,255,255,0.05);
  border-color: rgba(127,59,243,0.3);
}

.resena-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.resena-paciente {
  display: flex;
  align-items: center;
  gap: 12px;
}

.resena-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
  color: #fff;
}

.resena-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.resena-nombre {
  font-weight: 700;
  color: rgba(234,246,255,0.95);
  font-size: 15px;
}

.resena-fecha {
  font-size: 12px;
  color: rgba(234,246,255,0.6);
}

.resena-rating {
  display: flex;
  align-items: center;
  gap: 8px;
}

.resena-stars {
  font-size: 18px;
}

.resena-rating-value {
  font-weight: 700;
  color: rgba(234,246,255,0.9);
  font-size: 14px;
}

.resena-comentario {
  color: rgba(234,246,255,0.85);
  line-height: 1.6;
  font-size: 14px;
}

.btn-primary,
.btn-secondary {
  padding: 12px 20px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(127,59,243,0.4);
}

.btn-secondary {
  background: rgba(255,255,255,0.1);
  color: rgba(234,246,255,0.9);
  border: 1px solid rgba(255,255,255,0.2);
}

.btn-secondary:hover {
  background: rgba(255,255,255,0.15);
}

@media (max-width: 768px) {
  .perfil-header {
    flex-direction: column;
    text-align: center;
  }

  .perfil-actions {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>