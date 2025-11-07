<template>
  <div class="search-wrapper" v-click-outside="closeResults">
    <div class="mr-search" :class="{ focused: isFocused, 'has-results': resultsVisible }">
      <span class="mr-lens"></span>
      <input 
        type="text" 
        v-model="searchQuery"
        @input="handleSearch"
        @focus="handleFocus"
        @blur="handleBlur"
        :placeholder="searchQuery ? 'Sigue escribiendo...' : 'Buscar pacientes, citas, médicos...'"
        autocomplete="off"
      />
      <button 
        v-if="searchQuery" 
        class="clear-btn" 
        @click="clearSearch"
        title="Limpiar búsqueda"
      >
        ✕
      </button>
    </div>

    <!-- Resultados -->
    <transition name="results">
      <div v-if="resultsVisible && (loading || pacientes.length > 0 || citas.length > 0 || medicos.length > 0 || especialidades.length > 0 || (searchQuery && searchQuery.length >= 2))" class="search-results">
        <div v-if="loading" class="results-loading">
          <span class="spinner"></span>
          <span>Buscando pacientes, citas, médicos...</span>
        </div>
        
        <div v-else-if="searchQuery && searchQuery.length < 2" class="results-hint">
          <div class="hint-card">
            <span class="hint-icon">💡</span>
            <div class="hint-content">
              <p class="hint-title">Escribe al menos 2 letras</p>
              <p class="hint-text">Busca por nombre de paciente, ID de cita, fecha, médico o especialidad</p>
            </div>
          </div>
        </div>
        
        <div v-else-if="pacientes.length === 0 && citas.length === 0 && medicos.length === 0 && especialidades.length === 0 && searchQuery.length >= 2" class="no-results">
          <div class="no-results-content">
            <div class="no-results-icon-wrapper">
              <span class="no-results-icon">🔍</span>
            </div>
            <h3 class="no-results-title">No encontramos resultados</h3>
            <p class="no-results-text">No hay pacientes, citas, médicos o especialidades que coincidan con <span class="search-term">"{{ searchQuery }}"</span></p>
            <div class="no-results-suggestions">
              <div class="suggestion-item">
                <span class="suggestion-icon">💡</span>
                <span>Intenta con otro término de búsqueda</span>
              </div>
              <div class="suggestion-item">
                <span class="suggestion-icon">✏️</span>
                <span>Verifica la ortografía</span>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else>
          <!-- Pacientes -->
          <div v-if="pacientes.length > 0" class="results-section">
            <div class="results-header">
              <span class="section-icon">👤</span>
              <span class="section-title">Pacientes encontrados</span>
              <span class="section-count">({{ pacientes.length }})</span>
            </div>
            <div class="results-list">
              <div
                v-for="paciente in pacientes"
                :key="`paciente-${paciente.id}`"
                class="result-item paciente-item-enhanced"
              >
                <div class="paciente-card-header">
                  <div class="result-avatar">
                    <span class="avatar-icon">👤</span>
                  </div>
                  <div class="result-content">
                    <div class="result-header-info">
                      <div class="result-name">{{ paciente.nombre }}</div>
                    </div>
                    <div class="result-meta">
                      <span class="meta-icon">📧</span>
                      <span>{{ paciente.email || 'Sin email' }}</span>
                    </div>
                    <div v-if="paciente.phone" class="result-meta">
                      <span class="meta-icon">📞</span>
                      <span>{{ paciente.phone }}</span>
                    </div>
                    <div v-if="paciente.citas_hoy && paciente.citas_hoy.length > 0" class="citas-hoy-section">
                      <div class="citas-hoy-header">
                        <span class="citas-hoy-icon">📅</span>
                        <span class="citas-hoy-title">Citas de hoy ({{ paciente.citas_hoy.length }})</span>
                      </div>
                      <div class="citas-hoy-list">
                        <div 
                          v-for="cita in paciente.citas_hoy" 
                          :key="`cita-hoy-${cita.id}`"
                          class="cita-hoy-item"
                        >
                          <span class="cita-hoy-hora">{{ cita.hora }}</span>
                          <span class="cita-hoy-estado" :class="getEstadoClass(cita.estado)">{{ cita.estado }}</span>
                        </div>
                      </div>
                    </div>
                    <div v-else-if="paciente.tiene_citas_con_medico" class="citas-hoy-section">
                      <span class="no-citas-hoy">Sin citas programadas para hoy</span>
                    </div>
                    <div v-else class="citas-hoy-section">
                      <span class="no-citas-hoy new-patient">Nuevo paciente - Sin citas previas</span>
                    </div>
                    <div v-if="paciente.stats && paciente.tiene_citas_con_medico" class="result-stats">
                      <div class="stat-item">
                        <span class="stat-icon">📊</span>
                        <span class="stat-value">{{ paciente.stats.total_citas }}</span>
                        <span class="stat-label">Total citas</span>
                      </div>
                      <div class="stat-item">
                        <span class="stat-icon">✅</span>
                        <span class="stat-value">{{ paciente.stats.citas_completadas }}</span>
                        <span class="stat-label">Completadas</span>
                      </div>
                    </div>
                    <div v-else-if="!paciente.tiene_citas_con_medico" class="result-stats">
                      <div class="stat-item new-patient-badge">
                        <span class="stat-icon">🆕</span>
                        <span class="stat-label">Paciente nuevo</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="paciente-card-actions">
                  <button 
                    class="btn-action btn-ver-detalle"
                    @mousedown.prevent="verDetallePaciente(paciente)"
                    title="Ver información completa del paciente"
                  >
                    <span class="btn-icon">👁️</span>
                    <span>Ver Detalles</span>
                  </button>
                  <button 
                    class="btn-action btn-ver-citas"
                    @mousedown.prevent="selectPaciente(paciente)"
                    title="Ver todas las citas de este paciente"
                  >
                    <span class="btn-icon">📋</span>
                    <span>Ver Citas</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Médicos -->
          <div v-if="medicos.length > 0" class="results-section">
            <div class="results-header">
              <span class="section-icon">👨‍⚕️</span>
              <span class="section-title">Médicos encontrados</span>
              <span class="section-count">({{ medicos.length }})</span>
            </div>
            <div class="results-list">
              <button
                v-for="medico in medicos"
                :key="`medico-${medico.id}-${medico.especialidad_id}`"
                class="result-item medico-item"
                @mousedown.prevent="selectMedico(medico)"
              >
                <div class="result-avatar">
                  <span class="avatar-icon">👨‍⚕️</span>
                  <div v-if="medico.verif_status === 'verificado'" class="verificado-badge-small">
                    <span class="badge-icon">✓</span>
                  </div>
                </div>
                <div class="result-content">
                  <div class="result-header-info">
                    <div class="result-name">{{ medico.nombre }}</div>
                    <div v-if="medico.verif_status === 'verificado'" class="result-badge verified">
                      <span class="badge-icon">✓</span>
                      <span>Verificado</span>
                    </div>
                  </div>
                  <div class="result-meta">
                    <span class="meta-icon">🏥</span>
                    <span>{{ medico.especialidad_nombre }}</span>
                  </div>
                </div>
                <div class="result-action">
                  <span class="action-text">Ver Perfil</span>
                  <span class="action-arrow">→</span>
                </div>
              </button>
            </div>
          </div>

          <!-- Especialidades -->
          <div v-if="especialidades.length > 0" class="results-section">
            <div class="results-header">
              <span class="section-icon">🏥</span>
              <span class="section-title">Especialidades encontradas</span>
              <span class="section-count">({{ especialidades.length }})</span>
            </div>
            <div class="results-list">
              <button
                v-for="esp in especialidades"
                :key="`esp-${esp.id}`"
                class="result-item especialidad-item"
                @mousedown.prevent="selectEspecialidad(esp)"
              >
                <div class="result-avatar">
                  <span class="avatar-icon">🏥</span>
                </div>
                <div class="result-content">
                  <div class="result-name">{{ esp.nombre }}</div>
                  <div class="result-meta">
                    <span>Ver información de la especialidad</span>
                  </div>
                </div>
                <div class="result-action">
                  <span class="action-text">Ver</span>
                  <span class="action-arrow">→</span>
                </div>
              </button>
            </div>
          </div>

          <!-- Citas -->
          <div v-if="citas.length > 0" class="results-section">
            <div class="results-header">
              <span class="section-icon">📅</span>
              <span class="section-title">Citas encontradas</span>
              <span class="section-count">({{ citas.length }})</span>
            </div>
            <div class="results-list">
              <button
                v-for="cita in citas"
                :key="`cita-${cita.id}`"
                class="result-item cita-item"
                @mousedown.prevent="selectCita(cita)"
              >
                <div class="result-avatar">
                  <span class="avatar-icon">📅</span>
                </div>
                <div class="result-content">
                  <div class="result-header-info">
                    <div class="result-name">{{ cita.paciente_nombre }}</div>
                    <div class="result-badge" :class="getEstadoClass(cita.estado)">{{ cita.estado }}</div>
                  </div>
                  <div class="result-meta">
                    <span class="meta-icon">📅</span>
                    <span>{{ formatDate(cita.fecha) }} a las {{ cita.hora }}</span>
                  </div>
                  <div class="result-meta">
                    <span class="meta-icon">🏥</span>
                    <span>{{ cita.especialidad }}</span>
                  </div>
                  <div class="result-meta">
                    <span class="meta-icon">#</span>
                    <span>ID: {{ cita.id }}</span>
                  </div>
                </div>
                <div class="result-action">
                  <span class="action-text">Ver Detalles</span>
                  <span class="action-arrow">→</span>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal de Detalle de Paciente -->
    <PacienteDetalleModal
      :visible="pacienteDetalleModal.visible"
      :paciente-id="pacienteDetalleModal.pacienteId"
      @close="cerrarDetallePaciente"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import PacienteDetalleModal from './PacienteDetalleModal.vue'

const router = useRouter()

const searchQuery = ref('')
const isFocused = ref(false)
const loading = ref(false)
const pacientes = ref([])
const citas = ref([])
const medicos = ref([])
const especialidades = ref([])
const resultsVisible = ref(false)
const searchTimeout = ref(null)
const pacienteDetalleModal = ref({
  visible: false,
  pacienteId: null,
})

// Debounce manual para la búsqueda
async function performSearch(query) {
  if (!query || query.trim().length < 2) {
    pacientes.value = []
    citas.value = []
    medicos.value = []
    especialidades.value = []
    resultsVisible.value = false
    return
  }

  loading.value = true
  resultsVisible.value = true
  
  try {
    const { data } = await api.get('/medico/citas/search', {
      params: { q: query.trim() }
    })
    
    pacientes.value = Array.isArray(data?.pacientes) ? data.pacientes : []
    citas.value = Array.isArray(data?.citas) ? data.citas : []
    medicos.value = Array.isArray(data?.medicos) ? data.medicos : []
    especialidades.value = Array.isArray(data?.especialidades) ? data.especialidades : []
  } catch (error) {
    console.error('Error en búsqueda:', error)
    pacientes.value = []
    citas.value = []
    medicos.value = []
    especialidades.value = []
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  const query = searchQuery.value.trim()
  
  if (query.length === 0) {
    pacientes.value = []
    citas.value = []
    medicos.value = []
    especialidades.value = []
    resultsVisible.value = false
    loading.value = false
    return
  }
  
  // Si tiene menos de 2 caracteres, mostrar hint pero no buscar
  if (query.length < 2) {
    pacientes.value = []
    citas.value = []
    medicos.value = []
    especialidades.value = []
    loading.value = false
    if (isFocused.value) {
      resultsVisible.value = true
    }
    return
  }
  
  // Debounce manual con setTimeout
  searchTimeout.value = setTimeout(() => {
    performSearch(query)
  }, 300)
}

function handleFocus() {
  isFocused.value = true
  if (searchQuery.value.trim().length >= 2) {
    resultsVisible.value = true
  } else if (searchQuery.value.trim().length > 0) {
    // Mostrar hint si hay texto pero menos de 2 caracteres
    resultsVisible.value = true
  }
}

function handleBlur() {
  // Delay para permitir clicks en los resultados
  setTimeout(() => {
    isFocused.value = false
    if (!searchQuery.value || searchQuery.value.trim().length < 2) {
      resultsVisible.value = false
    }
  }, 250)
}

function clearSearch() {
  searchQuery.value = ''
  pacientes.value = []
  citas.value = []
  medicos.value = []
  especialidades.value = []
  resultsVisible.value = false
  isFocused.value = false
}

function closeResults() {
  resultsVisible.value = false
  isFocused.value = false
}

function selectPaciente(paciente) {
  // Filtrar citas por este paciente
  searchQuery.value = paciente.nombre
  performSearch(paciente.nombre)
}

function verDetallePaciente(paciente) {
  pacienteDetalleModal.value.pacienteId = paciente.id
  pacienteDetalleModal.value.visible = true
}

function cerrarDetallePaciente() {
  pacienteDetalleModal.value.visible = false
  pacienteDetalleModal.value.pacienteId = null
}

function selectMedico(medico) {
  // Navegar al perfil público del médico (sin permitir reservar)
  router.push({
    name: 'medico.perfil.publico',
    params: { id: String(medico.id) }
  }).catch(err => {
    console.error('Error al navegar al perfil:', err)
    router.push(`/doctor/${medico.id}`)
  })
  clearSearch()
}

function selectEspecialidad(especialidad) {
  // Solo mostrar información, no navegar (el médico no reserva citas)
  clearSearch()
}

function selectCita(cita) {
  // Emitir evento para que el componente padre pueda manejar la selección
  // Por ahora, simplemente recargamos la página con la fecha de la cita
  const fecha = cita.fecha
  clearSearch()
  // Recargar la página con la fecha para mostrar la cita en la agenda
  if (fecha) {
    window.location.href = `/medico?date=${fecha}`
  }
}

function formatDate(dateString) {
  if (!dateString) return ''
  try {
    const date = new Date(dateString + 'T00:00:00')
    return date.toLocaleDateString('es-ES', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    })
  } catch {
    return dateString
  }
}

function getEstadoClass(estado) {
  const e = estado?.toLowerCase() || ''
  if (e === 'confirmada') return 'status-confirmed'
  if (e === 'completada') return 'status-done'
  if (e === 'cancelada') return 'status-cancel'
  return 'status-pending'
}

// Directiva para cerrar al hacer click fuera
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
/* Reutilizar estilos de BuscarMedicos pero adaptados */
.search-wrapper {
  position: relative;
  flex: 1;
  max-width: 600px;
}

.mr-search {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid rgba(255,255,255,.16);
  background: rgba(255,255,255,.06);
  border-radius: 999px;
  padding: 10px 14px;
  height: 44px;
  transition: all 0.3s ease;
  position: relative;
}

.mr-search.focused {
  border-color: rgba(127,59,243,0.5);
  background: rgba(255,255,255,.1);
  box-shadow: 0 0 0 3px rgba(127,59,243,0.1);
}

.mr-search.has-results {
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}

.mr-lens {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: radial-gradient(circle at 40% 40%, #7fd6ff 0 40%, #5fb3ff 41% 100%);
  box-shadow: 0 0 6px rgba(127,214,255,.8);
  display: inline-block;
  position: relative;
  flex-shrink: 0;
}

.mr-lens::after {
  content: '';
  position: absolute;
  width: 8px;
  height: 2px;
  background: #7fd6ff;
  right: -8px;
  bottom: -1px;
  transform: rotate(35deg);
  border-radius: 2px;
  opacity: .8;
}

.mr-search input {
  background: transparent;
  border: 0;
  outline: 0;
  color: #eaf6ff;
  width: 100%;
  font-size: 14px;
  flex: 1;
}

.mr-search input::placeholder {
  color: rgba(234,246,255,.55);
}

.clear-btn {
  background: rgba(255,255,255,0.1);
  border: none;
  color: rgba(255,255,255,0.7);
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
  flex-shrink: 0;
}

.clear-btn:hover {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.search-results {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: rgba(20,20,35,.98);
  border: 2px solid rgba(127,59,243,0.3);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,0.1);
  backdrop-filter: blur(20px);
  max-height: 500px;
  overflow-y: auto;
  z-index: 1000;
  padding: 16px;
}

.results-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 32px 24px;
  color: rgba(234,246,255,0.9);
  font-weight: 600;
}

.results-hint {
  padding: 16px;
}

.hint-card {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  background: rgba(127,59,243,0.1);
  border: 1px solid rgba(127,59,243,0.3);
  border-radius: 12px;
}

.hint-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.hint-content {
  flex: 1;
}

.hint-title {
  margin: 0 0 4px 0;
  font-weight: 700;
  font-size: 14px;
  color: #fff;
}

.hint-text {
  margin: 0;
  font-size: 13px;
  color: rgba(234,246,255,0.7);
}

.results-section {
  margin-bottom: 16px;
}

.results-section:last-child {
  margin-bottom: 0;
}

.results-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  margin-bottom: 12px;
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: rgba(234,246,255,0.95);
}

.section-count {
  margin-left: auto;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 800;
}

.section-icon {
  font-size: 16px;
}

.results-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
  background: rgba(255,255,255,0.05);
  border: 2px solid rgba(255,255,255,0.1);
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
  width: 100%;
  color: rgba(234,246,255,0.95);
  position: relative;
  overflow: hidden;
}

.result-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  opacity: 0;
  transition: opacity 0.3s;
}

.result-item:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(127,59,243,0.5);
  transform: translateX(6px);
  box-shadow: 0 4px 12px rgba(127,59,243,0.2);
}

.result-item:hover::before {
  opacity: 1;
}

.paciente-item:hover {
  border-color: rgba(0,245,255,0.5);
  background: rgba(0,245,255,0.08);
}

.paciente-item:hover::before {
  background: linear-gradient(135deg, #00f5ff, #7fd6ff);
}

.cita-item:hover {
  border-color: rgba(255,42,136,0.5);
  background: rgba(255,42,136,0.08);
}

.cita-item:hover::before {
  background: linear-gradient(135deg, #ff2a88, #ff6ba8);
}

.result-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(127,59,243,0.3);
}

.avatar-icon {
  font-size: 24px;
}

.result-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.result-header-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.result-name {
  font-weight: 700;
  font-size: 15px;
  color: rgba(234,246,255,0.98);
}

.result-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.status-pending {
  background: rgba(251,191,36,.2);
  color: #fde68a;
  border: 1px solid rgba(251,191,36,.4);
}

.status-confirmed {
  background: rgba(34,197,94,.2);
  color: #86efac;
  border: 1px solid rgba(34,197,94,.4);
}

.status-done {
  background: rgba(16,185,129,.2);
  color: #bbf7d0;
  border: 1px solid rgba(16,185,129,.4);
}

.status-cancel {
  background: rgba(239,68,68,.2);
  color: #fca5a5;
  border: 1px solid rgba(239,68,68,.4);
}

.result-meta {
  font-size: 13px;
  color: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  gap: 6px;
}

.meta-icon {
  font-size: 14px;
  opacity: 0.8;
}

.result-action {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
  padding: 6px 12px;
  background: rgba(255,255,255,0.08);
  border-radius: 8px;
  transition: all 0.2s;
}

.result-item:hover .result-action {
  background: rgba(127,59,243,0.3);
  transform: scale(1.05);
}

.action-text {
  font-size: 12px;
  font-weight: 600;
  color: rgba(234,246,255,0.95);
}

.action-arrow {
  font-size: 16px;
  color: rgba(255,255,255,0.7);
  transition: transform 0.2s;
}

.result-item:hover .action-arrow {
  transform: translateX(2px);
  color: rgba(234,246,255,1);
}

.no-results {
  padding: 40px 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.no-results-content {
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.no-results-icon-wrapper {
  margin-bottom: 20px;
  display: flex;
  justify-content: center;
}

.no-results-icon {
  font-size: 64px;
  display: inline-block;
  background: linear-gradient(135deg, rgba(127,59,243,0.2), rgba(255,42,136,0.2));
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(127,59,243,0.3);
  box-shadow: 0 8px 24px rgba(127,59,243,0.15);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.9;
  }
}

.no-results-title {
  margin: 0 0 12px 0;
  font-size: 22px;
  font-weight: 700;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.no-results-text {
  margin: 0 0 24px 0;
  font-size: 15px;
  color: rgba(234,246,255,0.85);
  line-height: 1.6;
}

.search-term {
  color: rgba(255,42,136,0.9);
  font-weight: 600;
  font-style: italic;
}

.no-results-suggestions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 24px;
}

.suggestion-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px 16px;
  background: rgba(127,59,243,0.1);
  border: 1px solid rgba(127,59,243,0.2);
  border-radius: 12px;
  color: rgba(234,246,255,0.8);
  font-size: 13px;
}

.suggestion-icon {
  font-size: 16px;
}

/* Transiciones */
.results-enter-active,
.results-leave-active {
  transition: all 0.3s ease;
}

.results-enter-from,
.results-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(127,59,243,0.3);
  border-top-color: #7f3bf3;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Estilos mejorados para tarjetas de pacientes */
.result-item.paciente-item-enhanced {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 20px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  transition: all 0.3s;
  text-align: left;
  width: 100%;
  margin-bottom: 12px;
  cursor: default;
}

.result-item.paciente-item-enhanced:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(0,245,255,0.5);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,245,255,0.2);
}

.result-item.paciente-item-enhanced::before {
  display: none;
}

.paciente-card-header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.citas-hoy-section {
  margin-top: 12px;
  padding: 12px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
}

.citas-hoy-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 600;
  color: rgba(234,246,255,0.9);
}

.citas-hoy-icon {
  font-size: 16px;
}

.citas-hoy-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.cita-hoy-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 10px;
  background: rgba(255,255,255,0.05);
  border-radius: 8px;
  font-size: 12px;
}

.cita-hoy-hora {
  font-weight: 600;
  color: rgba(234,246,255,0.9);
}

.cita-hoy-estado {
  padding: 3px 8px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 600;
}

.no-citas-hoy {
  font-size: 12px;
  color: rgba(234,246,255,0.5);
  font-style: italic;
}

.no-citas-hoy.new-patient {
  color: rgba(0,245,255,0.8);
  font-weight: 600;
}

.new-patient-badge {
  padding: 6px 12px;
  background: rgba(0,245,255,0.1);
  border: 1px solid rgba(0,245,255,0.3);
  border-radius: 8px;
  color: #00f5ff;
}

.result-stats {
  display: flex;
  gap: 16px;
  margin-top: 8px;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}

.stat-icon {
  font-size: 14px;
}

.stat-value {
  font-weight: 700;
  color: #fff;
}

.stat-label {
  color: rgba(234,246,255,0.6);
}

.paciente-card-actions {
  display: flex;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.btn-ver-detalle {
  flex: 1;
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid rgba(127,59,243,0.3);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: rgba(127,59,243,0.15);
  color: #a78bfa;
}

.btn-ver-detalle:hover {
  background: rgba(127,59,243,0.25);
  border-color: rgba(127,59,243,0.5);
  transform: translateY(-1px);
}

.btn-ver-detalle .btn-icon {
  font-size: 16px;
}

.btn-ver-citas {
  flex: 1;
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: linear-gradient(135deg, #00f5ff, #7f3bf3);
  color: #fff;
  box-shadow: 0 4px 12px rgba(0,245,255,0.3);
}

.btn-ver-citas:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,245,255,0.4);
}

.btn-ver-citas .btn-icon {
  font-size: 16px;
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

.verificado-badge-small .badge-icon {
  font-size: 12px;
  color: #fff;
  font-weight: bold;
}

.result-badge.verified {
  background: rgba(127,59,243,0.15);
  color: #7f3bf3;
  border: 1px solid rgba(127,59,243,0.3);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.result-badge.verified .badge-icon {
  font-size: 12px;
}

.medico-item:hover {
  border-color: rgba(127,59,243,0.5);
  background: rgba(127,59,243,0.08);
}

.medico-item:hover::before {
  background: linear-gradient(135deg, #7f3bf3, #ff2a88);
}

.result-avatar {
  position: relative;
}
</style>