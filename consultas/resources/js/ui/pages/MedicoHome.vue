<template>
  <!-- HEADER DEL PANEL (idéntico al de Paciente) -->
  <header class="mr-dashbar">
    <div class="mr-dh">
      <!-- Marca -->
      <div class="mr-brand">
        <span class="mr-bolt">⚡</span>
        <span class="mr-word">MediReserva</span>
      </div>

      <!-- Botón de búsqueda -->
      <button 
        type="button" 
        class="mr-search-btn"
        @click="$router.push({ name: 'medico.buscar' })"
        title="Buscar pacientes, citas, médicos..."
      >
        <span class="search-icon">🔍</span>
        <span class="search-text">Buscar</span>
      </button>

      <!-- Usuario -->
      <button type="button" class="mr-userpill" @click.stop="menuOpen = !menuOpen">
        <span class="mr-avatar">🩺</span>
        <span class="mr-usertext">
          <strong>{{ doctorName }}</strong>
          <small>Médico</small>
        </span>
        <span class="mr-caret">▾</span>
      </button>

      <!-- Menú -->
      <div v-if="menuOpen" class="mr-menu">
        <a @click.prevent="$router.push('/medico')">Mi panel</a>
        <a @click.prevent="$router.push('/medico/perfil')">Perfil</a>
        <a @click.prevent="$router.push('/medico/notificaciones')">Notificaciones</a>
        <hr />
        <a @click.prevent="logout">Salir</a>
      </div>
    </div>
  </header>

  <!-- CONTENIDO -->
  <section class="page-slot">
    <div class="container">
      <!-- TOP SECTION: Welcome + Date Selector -->
      <div class="top-section">
      <!-- WELCOME -->
      <div class="welcome-section">
          <div class="welcome-header">
            <div class="welcome-title-group">
              <h1>¡Hola, {{ doctorName }}! <span class="wave-emoji">👋</span></h1>
              <div class="welcome-meta">
                <span class="role-badge">
                  <span class="role-icon">🩺</span>
                  <span>Médico</span>
                </span>
                <span class="verif-badge" :class="badgeClass">
                  <span class="verif-icon">✓</span>
                  <span>Verificación: {{ formatVerifStatus(verifStatus) }}</span>
                </span>
              </div>
        </div>
        <div class="welcome-actions">
              <button class="btn-primary-action" @click="goHorarios">
                <span class="btn-icon">⚙️</span>
                <span>Configurar Horarios</span>
              </button>
              <button class="btn-secondary-action" @click="cargarAgenda" :disabled="loading">
                <span v-if="loading" class="btn-spinner">⏳</span>
                <span v-else class="btn-icon">⟳</span>
                <span>{{ loading ? 'Actualizando…' : 'Refrescar' }}</span>
          </button>
            </div>
        </div>
      </div>

        <!-- DATE SELECTOR -->
        <div class="date-selector-section">
          <div class="date-selector-content">
            <label class="date-label">
              <span class="label-icon">📅</span>
              <span>Ver citas del día:</span>
            </label>
            <input
              type="date"
              v-model="fechaSeleccionada"
              @change="cambiarFecha"
              class="date-input"
            />
            <button class="btn-today" @click="irAHoy" title="Ir a hoy">
              <span>Hoy</span>
            </button>
          </div>
        </div>
      </div>

      <!-- QUICK STATS -->
      <div class="quick-stats">
        <div class="stat-card stat-total">
          <div class="stat-header">
          <div class="stat-icon">📅</div>
            <div class="stat-trend" v-if="totalHoy > 0">↑</div>
          </div>
          <div class="stat-value">{{ totalHoy }}</div>
          <div class="stat-label">Citas Hoy</div>
        </div>
        <div class="stat-card stat-confirmed">
          <div class="stat-header">
          <div class="stat-icon">✅</div>
            <div class="stat-trend" v-if="confirmadasHoy > 0">✓</div>
          </div>
          <div class="stat-value">{{ confirmadasHoy }}</div>
          <div class="stat-label">Confirmadas</div>
        </div>
        <div class="stat-card stat-pending">
          <div class="stat-header">
          <div class="stat-icon">⏳</div>
            <div class="stat-trend" v-if="pendientesHoy > 0">!</div>
          </div>
          <div class="stat-value">{{ pendientesHoy }}</div>
          <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card stat-completed">
          <div class="stat-header">
          <div class="stat-icon">🧾</div>
            <div class="stat-trend" v-if="completadasHoy > 0">✓</div>
          </div>
          <div class="stat-value">{{ completadasHoy }}</div>
          <div class="stat-label">Completadas</div>
        </div>
      </div>

      <!-- FILTROS -->
      <div class="filters-section">
        <div class="filter-tabs">
          <button
            class="filter-tab"
            :class="{ active: filtro === 'todas' }"
            @click="filtro = 'todas'"
          >
            <span class="tab-icon">📋</span>
            <span>Todas</span>
            <span class="tab-count">{{ agenda.length }}</span>
          </button>
          <button
            class="filter-tab"
            :class="{ active: filtro === 'pendiente' }"
            @click="filtro = 'pendiente'"
          >
            <span class="tab-icon">⏳</span>
            <span>Pendientes</span>
            <span class="tab-count">{{ pendientesHoy }}</span>
          </button>
          <button
            class="filter-tab"
            :class="{ active: filtro === 'confirmada' }"
            @click="filtro = 'confirmada'"
          >
            <span class="tab-icon">✅</span>
            <span>Confirmadas</span>
            <span class="tab-count">{{ confirmadasHoy }}</span>
          </button>
          <button
            class="filter-tab"
            :class="{ active: filtro === 'completada' }"
            @click="filtro = 'completada'"
          >
            <span class="tab-icon">✓</span>
            <span>Completadas</span>
            <span class="tab-count">{{ completadasHoy }}</span>
          </button>
        </div>
      </div>

      <!-- AGENDA -->
      <div class="appointments-section">
        <div class="section-header">
          <div class="section-title">
            <span class="title-icon">📒</span>
            <span>Agenda del Día</span>
            <span v-if="agendaFiltrada.length > 0" class="title-count">({{ agendaFiltrada.length }})</span>
          </div>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Cargando citas...</p>
        </div>

        <div v-else-if="!agendaFiltrada.length" class="empty-state">
          <div class="empty-illustration">
            <div class="empty-icon">📅</div>
            <div class="empty-pulse"></div>
          </div>
          <h3>No hay citas para mostrar</h3>
          <p v-if="filtro !== 'todas'">
            No hay citas con el estado "<strong>{{ formatEstado(filtro) }}</strong>" para esta fecha.
          </p>
          <p v-else>
            No tienes citas programadas para <strong>{{ new Date(fechaSeleccionada).toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</strong>.
          </p>
          <div class="empty-actions">
            <button class="btn-empty-primary" @click="goHorarios">
              <span class="btn-icon">⚙️</span>
              <span>Configurar Horarios</span>
            </button>
            <button class="btn-empty-secondary" @click="irAHoy" v-if="fechaSeleccionada !== new Date().toISOString().slice(0,10)">
              <span class="btn-icon">📅</span>
              <span>Ver citas de hoy</span>
            </button>
          </div>
        </div>

        <div v-else class="appointments-grid">
        <div
          v-for="c in agendaFiltrada"
          :key="c.id"
          class="appointment-card"
            :class="`appointment-${c.estado}`"
        >
          <div class="appointment-header">
              <div class="patient-info">
                <div class="patient-avatar" :class="statusPill(c.estado)">
                  <span class="avatar-icon">👤</span>
              </div>
                <div class="patient-details">
                  <h3 class="patient-name">{{ c.paciente }}</h3>
                  <div class="appointment-meta">
                    <span class="appointment-id">Cita #{{ c.id }}</span>
                    <span class="meta-separator">•</span>
                    <span class="appointment-time-meta">{{ c.hora }}</span>
            </div>
                </div>
              </div>
              <div class="appointment-status" :class="statusPill(c.estado)">
                <span class="status-dot"></span>
                <span class="status-text">{{ formatEstado(c.estado) }}</span>
              </div>
          </div>

            <div class="appointment-body">
              <div class="appointment-detail-row">
                <div class="detail-item">
                  <span class="detail-icon">🕒</span>
                  <div class="detail-content">
                    <span class="detail-label">Hora</span>
                    <span class="detail-value">{{ c.hora }}</span>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">📍</span>
                  <div class="detail-content">
                    <span class="detail-label">Ubicación</span>
                    <span class="detail-value">{{ c.lugar || 'Presencial/Telemedicina' }}</span>
                  </div>
                </div>
              </div>
          </div>

          <div class="appointment-actions">
            <button
              v-if="c.estado==='pendiente'"
                class="btn-action btn-confirm"
              @click="accion(c.id,'confirmar')"
              :disabled="busyId===c.id"
              >
                <span v-if="busyId===c.id" class="btn-spinner">⏳</span>
                <span v-else class="btn-icon">✓</span>
                <span>Confirmar</span>
              </button>

            <button
              v-if="c.estado==='pendiente'"
              class="btn-action btn-cancel"
              @click="accion(c.id,'cancelar')"
              :disabled="busyId===c.id"
              >
                <span v-if="busyId===c.id" class="btn-spinner">⏳</span>
                <span v-else class="btn-icon">✕</span>
                <span>Cancelar</span>
              </button>

            <button
              v-if="c.estado==='confirmada'"
                class="btn-action btn-complete"
              @click="accion(c.id,'completar')"
              :disabled="busyId===c.id"
              >
                <span v-if="busyId===c.id" class="btn-spinner">⏳</span>
                <span v-else class="btn-icon">✓</span>
                <span>Completar</span>
              </button>

            <button
              v-if="c.estado==='confirmada'"
              class="btn-action btn-cancel"
              @click="accion(c.id,'cancelar')"
              :disabled="busyId===c.id"
              >
                <span v-if="busyId===c.id" class="btn-spinner">⏳</span>
                <span v-else class="btn-icon">✕</span>
                <span>Cancelar</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import axios from 'axios'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const menuOpen = ref(false)
function handleOutside(e){ if (!e.target.closest('.mr-dh')) menuOpen.value = false }
onMounted(()=> document.addEventListener('click', handleOutside))
onBeforeUnmount(()=> document.removeEventListener('click', handleOutside))

// --- Auth token y cliente
const t = localStorage.getItem('token') || localStorage.getItem('auth_token') || sessionStorage.getItem('token')
if (t) axios.defaults.headers.common.Authorization = `Bearer ${t}`
axios.defaults.withCredentials = true

async function logout () {
  try { await axios.post('/api/auth/logout') } catch {}
  localStorage.removeItem('token'); localStorage.removeItem('auth_token'); sessionStorage.removeItem('token')
  router.push('/login')
}

// --- Datos del médico y agenda
const doctorName   = ref('Dr./Dra.')
const verifStatus  = ref('pendiente')
const agenda       = ref([])
const filtro       = ref('todas')
const loading      = ref(false)
const busyId       = ref(null)
const fechaSeleccionada = ref(new Date().toISOString().slice(0,10))

const badgeClass = computed(() => ({
  'text-yellow': verifStatus.value === 'pendiente',
  'text-green' : verifStatus.value === 'verificado',
  'text-red'   : verifStatus.value === 'rechazado',
}))

const totalHoy       = computed(() => agenda.value.length)
const pendientesHoy  = computed(() => agenda.value.filter(a=>a.estado==='pendiente').length)
const confirmadasHoy = computed(() => agenda.value.filter(a=>a.estado==='confirmada').length)
const completadasHoy = computed(() => agenda.value.filter(a=>a.estado==='completada').length)
const agendaFiltrada = computed(() => (filtro.value==='todas' ? agenda.value : agenda.value.filter(a=>a.estado===filtro.value)))

function statusPill(estado=''){
  const e = estado.toLowerCase()
  if (e === 'confirmada') return 'status-confirmed'
  if (e === 'completada') return 'status-done'
  if (e === 'cancelada')  return 'status-cancel'
  return 'status-pending'
}

async function cargarAgenda () {
  loading.value = true
  try {
    const { data } = await axios.get('/api/medico/citas', { params: { date: fechaSeleccionada.value } })
    agenda.value = (data ?? []).map(r => ({
      id: r.id,
      hora: new Date(r.starts_at).toTimeString().slice(0,5),
      paciente: r.paciente,
      estado: r.estado || 'pendiente',
      lugar: r.lugar || null,
    }))
  } finally { loading.value = false }
}

function cambiarFecha() {
  cargarAgenda()
}

function irAHoy() {
  fechaSeleccionada.value = new Date().toISOString().slice(0,10)
  cargarAgenda()
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

function formatVerifStatus(status) {
  const map = {
    'pendiente': 'Pendiente',
    'verificado': 'Verificado',
    'rechazado': 'Rechazado',
  }
  return map[status] || status
}

async function accion(id, tipo){
  const path = { confirmar:'confirmar', completar:'completar', cancelar:'cancelar' }[tipo]
  if (!path) return
  
  // Confirmaciones antes de acciones críticas
  let confirmar = true
  if (tipo === 'cancelar') {
    confirmar = confirm('¿Estás seguro de que deseas cancelar esta cita? Esta acción no se puede deshacer.')
  } else if (tipo === 'completar') {
    confirmar = confirm('¿Confirmas que esta cita ha sido completada?')
  }
  
  if (!confirmar) return
  
  busyId.value = id
  try {
    await axios.post(`/api/medico/citas/${id}/${path}`)
    await cargarAgenda()
  } catch (e) {
    alert(e?.response?.data?.message || 'Operación no disponible')
  } finally { busyId.value = null }
}

function goHorarios(){ router.push('/medico/horarios') }

onMounted(async ()=>{
  try {
    const me = await axios.get('/api/auth/me')
    doctorName.value  = me.data?.user?.name || doctorName.value
    verifStatus.value = me.data?.user?.verif_status ?? me.data?.verif_status ?? 'pendiente'
  } catch {}
  await cargarAgenda()
})
</script>

<style scoped>
/* ===== Tipografías de estado pequeñas (para el badge del héroe) ===== */
.text-yellow{ color:#fde68a } .text-green{ color:#86efac } .text-red{ color:#fca5a5 }

/* Animaciones */
@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* ==================== */
/* == HEADER (mr-*)  == */
/* ==================== */
.mr-dashbar{
  position:sticky; top:0; z-index:30;
  background: linear-gradient(180deg, rgba(10,1,24,.92) 0%, rgba(10,1,24,.92) 70%, rgba(10,1,24,.92) 100%);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255,255,255,.06);
  padding: 16px 0;
  margin-bottom: 10px;
}
.mr-dh{
  max-width: 1400px; margin: 0 auto; padding: 0 24px;
  display:grid; 
  grid-template-columns: auto 1fr auto;
  align-items:center; 
  gap:16px; 
  position:relative;
  height: 100%;
}
.mr-brand{ 
  display:flex; 
  align-items:center; 
  gap:10px; 
  white-space:nowrap;
  flex-shrink: 0;
}
.mr-bolt{ font-size:20px }
.mr-word{
  font-size:22px; font-weight:900;
  background: linear-gradient(135deg,#ff2a88 0%, #7f3bf3 45%, #00e5ff 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
/* Botón de búsqueda */
.mr-search-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 999px;
  color: #eaf6ff;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  font-weight: 600;
  flex: 1;
  max-width: 200px;
  justify-content: center;
}

.mr-search-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(127, 59, 243, 0.5);
  box-shadow: 0 0 0 3px rgba(127, 59, 243, 0.1);
  transform: translateY(-1px);
}

.search-icon {
  font-size: 16px;
}

.search-text {
  font-weight: 600;
}
.mr-userpill{
  display:flex; align-items:center; gap:12px;
  height: 44px; padding: 6px 14px; cursor: pointer;
  border:1px solid rgba(255,255,255,.16); border-radius: 999px;
  background: rgba(255,255,255,.06); color:#fff;
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.04);
  flex-shrink: 0;
}
.mr-avatar{
  width:32px; height:32px; border-radius:50%;
  display:grid; place-items:center; font-size:16px;
  background: radial-gradient(60% 60% at 30% 30%, #ff4db2 0, #8a36e8 80%);
  box-shadow: 0 0 0 4px rgba(255,255,255,.06) inset;
}
.mr-usertext{ display:flex; flex-direction:column; line-height:1.05 }
.mr-usertext strong{ font-size:13px }
.mr-usertext small{ font-size:11px; color: rgba(255,255,255,.7) }
.mr-caret{ opacity:.75 }
.mr-menu{
  position:absolute; right:24px; top:52px; min-width:180px;
  background: rgba(20,20,35,.96); color:#e8f0ff;
  border:1px solid rgba(255,255,255,.12); border-radius:14px;
  box-shadow: 0 10px 30px rgba(0,0,0,.45); overflow:hidden; backdrop-filter: blur(10px);
}
.mr-menu a{ display:block; padding:10px 12px; text-decoration:none; color:inherit }
.mr-menu a:hover{ background: rgba(255,255,255,.06) }
.mr-menu hr{ border:0; border-top:1px solid rgba(255,255,255,.08); margin:4px 0 }
/* Media query para buscador removido - ahora usa BuscarMedicos que maneja su propio responsive */

/* ===================== */
/* == CONTENIDO (UI)  == */
/* ===================== */
.top-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.welcome-section{
  padding: 24px 28px;
  background: linear-gradient(135deg, rgba(127,59,243,0.15) 0%, rgba(255,42,136,0.1) 100%);
  border:1px solid rgba(255,255,255,.12);
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(127,59,243,.2);
  animation: fadeIn 0.5s ease-out;
}

.welcome-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.welcome-title-group {
  flex: 1;
  min-width: 280px;
}

.welcome-title-group h1{ 
  font-size:28px; 
  font-weight:900; 
  margin:0 0 10px 0;
  background: linear-gradient(135deg, #fff, #a78bfa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
}

.wave-emoji {
  display: inline-block;
  animation: wave 1s ease-in-out infinite;
}

@keyframes wave {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(20deg); }
  75% { transform: rotate(-20deg); }
}

.welcome-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.role-badge {
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(127,59,243,0.15);
  color: #a78bfa;
  border: 1px solid rgba(127,59,243,0.3);
}

.role-icon {
  font-size: 14px;
}

.verif-badge {
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  border: 1px solid;
}

.verif-badge.text-yellow {
  background: rgba(251,191,36,0.15);
  color: #fde68a;
  border-color: rgba(251,191,36,0.3);
}

.verif-badge.text-green {
  background: rgba(34,197,94,0.15);
  color: #86efac;
  border-color: rgba(34,197,94,0.3);
}

.verif-badge.text-red {
  background: rgba(239,68,68,0.15);
  color: #fca5a5;
  border-color: rgba(239,68,68,0.3);
}

.verif-icon {
  font-size: 14px;
}


.welcome-actions{ 
  display:flex; 
  gap:10px; 
  flex-wrap:wrap;
  flex-shrink: 0;
  align-items: flex-start;
}

.btn-primary-action{
  padding:12px 20px; 
  border-radius:12px; 
  border:0; 
  color:#fff; 
  cursor:pointer;
  background: linear-gradient(135deg,#ff2a88,#7f3bf3);
  box-shadow: 0 8px 24px rgba(127,59,243,.4);
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-primary-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(127,59,243,.5);
}

.btn-secondary-action{
  padding:12px 20px; 
  border-radius:12px; 
  color:#fff; 
  background:rgba(255,255,255,.09);
  border:1px solid rgba(255,255,255,.12);
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-secondary-action:hover:not(:disabled) {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.2);
}

.btn-secondary-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-icon {
  font-size: 16px;
}

.btn-spinner {
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

/* Date Selector */
.date-selector-section {
  padding: 16px 20px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
}

.date-selector-content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.date-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: rgba(234,246,255,0.9);
}

.label-icon {
  font-size: 18px;
}

.date-input {
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: #fff;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s;
}

.date-input:focus {
  outline: none;
  border-color: #7f3bf3;
  background: rgba(255,255,255,0.08);
}

.btn-today {
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.btn-today:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.2);
}

/* Quick stats */
.quick-stats{
  display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:24px;
}
.stat-card{
  background: rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius: 18px; padding: 20px;
  box-shadow: 0 10px 24px rgba(0,0,0,.28);
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
}
.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(127,59,243,0.3);
  box-shadow: 0 12px 32px rgba(127,59,243,.3);
}
.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.stat-icon{ 
  font-size:24px; 
  opacity: 0.9;
}
.stat-trend {
  font-size: 14px;
  opacity: 0.6;
}
.stat-value{ 
  font-weight:900; 
  font-size:28px;
  color: rgba(234,246,255,0.98);
  margin-bottom: 4px;
}
.stat-label{ 
  color: rgba(234,246,255,.7);
  font-size: 13px;
  font-weight: 500;
}
.stat-card.stat-total .stat-icon { color: #60a5fa; }
.stat-card.stat-confirmed .stat-icon { color: #86efac; }
.stat-card.stat-pending .stat-icon { color: #fde68a; }
.stat-card.stat-completed .stat-icon { color: #a5b4fc; }

/* Filters */
.filters-section {
  margin-bottom: 24px;
  padding: 4px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
}

.filter-tabs {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.filter-tab {
  flex: 1;
  min-width: 120px;
  padding: 12px 16px;
  border-radius: 12px;
  border: none;
  background: transparent;
  color: rgba(234,246,255,0.7);
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.filter-tab:hover {
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
}

.filter-tab.active {
  background: linear-gradient(135deg, rgba(127,59,243,0.2), rgba(255,42,136,0.15));
  color: rgba(234,246,255,0.98);
  border: 1px solid rgba(127,59,243,0.3);
}

.tab-icon {
  font-size: 16px;
}

.tab-count {
  padding: 2px 8px;
  border-radius: 8px;
  background: rgba(255,255,255,0.1);
  font-size: 12px;
  font-weight: 700;
}

.filter-tab.active .tab-count {
  background: rgba(255,255,255,0.2);
}

/* Agenda */
.appointments-section {
  margin-top: 8px;
}

.section-header {
  margin-bottom: 20px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  font-size: 20px;
  color: rgba(234,246,255,0.98);
}

.title-icon {
  font-size: 22px;
}

.title-count {
  font-size: 16px;
  font-weight: 600;
  color: rgba(234,246,255,0.6);
  margin-left: 4px;
}

.appointments-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.appointment-card{
  background: rgba(255,255,255,.05); 
  border:1px solid rgba(255,255,255,.1);
  border-radius: 16px; 
  padding: 20px; 
  transition: all 0.3s;
  animation: fadeIn 0.4s ease-out;
}

.appointment-card:hover {
  background: rgba(255,255,255,.08);
  border-color: rgba(127,59,243,0.3);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(127,59,243,.2);
}

.appointment-header{ 
  display:flex; 
  align-items:center; 
  justify-content:space-between; 
  gap:16px; 
  margin-bottom:16px;
}

.patient-info{ 
  display:flex; 
  align-items:center; 
  gap:14px;
  flex: 1;
}

.patient-avatar{ 
  width:48px; 
  height:48px; 
  border-radius:50%; 
  display:grid; 
  place-items:center; 
  background: rgba(255,255,255,.08);
  border: 2px solid rgba(255,255,255,.1);
  flex-shrink: 0;
}

.patient-avatar.status-pending {
  border-color: rgba(251,191,36,0.4);
}

.patient-avatar.status-confirmed {
  border-color: rgba(34,197,94,0.4);
}

.avatar-icon {
  font-size: 24px;
}

.patient-details {
  flex: 1;
  min-width: 0;
}

.patient-name{ 
  margin:0 0 6px 0; 
  font-weight:700;
  font-size: 18px;
  color: rgba(234,246,255,0.98);
}

.appointment-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: rgba(234,246,255,0.6);
  flex-wrap: wrap;
}

.appointment-id {
  font-weight: 600;
}

.meta-separator {
  opacity: 0.4;
}

.appointment-time-meta {
  font-weight: 500;
}

.appointment-status{
  padding:8px 14px; 
  border-radius:12px; 
  font-size:13px; 
  font-weight:600;
  border:1px solid;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-pending{  
  background:rgba(251,191,36,.15); 
  color:#fde68a; 
  border-color:rgba(251,191,36,.35);
}
.status-pending .status-dot {
  background: #fde68a;
  box-shadow: 0 0 8px rgba(251,191,36,.5);
}

.status-confirmed{ 
  background:rgba(34,197,94,.15);  
  color:#86efac; 
  border-color:rgba(34,197,94,.42);
}
.status-confirmed .status-dot {
  background: #86efac;
  box-shadow: 0 0 8px rgba(34,197,94,.5);
}

.status-done{      
  background:rgba(16,185,129,.15); 
  color:#bbf7d0; 
  border-color:rgba(16,185,129,.42);
}
.status-done .status-dot {
  background: #bbf7d0;
  box-shadow: 0 0 8px rgba(16,185,129,.5);
}

.status-cancel{    
  background:rgba(239,68,68,.15);  
  color:#fca5a5; 
  border-color:rgba(239,68,68,.42);
}
.status-cancel .status-dot {
  background: #fca5a5;
  box-shadow: 0 0 8px rgba(239,68,68,.5);
}

.appointment-body {
  margin-bottom: 16px;
}

.appointment-detail-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.detail-item{ 
  display:flex; 
  align-items:center; 
  gap:10px; 
  padding:12px 16px; 
  background:rgba(255,255,255,.04); 
  border:1px solid rgba(255,255,255,.08); 
  border-radius:12px;
  flex: 1;
  min-width: 150px;
}

.detail-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.detail-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.detail-label {
  font-size: 11px;
  color: rgba(234,246,255,0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.detail-value {
  font-size: 14px;
  color: rgba(234,246,255,0.9);
  font-weight: 600;
}

.appointment-actions{ 
  display:flex; 
  gap:10px; 
  flex-wrap:wrap;
  padding-top: 16px;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.btn-action{
  padding:10px 16px; 
  border-radius:10px; 
  border:1px solid; 
  background:rgba(255,255,255,.06); 
  color:#fff; 
  cursor:pointer;
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-action:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-confirm {
  background: rgba(34,197,94,.15);
  border-color: rgba(34,197,94,.35);
  color: #86efac;
}

.btn-confirm:hover:not(:disabled) {
  background: rgba(34,197,94,.25);
  border-color: rgba(34,197,94,.5);
}

.btn-complete {
  background: linear-gradient(135deg, rgba(16,185,129,.2), rgba(34,197,94,.15));
  border-color: rgba(16,185,129,.35);
  color: #bbf7d0;
}

.btn-complete:hover:not(:disabled) {
  background: linear-gradient(135deg, rgba(16,185,129,.3), rgba(34,197,94,.25));
  border-color: rgba(16,185,129,.5);
}

.btn-cancel{ 
  background: rgba(239,68,68,.15); 
  border-color: rgba(239,68,68,.35); 
  color:#fca5a5;
}

.btn-cancel:hover:not(:disabled) {
  background: rgba(239,68,68,.25);
  border-color: rgba(239,68,68,.5);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255,255,255,0.02);
  border: 2px dashed rgba(255,255,255,0.1);
  border-radius: 20px;
}

.empty-illustration {
  position: relative;
  display: inline-block;
  margin-bottom: 24px;
}

.empty-icon {
  font-size: 80px;
  opacity: 0.5;
  display: block;
}

.empty-pulse {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 2px solid rgba(127,59,243,0.3);
  animation: pulse-ring 2s ease-out infinite;
}

@keyframes pulse-ring {
  0% {
    transform: translate(-50%, -50%) scale(0.8);
    opacity: 1;
  }
  100% {
    transform: translate(-50%, -50%) scale(1.4);
    opacity: 0;
  }
}

.empty-state h3 {
  font-size: 24px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 12px;
}

.empty-state p {
  font-size: 15px;
  color: rgba(234,246,255,0.7);
  margin-bottom: 8px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

.empty-state p strong {
  color: rgba(234,246,255,0.9);
  font-weight: 600;
}

.empty-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 24px;
  flex-wrap: wrap;
}

.btn-empty-primary {
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

.btn-empty-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127,59,243,0.4);
}

.btn-empty-secondary {
  padding: 12px 24px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-empty-secondary:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.2);
}

.loading-state {
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

.loading-state p {
  color: rgba(234,246,255,0.7);
  font-size: 15px;
}

@media (max-width: 900px){
  .quick-stats{ grid-template-columns:repeat(2,minmax(0,1fr)) }
  
  .welcome-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .welcome-title-group {
    min-width: 100%;
    margin-bottom: 16px;
  }
  
  .welcome-actions {
    width: 100%;
  }
  
  .welcome-actions button {
    flex: 1;
    min-width: 140px;
  }
  
  .date-selector-content {
    flex-direction: column;
    align-items: stretch;
  }
  
  .date-label {
    width: 100%;
  }
  
  .date-input {
    width: 100%;
  }
  
  .btn-today {
    width: 100%;
  }
  
  .filter-tabs {
    flex-direction: column;
  }
  
  .filter-tab {
    width: 100%;
  }
  
  .quick-stats {
    grid-template-columns: 1fr;
  }
}
</style>
