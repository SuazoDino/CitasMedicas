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
        :placeholder="searchQuery ? 'Sigue escribiendo...' : 'Buscar médicos, especialidades...'"
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
      <div v-if="resultsVisible && (loading || medicos.length > 0 || especialidades.length > 0 || (searchQuery && searchQuery.length >= 2))" class="search-results">
        <div v-if="loading" class="results-loading">
          <span class="spinner"></span>
          <span>Buscando médicos y especialidades...</span>
        </div>
        
        <div v-else-if="searchQuery && searchQuery.length < 2" class="results-hint">
          <div class="hint-card">
            <span class="hint-icon">💡</span>
            <div class="hint-content">
              <p class="hint-title">Escribe al menos 2 letras</p>
              <p class="hint-text">Busca por nombre de médico o especialidad</p>
            </div>
          </div>
        </div>
        
        <div v-else-if="medicos.length === 0 && especialidades.length === 0 && searchQuery.length >= 2" class="no-results">
          <div class="no-results-content">
            <div class="no-results-icon-wrapper">
              <span class="no-results-icon">🔍</span>
            </div>
            <h3 class="no-results-title">No encontramos resultados</h3>
            <p class="no-results-text">No hay médicos o especialidades que coincidan con <span class="search-term">"{{ searchQuery }}"</span></p>
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
          <!-- Médicos -->
          <div v-if="medicos.length > 0" class="results-section">
            <div class="results-header">
              <span class="section-icon">👨‍⚕️</span>
              <span class="section-title">Médicos encontrados</span>
              <span class="section-count">({{ medicos.length }})</span>
            </div>
            <div class="results-list">
              <div
                v-for="medico in medicos"
                :key="`medico-${medico.id}-${medico.especialidad_id}`"
                class="result-item doctor-item-enhanced"
              >
                <div class="doctor-card-header">
                  <div class="result-avatar">
                    <span class="avatar-icon">👨‍⚕️</span>
                    <div v-if="medico.verif_status === 'verificado'" class="verificado-badge-small">
                      <span class="badge-icon">✓</span>
                    </div>
                  </div>
                  <div class="result-content">
                    <div class="result-header-info">
                      <div class="result-name">{{ medico.nombre }}</div>
                      <div class="result-badges">
                        <div class="result-badge available">Disponible</div>
                        <div v-if="medico.verif_status === 'verificado'" class="result-badge verified">
                          <span class="badge-icon">✓</span>
                          <span>Verificado</span>
                        </div>
                      </div>
                    </div>
                    <div class="result-meta">
                      <span class="meta-icon">🏥</span>
                      <span>{{ medico.especialidad_nombre }}</span>
                    </div>
                    <div v-if="medico.rating && medico.rating.promedio > 0" class="result-rating">
                      <span class="rating-stars">{{ getStars(medico.rating.promedio) }}</span>
                      <span class="rating-value">{{ medico.rating.promedio }}</span>
                      <span class="rating-count">({{ medico.rating.total_resenas }} reseña{{ medico.rating.total_resenas !== 1 ? 's' : '' }})</span>
                    </div>
                    <div v-else class="result-rating">
                      <span class="no-rating">Sin reseñas aún</span>
                    </div>
                    <div v-if="medico.stats" class="result-stats">
                      <div class="stat-item">
                        <span class="stat-icon">✅</span>
                        <span class="stat-value">{{ medico.stats.citas_completadas }}</span>
                        <span class="stat-label">Citas</span>
                      </div>
                      <div class="stat-item">
                        <span class="stat-icon">👥</span>
                        <span class="stat-value">{{ medico.stats.pacientes_atendidos }}</span>
                        <span class="stat-label">Pacientes</span>
                      </div>
                      <div class="stat-item">
                        <span class="stat-icon">⭐</span>
                        <span class="stat-value">{{ medico.stats.seguidores }}</span>
                        <span class="stat-label">Seguidores</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="doctor-card-actions">
                  <button 
                    class="btn-action btn-reservar"
                    @mousedown.prevent="reservarCita(medico)"
                    title="Reservar cita directamente"
                  >
                    <span class="btn-icon">📅</span>
                    <span>Reservar Cita</span>
                  </button>
                  <button 
                    class="btn-action btn-perfil"
                    @mousedown.prevent="selectMedico(medico)"
                    title="Ver perfil completo"
                  >
                    <span class="btn-icon">👁️</span>
                    <span>Ver Perfil</span>
                  </button>
                </div>
              </div>
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
                class="result-item specialty-item"
                @mousedown.prevent="selectEspecialidad(esp)"
              >
                <div class="result-avatar">
                  <span class="avatar-icon">🏥</span>
                </div>
                <div class="result-content">
                  <div class="result-name">{{ esp.nombre }}</div>
                  <div class="result-meta">
                    <span>Ver médicos de esta especialidad</span>
                  </div>
                </div>
                <div class="result-action">
                  <span class="action-text">Ver</span>
                  <span class="action-arrow">→</span>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

const searchQuery = ref('')
const isFocused = ref(false)
const loading = ref(false)
const medicos = ref([])
const especialidades = ref([])
const resultsVisible = ref(false)
const searchTimeout = ref(null)

// Debounce manual para la búsqueda
async function performSearch(query) {
  if (!query || query.trim().length < 2) {
    medicos.value = []
    especialidades.value = []
    resultsVisible.value = false
    return
  }

  loading.value = true
  resultsVisible.value = true
  
  try {
    const { data } = await api.get('/public/search', {
      params: { q: query.trim() }
    })
    
    medicos.value = Array.isArray(data?.medicos) ? data.medicos : []
    especialidades.value = Array.isArray(data?.especialidades) ? data.especialidades : []
  } catch (error) {
    console.error('Error en búsqueda:', error)
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
    medicos.value = []
    especialidades.value = []
    resultsVisible.value = false
    loading.value = false
    return
  }
  
  // Si tiene menos de 2 caracteres, mostrar hint pero no buscar
  if (query.length < 2) {
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
  medicos.value = []
  especialidades.value = []
  resultsVisible.value = false
  isFocused.value = false
}

function closeResults() {
  resultsVisible.value = false
  isFocused.value = false
}

function selectMedico(medico) {
  // Navegar al perfil público del médico
  console.log('🔍 Navegando al perfil del médico:', medico.id, medico.nombre)
  router.push({
    name: 'medico.perfil.publico',
    params: { id: String(medico.id) }
  }).catch(err => {
    console.error('Error al navegar al perfil:', err)
    // Si falla, intentar con path directo
    router.push(`/doctor/${medico.id}`)
  })
  clearSearch()
}

function reservarCita(medico) {
  // Navegar directamente a reservar cita con el médico y especialidad preseleccionados
  console.log('📅 Reservando cita con médico:', medico.id, medico.nombre)
  router.push({
    name: 'paciente.reservar',
    query: {
      medico_id: medico.id,
      especialidad_id: medico.especialidad_id
    }
  })
  clearSearch()
}

function getStars(rating) {
  if (!rating || rating <= 0) return '☆☆☆☆☆'
  const full = Math.floor(rating)
  const hasHalf = rating % 1 >= 0.5
  return '⭐'.repeat(full) + (hasHalf ? '✨' : '') + '☆'.repeat(5 - full - (hasHalf ? 1 : 0))
}

function selectEspecialidad(especialidad) {
  // Navegar a la página de reservar cita con la especialidad preseleccionada
  router.push({
    name: 'paciente.reservar',
    query: {
      especialidad_id: especialidad.id
    }
  })
  clearSearch()
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
  color: rgba(255,255,255,0.7);
  line-height: 1.4;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255,255,255,0.2);
  border-top-color: rgba(127,59,243,0.8);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
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
  gap: 4px;
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

.doctor-item:hover {
  border-color: rgba(255,42,136,0.5);
  background: rgba(255,42,136,0.08);
}

.doctor-item:hover::before {
  background: linear-gradient(135deg, #ff2a88, #ff6ba8);
}

/* Estilos mejorados para tarjetas de médicos */
.result-item.doctor-item-enhanced {
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

.result-item.doctor-item-enhanced:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(127,59,243,0.5);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(127,59,243,0.2);
}

.result-item.doctor-item-enhanced::before {
  display: none;
}

.doctor-card-header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.result-avatar {
  position: relative;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(127,59,243,0.3), rgba(0,245,255,0.3));
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 2px solid rgba(255,255,255,0.1);
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

.verificado-badge-small .badge-icon {
  font-size: 12px;
  color: #fff;
  font-weight: bold;
}

.result-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.result-header-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.result-name {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  line-height: 1.2;
}

.result-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.result-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.result-badge.available {
  background: rgba(0,245,255,0.15);
  color: #00f5ff;
  border: 1px solid rgba(0,245,255,0.3);
}

.result-badge.verified {
  background: rgba(127,59,243,0.15);
  color: #7f3bf3;
  border: 1px solid rgba(127,59,243,0.3);
}

.result-badge .badge-icon {
  font-size: 12px;
}

.result-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  color: rgba(234,246,255,0.7);
  font-size: 14px;
}

.meta-icon {
  font-size: 16px;
}

.result-rating {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.rating-stars {
  font-size: 14px;
  letter-spacing: 1px;
}

.rating-value {
  font-weight: 700;
  color: #fff;
}

.rating-count {
  color: rgba(234,246,255,0.6);
  font-size: 13px;
}

.no-rating {
  color: rgba(234,246,255,0.5);
  font-size: 13px;
  font-style: italic;
}

.result-stats {
  display: flex;
  gap: 16px;
  margin-top: 4px;
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

.doctor-card-actions {
  display: flex;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.btn-action {
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
}

.btn-action .btn-icon {
  font-size: 16px;
}

.btn-reservar {
  background: linear-gradient(135deg, #ff006e, #00f5ff);
  color: #fff;
  box-shadow: 0 4px 12px rgba(255,0,110,0.3);
}

.btn-reservar:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255,0,110,0.4);
}

.btn-perfil {
  background: rgba(255,255,255,0.08);
  color: rgba(234,246,255,0.9);
  border: 1px solid rgba(255,255,255,0.15);
}

.btn-perfil:hover {
  background: rgba(255,255,255,0.12);
  border-color: rgba(127,59,243,0.5);
  transform: translateY(-2px);
}

.specialty-item:hover {
  border-color: rgba(0,245,255,0.5);
  background: rgba(0,245,255,0.08);
}

.specialty-item:hover::before {
  background: linear-gradient(135deg, #00f5ff, #7fd6ff);
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
  gap: 10px;
}

.result-badge {
  padding: 4px 10px;
  background: rgba(0,245,255,0.2);
  border: 1px solid rgba(0,245,255,0.4);
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  color: rgba(0,245,255,0.9);
}

.result-hint {
  font-size: 12px;
  color: rgba(234,246,255,0.6);
  margin-top: 2px;
}

.result-name {
  font-weight: 700;
  font-size: 15px;
  color: rgba(234,246,255,0.98);
}

.result-meta {
  font-size: 13px;
  color: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
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
  border-radius: 10px;
  font-size: 14px;
  color: rgba(234,246,255,0.9);
  transition: all 0.3s ease;
}

.suggestion-item:hover {
  background: rgba(127,59,243,0.15);
  border-color: rgba(127,59,243,0.4);
  transform: translateY(-2px);
}

.suggestion-icon {
  font-size: 18px;
  opacity: 0.9;
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

/* Scrollbar personalizado */
.search-results::-webkit-scrollbar {
  width: 8px;
}

.search-results::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.05);
  border-radius: 4px;
}

.search-results::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.2);
  border-radius: 4px;
}

.search-results::-webkit-scrollbar-thumb:hover {
  background: rgba(255,255,255,0.3);
}

@media (max-width: 980px) {
  .search-wrapper {
    display: none;
  }
}

@media (max-width: 768px) {
  .search-wrapper {
    max-width: 100%;
  }
  
  .search-results {
    max-height: 400px;
  }
}
</style>


