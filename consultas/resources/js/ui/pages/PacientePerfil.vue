<template>
  <section class="page-slot paciente-perfil-page">
    <div class="container perfil-container">
      <header class="page-header">
        <div>
          <h1>Mi Perfil 👤</h1>
          <p>Gestiona tu información personal y revisa tu historial médico.</p>
        </div>
        <button class="btn-soft" @click="volver">← Volver al panel</button>
      </header>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando tu perfil...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <span class="error-icon">⚠️</span>
        <h2>Error al cargar el perfil</h2>
        <p>{{ error }}</p>
        <button class="btn-primary" @click="cargarPerfil">Reintentar</button>
      </div>

      <div v-else-if="paciente" class="perfil-content">
        <!-- Información Personal -->
        <div class="section-card">
          <div class="section-header">
            <h2>
              <span class="section-icon">👤</span>
              Información Personal
            </h2>
            <button 
              class="btn-edit-toggle" 
              @click="editando = !editando"
              :class="{ active: editando }"
            >
              {{ editando ? '✕ Cancelar' : '✏️ Editar' }}
            </button>
          </div>

          <form v-if="editando" @submit.prevent="guardarPerfil" class="form-edit">
            <div class="form-grid">
              <label class="form-field">
                <span class="field-label">Nombre completo</span>
                <input 
                  type="text" 
                  v-model="formData.name" 
                  class="form-input"
                  required
                />
              </label>

              <label class="form-field">
                <span class="field-label">Correo electrónico</span>
                <input 
                  type="email" 
                  v-model="formData.email" 
                  class="form-input"
                  disabled
                />
                <span class="field-hint">El correo no se puede modificar</span>
              </label>

              <label class="form-field">
                <span class="field-label">Teléfono</span>
                <input 
                  type="tel" 
                  v-model="formData.phone" 
                  class="form-input"
                />
              </label>

              <label class="form-field">
                <span class="field-label">Tipo de documento</span>
                <select v-model="formData.doc_tipo" class="form-input">
                  <option value="">Seleccionar...</option>
                  <option value="DNI">DNI</option>
                  <option value="CE">Cédula de Extranjería</option>
                  <option value="PAS">Pasaporte</option>
                </select>
              </label>

              <label class="form-field">
                <span class="field-label">Número de documento</span>
                <input 
                  type="text" 
                  v-model="formData.doc_numero" 
                  class="form-input"
                />
              </label>

              <label class="form-field">
                <span class="field-label">Fecha de nacimiento</span>
                <input 
                  type="date" 
                  v-model="formData.birthdate" 
                  class="form-input"
                />
              </label>

              <label class="form-field">
                <span class="field-label">Género</span>
                <select v-model="formData.gender" class="form-input">
                  <option value="">Seleccionar...</option>
                  <option value="M">Masculino</option>
                  <option value="F">Femenino</option>
                  <option value="X">No binario</option>
                </select>
              </label>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary" :disabled="saving">
                <span v-if="saving" class="btn-spinner">⏳</span>
                <span v-else>💾 Guardar Cambios</span>
              </button>
            </div>

            <div v-if="formError" class="form-message error">
              <span class="message-icon">⚠️</span>
              <span>{{ formError }}</span>
            </div>
            <div v-if="formSuccess" class="form-message success">
              <span class="message-icon">✅</span>
              <span>{{ formSuccess }}</span>
            </div>
          </form>

          <div v-else class="info-display">
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Nombre</span>
                <span class="info-value">{{ paciente.nombre }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Correo electrónico</span>
                <span class="info-value">{{ paciente.email }}</span>
                <span v-if="paciente.email_verified_at" class="verified-badge">
                  ✓ Verificado
                </span>
              </div>
              <div class="info-item">
                <span class="info-label">Teléfono</span>
                <span class="info-value">{{ paciente.phone || 'No registrado' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Documento</span>
                <span class="info-value">
                  {{ paciente.doc_tipo ? `${paciente.doc_tipo}: ${paciente.doc_numero}` : 'No registrado' }}
                </span>
              </div>
              <div class="info-item">
                <span class="info-label">Fecha de nacimiento</span>
                <span class="info-value">
                  {{ paciente.birthdate ? formatDate(paciente.birthdate) : 'No registrado' }}
                  <span v-if="paciente.edad" class="edad-badge">({{ paciente.edad }} años)</span>
                </span>
              </div>
              <div class="info-item">
                <span class="info-label">Género</span>
                <span class="info-value">{{ formatGender(paciente.gender) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.total_citas }}</div>
              <div class="stat-label">Total Citas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.citas_pendientes }}</div>
              <div class="stat-label">Próximas Citas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.citas_completadas }}</div>
              <div class="stat-label">Completadas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.citas_canceladas }}</div>
              <div class="stat-label">Canceladas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.medicos_favoritos }}</div>
              <div class="stat-label">Médicos Favoritos</div>
            </div>
          </div>
        </div>

        <!-- Próximas Citas -->
        <div class="section-card">
          <h2>
            <span class="section-icon">📅</span>
            Próximas Citas
          </h2>
          <div v-if="proximasCitas.length" class="citas-list">
            <div 
              v-for="cita in proximasCitas" 
              :key="cita.id"
              class="cita-card"
            >
              <div class="cita-header">
                <div class="cita-fecha">
                  <span class="fecha-dia">{{ formatDateShort(cita.fecha) }}</span>
                  <span class="fecha-hora">{{ cita.hora }}</span>
                </div>
                <span class="cita-estado" :class="getEstadoClass(cita.estado)">
                  {{ cita.estado }}
                </span>
              </div>
              <div class="cita-content">
                <div class="cita-medico">
                  <span class="cita-icon">👨‍⚕️</span>
                  <span>{{ cita.medico_nombre }}</span>
                </div>
                <div class="cita-especialidad">
                  <span class="cita-icon">🏥</span>
                  <span>{{ cita.especialidad_nombre }}</span>
                </div>
                <div v-if="cita.motivo" class="cita-motivo">
                  <span class="cita-icon">💬</span>
                  <span>{{ cita.motivo }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <span class="empty-icon">📅</span>
            <p>No tienes citas próximas programadas.</p>
            <button class="btn-primary" @click="irAReservar">Reservar una cita</button>
          </div>
        </div>

        <!-- Historial -->
        <div class="section-card">
          <h2>
            <span class="section-icon">📋</span>
            Historial de Citas
          </h2>
          <div v-if="historial.length" class="citas-list">
            <div 
              v-for="cita in historial" 
              :key="cita.id"
              class="cita-card historial-card"
            >
              <div class="cita-header">
                <div class="cita-fecha">
                  <span class="fecha-dia">{{ cita.fecha }}</span>
                  <span class="fecha-hora">{{ cita.hora }}</span>
                </div>
                <span class="cita-estado" :class="getEstadoClass(cita.estado)">
                  {{ cita.estado }}
                </span>
              </div>
              <div class="cita-content">
                <div class="cita-medico">
                  <span class="cita-icon">👨‍⚕️</span>
                  <span>{{ cita.medico_nombre }}</span>
                </div>
                <div class="cita-especialidad">
                  <span class="cita-icon">🏥</span>
                  <span>{{ cita.especialidad_nombre }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <span class="empty-icon">📋</span>
            <p>Aún no tienes citas en tu historial.</p>
          </div>
        </div>

        <!-- Médicos Favoritos -->
        <div class="section-card">
          <h2>
            <span class="section-icon">⭐</span>
            Médicos Favoritos
          </h2>
          <div v-if="favoritos.length" class="favoritos-list">
            <div 
              v-for="medico in favoritos" 
              :key="medico.id"
              class="medico-favorito-card"
              @click="verMedico(medico.id)"
            >
              <div class="medico-avatar">
                <span class="avatar-icon">👨‍⚕️</span>
                <div v-if="medico.verif_status === 'verificado'" class="verificado-badge-small">
                  <span class="badge-icon">✓</span>
                </div>
              </div>
              <div class="medico-info">
                <div class="medico-nombre">{{ medico.nombre }}</div>
                <div class="medico-especialidad">{{ medico.especialidad_nombre }}</div>
              </div>
              <div class="medico-action">
                <span class="action-arrow">→</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <span class="empty-icon">⭐</span>
            <p>No tienes médicos favoritos aún.</p>
            <button class="btn-primary" @click="irABuscar">Buscar médicos</button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

const loading = ref(true)
const error = ref(null)
const paciente = ref(null)
const stats = ref({})
const proximasCitas = ref([])
const historial = ref([])
const favoritos = ref([])

const editando = ref(false)
const saving = ref(false)
const formError = ref('')
const formSuccess = ref('')
const formData = ref({
  name: '',
  phone: '',
  doc_tipo: '',
  doc_numero: '',
  birthdate: '',
  gender: '',
})

onMounted(() => {
  cargarPerfil()
})

async function cargarPerfil() {
  loading.value = true
  error.value = null
  
  try {
    const { data } = await api.get('/paciente/perfil')
    
    paciente.value = data.paciente
    stats.value = data.stats || {}
    proximasCitas.value = data.proximas_citas || []
    historial.value = data.historial || []
    favoritos.value = data.favoritos || []
    
    // Inicializar formulario con datos actuales
    formData.value = {
      name: paciente.value.nombre,
      email: paciente.value.email,
      phone: paciente.value.phone || '',
      doc_tipo: paciente.value.doc_tipo || '',
      doc_numero: paciente.value.doc_numero || '',
      birthdate: paciente.value.birthdate || '',
      gender: paciente.value.gender || '',
    }
  } catch (err) {
    console.error('Error al cargar perfil:', err)
    error.value = err?.response?.data?.message || 'No se pudo cargar el perfil'
  } finally {
    loading.value = false
  }
}

async function guardarPerfil() {
  saving.value = true
  formError.value = ''
  formSuccess.value = ''
  
  try {
    await api.put('/paciente/perfil', formData.value)
    formSuccess.value = 'Perfil actualizado correctamente'
    
    // Recargar datos
    await cargarPerfil()
    
    // Cerrar edición después de 2 segundos
    setTimeout(() => {
      editando.value = false
      formSuccess.value = ''
    }, 2000)
  } catch (err) {
    console.error('Error al guardar perfil:', err)
    formError.value = err?.response?.data?.message || 'Error al actualizar el perfil'
  } finally {
    saving.value = false
  }
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString + 'T00:00:00')
  return date.toLocaleDateString('es-ES', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

function formatDateShort(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString + 'T00:00:00')
  return date.toLocaleDateString('es-ES', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

function formatGender(gender) {
  if (!gender) return 'No especificado'
  const map = {
    'M': 'Masculino',
    'F': 'Femenino',
    'X': 'No binario',
  }
  return map[gender] || gender
}

function getEstadoClass(estado) {
  const e = estado?.toLowerCase() || ''
  if (e === 'confirmada') return 'status-confirmed'
  if (e === 'completada') return 'status-done'
  if (e === 'cancelada') return 'status-cancel'
  return 'status-pending'
}

function verMedico(medicoId) {
  router.push({
    name: 'medico.perfil.publico',
    params: { id: String(medicoId) }
  })
}

function irAReservar() {
  router.push({ name: 'paciente.reservar' })
}

function irABuscar() {
  router.push({ name: 'paciente.home' })
}

function volver() {
  router.push({ name: 'paciente.home' })
}
</script>

<style scoped>
.paciente-perfil-page {
  padding: 40px 20px;
  min-height: 100vh;
}

.perfil-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  gap: 20px;
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

.loading-state, .error-state {
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

.section-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 30px;
  margin-bottom: 30px;
  backdrop-filter: blur(10px);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.section-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-icon {
  font-size: 28px;
}

.btn-edit-toggle {
  padding: 10px 20px;
  border-radius: 12px;
  border: 1px solid rgba(127,59,243,0.3);
  background: rgba(127,59,243,0.1);
  color: #7f3bf3;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-edit-toggle:hover {
  background: rgba(127,59,243,0.2);
  border-color: rgba(127,59,243,0.5);
}

.btn-edit-toggle.active {
  background: rgba(239,68,68,0.1);
  border-color: rgba(239,68,68,0.3);
  color: #ef4444;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  font-size: 14px;
  font-weight: 600;
  color: rgba(234,246,255,0.9);
}

.form-input {
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: #fff;
  font-size: 15px;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #7f3bf3;
  background: rgba(255,255,255,0.08);
}

.form-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.field-hint {
  font-size: 12px;
  color: rgba(234,246,255,0.5);
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
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
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127,59,243,0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-message {
  margin-top: 16px;
  padding: 12px 16px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.form-message.error {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.3);
  color: #fca5a5;
}

.form-message.success {
  background: rgba(34,197,94,0.1);
  border: 1px solid rgba(34,197,94,0.3);
  color: #86efac;
}

.info-display {
  margin-top: 20px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-label {
  font-size: 13px;
  font-weight: 600;
  color: rgba(234,246,255,0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 16px;
  font-weight: 600;
  color: rgba(234,246,255,0.98);
  display: flex;
  align-items: center;
  gap: 8px;
}

.verified-badge {
  padding: 4px 10px;
  border-radius: 8px;
  background: rgba(34,197,94,0.15);
  color: #86efac;
  border: 1px solid rgba(34,197,94,0.3);
  font-size: 11px;
  font-weight: 600;
}

.edad-badge {
  padding: 4px 10px;
  border-radius: 8px;
  background: rgba(127,59,243,0.15);
  color: #a78bfa;
  font-size: 12px;
  font-weight: 600;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s;
}

.stat-card:hover {
  background: rgba(255,255,255,0.08);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(127,59,243,0.2);
}

.stat-icon {
  font-size: 36px;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #fff;
  line-height: 1;
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
  margin-top: 20px;
}

.cita-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 20px;
  transition: all 0.2s;
}

.cita-card:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(127,59,243,0.3);
}

.cita-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.cita-fecha {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.fecha-dia {
  font-size: 16px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
}

.fecha-hora {
  font-size: 14px;
  color: rgba(234,246,255,0.7);
}

.cita-estado {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
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

.cita-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.cita-medico, .cita-especialidad, .cita-motivo {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: rgba(234,246,255,0.8);
}

.cita-icon {
  font-size: 16px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: rgba(234,246,255,0.6);
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
  opacity: 0.5;
}

.favoritos-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.medico-favorito-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.medico-favorito-card:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(127,59,243,0.3);
  transform: translateY(-2px);
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

.medico-info {
  flex: 1;
}

.medico-nombre {
  font-size: 16px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 4px;
}

.medico-especialidad {
  font-size: 13px;
  color: rgba(234,246,255,0.6);
}

.medico-action {
  font-size: 20px;
  color: rgba(234,246,255,0.4);
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