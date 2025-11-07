<template>
  <section class="page-slot mis-citas-page">
    <div class="container">
      <header class="page-header">
        <div>
          <h1>Mis Citas 📅</h1>
          <p>Gestiona todas tus citas médicas en un solo lugar</p>
        </div>
        <div class="header-actions">
          <button class="btn-soft" @click="volver">← Volver</button>
          <button class="btn-primary" @click="irAReservar">+ Nueva Cita</button>
        </div>
      </header>

      <!-- Filtros y Búsqueda -->
      <div class="filters-section">
        <div class="filters-grid">
          <!-- Búsqueda -->
          <div class="filter-group search-group">
            <label class="filter-label">
              <span class="filter-icon">🔍</span>
              Buscar
            </label>
            <input
              type="text"
              v-model="filters.q"
              @input="debounceSearch"
              placeholder="Buscar por médico, especialidad, motivo..."
              class="filter-input"
            />
          </div>

          <!-- Estado -->
          <div class="filter-group">
            <label class="filter-label">
              <span class="filter-icon">📊</span>
              Estado
            </label>
            <select v-model="filters.estado" @change="aplicarFiltros" class="filter-select">
              <option value="">Todos</option>
              <option value="pendiente">Pendiente</option>
              <option value="confirmada">Confirmada</option>
              <option value="completada">Completada</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>

          <!-- Fecha Desde -->
          <div class="filter-group">
            <label class="filter-label">
              <span class="filter-icon">📅</span>
              Desde
            </label>
            <input
              type="date"
              v-model="filters.desde"
              @change="aplicarFiltros"
              class="filter-input"
            />
          </div>

          <!-- Fecha Hasta -->
          <div class="filter-group">
            <label class="filter-label">
              <span class="filter-icon">📅</span>
              Hasta
            </label>
            <input
              type="date"
              v-model="filters.hasta"
              @change="aplicarFiltros"
              class="filter-input"
            />
          </div>

          <!-- Limpiar Filtros -->
          <div class="filter-group">
            <button class="btn-clear-filters" @click="limpiarFiltros">
              <span class="btn-icon">🔄</span>
              Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Estadísticas Rápidas -->
      <div class="stats-bar">
        <div class="stat-item">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ stats.pendientes }}</span>
          <span class="stat-label">Pendientes</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ stats.completadas }}</span>
          <span class="stat-label">Completadas</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ stats.canceladas }}</span>
          <span class="stat-label">Canceladas</span>
        </div>
      </div>

      <!-- Lista de Citas -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando tus citas...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <span class="error-icon">⚠️</span>
        <h2>Error al cargar las citas</h2>
        <p>{{ error }}</p>
        <button class="btn-primary" @click="cargarCitas">Reintentar</button>
      </div>

      <div v-else-if="citas.length === 0" class="empty-state">
        <span class="empty-icon">📅</span>
        <h2>No hay citas</h2>
        <p v-if="hasFilters">No se encontraron citas con los filtros aplicados.</p>
        <p v-else>No tienes citas registradas aún.</p>
        <button class="btn-primary" @click="irAReservar">Reservar mi primera cita</button>
      </div>

      <div v-else class="citas-list">
        <div
          v-for="cita in citas"
          :key="cita.id"
          class="cita-card"
          :class="{
            'cita-pasada': cita.es_pasada,
            'cita-hoy': cita.es_hoy,
            'cita-cancelada': cita.estado === 'cancelada',
          }"
          @click="verDetalle(cita)"
        >
          <div class="cita-header">
            <div class="cita-medico-info">
              <div class="medico-avatar">
                <span class="avatar-icon">👨‍⚕️</span>
                <div v-if="cita.medico_verificado" class="verificado-badge-small">
                  <span class="badge-icon">✓</span>
                </div>
              </div>
              <div class="medico-details">
                <h3 class="medico-nombre">{{ cita.medico_nombre }}</h3>
                <div class="medico-meta">
                  <span class="especialidad-badge">{{ cita.especialidad_nombre }}</span>
                  <span v-if="cita.medico_verificado" class="verificado-text">✓ Verificado</span>
                </div>
              </div>
            </div>
            <div class="cita-estado" :class="getEstadoClass(cita.estado)">
              {{ formatEstado(cita.estado) }}
            </div>
          </div>

          <div class="cita-body">
            <div class="cita-fecha-info">
              <div class="fecha-item">
                <span class="fecha-icon">📅</span>
                <div class="fecha-content">
                  <span class="fecha-dia">{{ cita.fecha_completa }}</span>
                  <span class="fecha-humana">{{ cita.fecha_humana }}</span>
                </div>
              </div>
              <div v-if="cita.motivo" class="motivo-item">
                <span class="motivo-icon">💬</span>
                <span class="motivo-text">{{ cita.motivo }}</span>
              </div>
            </div>
          </div>

          <div class="cita-actions">
            <button
              class="btn-action btn-detail"
              @click.stop="verDetalle(cita)"
              title="Ver detalles"
            >
              <span class="btn-icon">👁️</span>
              <span>Detalles</span>
            </button>
            <button
              v-if="cita.puede_cancelar"
              class="btn-action btn-cancel"
              @click.stop="cancelarCita(cita)"
              :disabled="cancelando === cita.id"
              title="Cancelar cita"
            >
              <span v-if="cancelando === cita.id" class="btn-spinner">⏳</span>
              <span v-else class="btn-icon">❌</span>
              <span>Cancelar</span>
            </button>
            <button
              v-if="cita.puede_reprogramar"
              class="btn-action btn-reschedule"
              @click.stop="reprogramarCita(cita)"
              :disabled="reprogramando === cita.id"
              title="Reprogramar cita"
            >
              <span v-if="reprogramando === cita.id" class="btn-spinner">⏳</span>
              <span v-else class="btn-icon">🔄</span>
              <span>Reprogramar</span>
            </button>
            <button
              v-if="cita.estado === 'completada' && !cita.rating"
              class="btn-action btn-rate"
              @click.stop="abrirCalificacion(cita)"
              title="Calificar atención"
            >
              <span class="btn-icon">⭐</span>
              <span>Calificar</span>
            </button>
            <button
              v-if="cita.estado === 'completada' && cita.rating"
              class="btn-action btn-rated"
              @click.stop="abrirCalificacion(cita)"
              title="Ver/Editar calificación"
            >
              <span class="btn-icon">⭐</span>
              <span>{{ cita.rating }}/5</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="pagination && pagination.total > pagination.per_page" class="pagination">
        <button
          class="btn-pagination"
          :disabled="pagination.current_page === 1"
          @click="cambiarPagina(pagination.current_page - 1)"
        >
          ← Anterior
        </button>
        <span class="pagination-info">
          Página {{ pagination.current_page }} de {{ pagination.last_page }}
          ({{ pagination.total }} citas)
        </span>
        <button
          class="btn-pagination"
          :disabled="pagination.current_page === pagination.last_page"
          @click="cambiarPagina(pagination.current_page + 1)"
        >
          Siguiente →
        </button>
      </div>
    </div>

    <!-- Modal de Calificación -->
    <RatingModal
      :visible="ratingModal.visible"
      :cita="ratingModal.cita"
      :existing-rating="ratingModal.existingRating"
      @close="cerrarCalificacion"
      @saved="onCalificacionGuardada"
    />

    <!-- Modal de Detalle de Cita -->
    <div v-if="citaSeleccionada" class="modal-overlay" @click="cerrarDetalle">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Detalles de la Cita</h2>
          <button class="btn-close" @click="cerrarDetalle">✕</button>
        </div>
        <div v-if="loadingDetalle" class="loading-state">
          <div class="spinner"></div>
          <p>Cargando detalles...</p>
        </div>
        <div v-else-if="detalleCita" class="modal-body">
          <div class="detail-section">
            <h3>👨‍⚕️ Médico</h3>
            <div class="detail-content">
              <p><strong>Nombre:</strong> {{ detalleCita.medico_nombre }}</p>
              <p v-if="detalleCita.medico_email"><strong>Email:</strong> {{ detalleCita.medico_email }}</p>
              <p v-if="detalleCita.medico_phone"><strong>Teléfono:</strong> {{ detalleCita.medico_phone }}</p>
              <p v-if="detalleCita.medico_verificado" class="verificado-badge-inline">
                ✓ Médico Verificado
              </p>
            </div>
          </div>

          <div class="detail-section">
            <h3>📅 Información de la Cita</h3>
            <div class="detail-content">
              <p><strong>Fecha y Hora:</strong> {{ detalleCita.fecha_completa }}</p>
              <p><strong>Especialidad:</strong> {{ detalleCita.especialidad_nombre }}</p>
              <p><strong>Estado:</strong> 
                <span class="estado-badge" :class="getEstadoClass(detalleCita.estado)">
                  {{ formatEstado(detalleCita.estado) }}
                </span>
              </p>
              <p v-if="detalleCita.motivo"><strong>Motivo:</strong> {{ detalleCita.motivo }}</p>
              <p v-if="detalleCita.notas"><strong>Notas:</strong> {{ detalleCita.notas }}</p>
              <p v-if="detalleCita.cancel_reason"><strong>Razón de cancelación:</strong> {{ detalleCita.cancel_reason }}</p>
            </div>
          </div>

          <div class="modal-actions">
            <button
              v-if="detalleCita.puede_cancelar"
              class="btn-action btn-cancel"
              @click="cancelarCita(detalleCita)"
            >
              ❌ Cancelar Cita
            </button>
            <button
              v-if="detalleCita.puede_reprogramar"
              class="btn-action btn-reschedule"
              @click="reprogramarCita(detalleCita)"
            >
              🔄 Reprogramar
            </button>
            <button class="btn-action btn-close-modal" @click="cerrarDetalle">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import RatingModal from '../components/RatingModal.vue'

const router = useRouter()

const loading = ref(true)
const error = ref(null)
const citas = ref([])
const pagination = ref(null)
const stats = ref({
  total: 0,
  pendientes: 0,
  completadas: 0,
  canceladas: 0,
})

const filters = ref({
  q: '',
  estado: '',
  desde: '',
  hasta: '',
  medico_id: '',
  especialidad_id: '',
  order_by: 'starts_at',
  order_dir: 'desc',
  per_page: 20,
  page: 1,
})

const searchTimeout = ref(null)
const cancelando = ref(null)
const reprogramando = ref(null)
const citaSeleccionada = ref(null)
const detalleCita = ref(null)
const loadingDetalle = ref(false)
const ratingModal = ref({
  visible: false,
  cita: null,
  existingRating: null,
})

const hasFilters = computed(() => {
  return filters.value.q || filters.value.estado || filters.value.desde || filters.value.hasta
})

onMounted(() => {
  cargarCitas()
})

function debounceSearch() {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  searchTimeout.value = setTimeout(() => {
    filters.value.page = 1
    aplicarFiltros()
  }, 500)
}

function aplicarFiltros() {
  filters.value.page = 1
  cargarCitas()
}

function limpiarFiltros() {
  filters.value = {
    q: '',
    estado: '',
    desde: '',
    hasta: '',
    medico_id: '',
    especialidad_id: '',
    order_by: 'starts_at',
    order_dir: 'desc',
    per_page: 20,
    page: 1,
  }
  cargarCitas()
}

function cambiarPagina(page) {
  filters.value.page = page
  cargarCitas()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function cargarCitas() {
  loading.value = true
  error.value = null

  try {
    const params = { ...filters.value }
    // Eliminar valores vacíos
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key]
      }
    })

    const { data } = await api.get('/paciente/citas', { params })

    citas.value = data.data || []
    pagination.value = data.pagination || null

    // Calcular estadísticas
    calcularEstadisticas()
  } catch (err) {
    console.error('Error al cargar citas:', err)
    error.value = err?.response?.data?.message || 'No se pudieron cargar las citas'
    citas.value = []
  } finally {
    loading.value = false
  }
}

function calcularEstadisticas() {
  stats.value = {
    total: pagination.value?.total || citas.value.length,
    pendientes: citas.value.filter(c => c.estado === 'pendiente').length,
    completadas: citas.value.filter(c => c.estado === 'completada').length,
    canceladas: citas.value.filter(c => c.estado === 'cancelada').length,
  }
}

async function verDetalle(cita) {
  citaSeleccionada.value = cita
  loadingDetalle.value = true
  detalleCita.value = null

  try {
    const { data } = await api.get(`/paciente/citas/${cita.id}`)
    detalleCita.value = data
  } catch (err) {
    console.error('Error al cargar detalle:', err)
    // Usar datos básicos de la cita si falla
    detalleCita.value = cita
  } finally {
    loadingDetalle.value = false
  }
}

function cerrarDetalle() {
  citaSeleccionada.value = null
  detalleCita.value = null
}

async function cancelarCita(cita) {
  if (!confirm('¿Estás seguro de que deseas cancelar esta cita?')) {
    return
  }

  cancelando.value = cita.id
  try {
    await api.post(`/paciente/citas/${cita.id}/cancelar`, {
      motivo: prompt('Motivo de cancelación (opcional):') || null
    })
    
    // Recargar citas
    await cargarCitas()
    
    // Cerrar modal si está abierto
    if (citaSeleccionada.value?.id === cita.id) {
      cerrarDetalle()
    }
    
    alert('Cita cancelada correctamente')
  } catch (err) {
    console.error('Error al cancelar cita:', err)
    alert(err?.response?.data?.message || 'No se pudo cancelar la cita')
  } finally {
    cancelando.value = null
  }
}

function reprogramarCita(cita) {
  router.push({
    name: 'paciente.reservar',
    query: {
      medico_id: cita.medico_id,
      especialidad_id: cita.especialidad_id,
      reprogramar: cita.id,
    }
  })
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

function irAReservar() {
  router.push({ name: 'paciente.reservar' })
}

async function abrirCalificacion(cita) {
  ratingModal.value.cita = cita
  ratingModal.value.visible = true
  
  // Cargar calificación existente si existe
  try {
    const { data } = await api.get(`/paciente/citas/${cita.id}/rating`)
    ratingModal.value.existingRating = data
  } catch (err) {
    // Si no hay calificación, continuar sin error
    ratingModal.value.existingRating = null
  }
}

function cerrarCalificacion() {
  ratingModal.value.visible = false
  ratingModal.value.cita = null
  ratingModal.value.existingRating = null
}

function onCalificacionGuardada(ratingData) {
  // Actualizar la cita en la lista
  const citaIndex = citas.value.findIndex(c => c.id === ratingModal.value.cita.id)
  if (citaIndex !== -1) {
    citas.value[citaIndex].rating = ratingData.rating
    citas.value[citaIndex].review = ratingData.review
  }
  cerrarCalificacion()
}

function volver() {
  router.push({ name: 'paciente.home' })
}
</script>

<style scoped>
.mis-citas-page {
  padding: 40px 20px;
  min-height: 100vh;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  gap: 20px;
  flex-wrap: wrap;
}

.page-header h1 {
  font-size: 32px;
  font-weight: 700;
  background: linear-gradient(135deg, #00f5ff, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 8px;
}

.page-header p {
  color: rgba(234,246,255,0.7);
  font-size: 16px;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.filters-section {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 24px;
  margin-bottom: 30px;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.search-group {
  grid-column: 1 / -1;
}

.filter-label {
  font-size: 13px;
  font-weight: 600;
  color: rgba(234,246,255,0.9);
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-icon {
  font-size: 16px;
}

.filter-input,
.filter-select {
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: #fff;
  font-size: 15px;
  transition: all 0.2s;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #7f3bf3;
  background: rgba(255,255,255,0.08);
}

.btn-clear-filters {
  padding: 12px 20px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-clear-filters:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.2);
}

.stats-bar {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.stat-item {
  flex: 1;
  min-width: 120px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 13px;
  color: rgba(234,246,255,0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.citas-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cita-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  padding: 24px;
  transition: all 0.3s;
  cursor: pointer;
}

.cita-card:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(127,59,243,0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(127,59,243,0.2);
}

.cita-card.cita-pasada {
  opacity: 0.7;
}

.cita-card.cita-hoy {
  border-color: rgba(0,245,255,0.5);
  background: rgba(0,245,255,0.05);
}

.cita-card.cita-cancelada {
  opacity: 0.6;
  border-color: rgba(239,68,68,0.3);
}

.cita-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.cita-medico-info {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}

.medico-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7f3bf3, #ff2a88);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
}

.avatar-icon {
  font-size: 28px;
}

.verificado-badge-small {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00f5ff, #7f3bf3);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(20,20,35,0.98);
  box-shadow: 0 2px 8px rgba(0,245,255,0.5);
}

.badge-icon {
  font-size: 12px;
  color: #fff;
  font-weight: bold;
}

.medico-details {
  flex: 1;
}

.medico-nombre {
  font-size: 18px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 6px;
}

.medico-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.especialidad-badge {
  padding: 4px 12px;
  border-radius: 8px;
  background: rgba(127,59,243,0.15);
  color: #a78bfa;
  font-size: 13px;
  font-weight: 600;
}

.verificado-text {
  font-size: 12px;
  color: #00f5ff;
  font-weight: 600;
}

.cita-estado {
  padding: 8px 16px;
  border-radius: 10px;
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

.cita-body {
  margin-bottom: 16px;
}

.cita-fecha-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.fecha-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.fecha-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.fecha-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.fecha-dia {
  font-size: 16px;
  font-weight: 600;
  color: rgba(234,246,255,0.98);
}

.fecha-humana {
  font-size: 13px;
  color: rgba(234,246,255,0.6);
}

.motivo-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255,255,255,0.03);
  border-radius: 10px;
}

.motivo-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.motivo-text {
  font-size: 14px;
  color: rgba(234,246,255,0.8);
  font-style: italic;
}

.cita-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  padding-top: 16px;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.btn-action {
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-detail {
  background: rgba(127,59,243,0.15);
  color: #a78bfa;
  border: 1px solid rgba(127,59,243,0.3);
}

.btn-detail:hover {
  background: rgba(127,59,243,0.25);
}

.btn-cancel {
  background: rgba(239,68,68,0.15);
  color: #fca5a5;
  border: 1px solid rgba(239,68,68,0.3);
}

.btn-cancel:hover:not(:disabled) {
  background: rgba(239,68,68,0.25);
}

.btn-reschedule {
  background: rgba(0,245,255,0.15);
  color: #7fd6ff;
  border: 1px solid rgba(0,245,255,0.3);
}

.btn-reschedule:hover:not(:disabled) {
  background: rgba(0,245,255,0.25);
}

.btn-rate {
  background: rgba(251,191,36,0.15);
  color: #fde68a;
  border: 1px solid rgba(251,191,36,0.3);
}

.btn-rate:hover:not(:disabled) {
  background: rgba(251,191,36,0.25);
}

.btn-rated {
  background: rgba(34,197,94,0.15);
  color: #86efac;
  border: 1px solid rgba(34,197,94,0.3);
}

.btn-rated:hover:not(:disabled) {
  background: rgba(34,197,94,0.25);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-spinner {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 40px;
  padding: 20px;
}

.btn-pagination {
  padding: 10px 20px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-pagination:hover:not(:disabled) {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.2);
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  color: rgba(234,246,255,0.7);
  font-size: 14px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.8);
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
  border-radius: 20px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.modal-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.1);
  color: rgba(234,246,255,0.9);
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-close:hover {
  background: rgba(255,255,255,0.2);
}

.modal-body {
  padding: 24px;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h3 {
  font-size: 18px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 12px;
}

.detail-content p {
  margin-bottom: 8px;
  color: rgba(234,246,255,0.8);
  font-size: 15px;
}

.detail-content strong {
  color: rgba(234,246,255,0.98);
  font-weight: 600;
}

.verificado-badge-inline {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 8px;
  background: rgba(34,197,94,0.15);
  color: #86efac;
  border: 1px solid rgba(34,197,94,0.3);
  font-size: 13px;
  font-weight: 600;
  margin-top: 8px;
}

.estado-badge {
  padding: 4px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid rgba(255,255,255,0.1);
  flex-wrap: wrap;
}

.btn-close-modal {
  background: rgba(255,255,255,0.1);
  color: rgba(234,246,255,0.9);
  border: 1px solid rgba(255,255,255,0.2);
}

.btn-close-modal:hover {
  background: rgba(255,255,255,0.15);
}

.loading-state,
.error-state,
.empty-state {
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

.error-icon,
.empty-icon {
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
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127,59,243,0.4);
}

.btn-soft {
  padding: 10px 20px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-soft:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.2);
}
</style>