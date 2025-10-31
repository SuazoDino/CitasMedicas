<template>
  <!-- HEADER DEL PANEL (idéntico al de Paciente) -->
  <header class="mr-dashbar">
    <div class="mr-dh">
      <!-- Marca -->
      <div class="mr-brand">
        <span class="mr-bolt">⚡</span>
        <span class="mr-word">MediReserva</span>
      </div>

      <!-- Buscador -->
      <div class="mr-search">
        <span class="mr-lens"></span>
        <input placeholder="Buscar pacientes, citas..." />
      </div>

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
        <hr />
        <a @click.prevent="logout">Salir</a>
      </div>
    </div>
  </header>

  <!-- CONTENIDO -->
  <section class="page-slot">
    <div class="container">
      <!-- WELCOME -->
      <div class="welcome-section">
        <div class="welcome-content">
          <h1>¡Hola, {{ doctorName }}! 👋</h1>
          <p>
            Tienes {{ totalHoy }} citas para hoy · {{ pendientesHoy }} pendientes ·
            Verificación: <b :class="badgeClass">{{ verifStatus }}</b>
          </p>
        </div>
        <div class="welcome-actions">
          <button class="btn-new-appointment" @click="goHorarios">⚙️ Configurar Horarios</button>
          <button class="btn-soft" @click="cargarAgenda" :disabled="loading">
            {{ loading ? 'Actualizando…' : '⟳ Refrescar agenda' }}
          </button>
        </div>
      </div>

      <!-- QUICK STATS (mismo layout que Paciente) -->
      <div class="quick-stats">
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-value">{{ totalHoy }}</div>
          <div class="stat-label">Citas Hoy</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-value">{{ confirmadasHoy }}</div>
          <div class="stat-label">Confirmadas</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⏳</div>
          <div class="stat-value">{{ pendientesHoy }}</div>
          <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🧾</div>
          <div class="stat-value">{{ completadasHoy }}</div>
          <div class="stat-label">Completadas</div>
        </div>
      </div>

      <!-- AGENDA (usa las mismas tarjetas “appointment-card”) -->
      <div class="appointments-section">
        <div class="section-title">📒 Agenda del Día</div>

        <div v-if="!agendaFiltrada.length" class="appointment-card">
          <div class="doctor-details"><h3>Sin citas para mostrar</h3></div>
          <div class="appointment-details">
            <div class="detail-item">Cuando tengas citas, aparecerán aquí.</div>
          </div>
        </div>

        <div
          v-for="c in agendaFiltrada"
          :key="c.id"
          class="appointment-card"
        >
          <div class="appointment-header">
            <div class="doctor-info">
              <div class="doctor-avatar">👤</div>
              <div class="doctor-details">
                <h3>{{ c.paciente }}</h3>
                <div class="doctor-specialty">ID Cita #{{ c.id }}</div>
              </div>
            </div>
            <div class="appointment-status" :class="statusPill(c.estado)">{{ c.estado }}</div>
          </div>

          <div class="appointment-details">
            <div class="detail-item"><span>🕒</span><span>{{ c.hora }}</span></div>
            <div class="detail-item"><span>📍</span><span>{{ c.lugar || 'Presencial/Telemedicina' }}</span></div>
          </div>

          <div class="appointment-actions">
            <button
              v-if="c.estado==='pendiente'"
              class="btn-action btn-primary"
              @click="accion(c.id,'confirmar')"
              :disabled="busyId===c.id"
            >Confirmar</button>

            <button
              v-if="c.estado==='pendiente'"
              class="btn-action btn-cancel"
              @click="accion(c.id,'cancelar')"
              :disabled="busyId===c.id"
            >Cancelar</button>

            <button
              v-if="c.estado==='confirmada'"
              class="btn-action btn-primary"
              @click="accion(c.id,'completar')"
              :disabled="busyId===c.id"
            >Completar</button>

            <button
              v-if="c.estado==='confirmada'"
              class="btn-action btn-cancel"
              @click="accion(c.id,'cancelar')"
              :disabled="busyId===c.id"
            >Cancelar</button>
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
    const today = new Date().toISOString().slice(0,10)
    const { data } = await axios.get('/api/medico/citas', { params: { date: today } })
    agenda.value = (data ?? []).map(r => ({
      id: r.id,
      hora: new Date(r.starts_at).toTimeString().slice(0,5),
      paciente: r.paciente,
      estado: r.estado || 'pendiente',
      lugar: r.lugar || null,
    }))
  } finally { loading.value = false }
}

async function accion(id, tipo){
  const path = { confirmar:'confirmar', completar:'completar', cancelar:'cancelar' }[tipo]
  if (!path) return
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

/* ==================== */
/* == HEADER (mr-*)  == */
/* ==================== */
.mr-dashbar{
  position:sticky; top:0; z-index:30;
  background: linear-gradient(180deg, rgba(10,1,24,.92) 0%, rgba(10,1,24,.92) 70%, rgba(10,1,24,.92) 100%);
  backdrop-filter: blur(8px);
  height: 100px;
  border-bottom: 1px solid rgba(255,255,255,.06);
  padding: 10px 0 14px;
  margin-bottom: 10px;
}
.mr-dh{
  max-width: 1400px; margin: 0 auto; padding: 0 24px;
  display:flex; align-items:center; gap:16px; position:relative;
  margin-top: 20px;
}
.mr-brand{ display:flex; align-items:center; gap:10px; white-space:nowrap }
.mr-bolt{ font-size:20px }
.mr-word{
  font-size:22px; font-weight:900;
  background: linear-gradient(135deg,#ff2a88 0%, #7f3bf3 45%, #00e5ff 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.mr-search{
  flex:1; display:flex; align-items:center; gap:10px;
  border:1px solid rgba(255,255,255,.16);
  background: rgba(255,255,255,.06);
  border-radius: 999px; padding: 10px 14px; height: 44px;
}
.mr-lens{
  width:14px; height:14px; border-radius:50%;
  background: radial-gradient(circle at 40% 40%, #7fd6ff 0 40%, #5fb3ff 41% 100%);
  box-shadow: 0 0 6px rgba(127,214,255,.8);
  display:inline-block; position:relative;
}
.mr-lens::after{
  content:''; position:absolute; width:8px; height:2px; background:#7fd6ff;
  right:-8px; bottom:-1px; transform: rotate(35deg); border-radius:2px; opacity:.8;
}
.mr-search input{
  background: transparent; border:0; outline:0; color:#eaf6ff; width:100%; font-size:14px;
}
.mr-search input::placeholder{ color: rgba(234,246,255,.55) }
.mr-userpill{
  display:flex; align-items:center; gap:12px;
  height: 44px; padding: 6px 14px; cursor: pointer;
  border:1px solid rgba(255,255,255,.16); border-radius: 999px;
  background: rgba(255,255,255,.06); color:#fff;
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.04);
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
@media (max-width:980px){ .mr-search{ display:none } }

/* ===================== */
/* == CONTENIDO (UI)  == */
/* ===================== */
.welcome-section{
  display:flex; align-items:center; justify-content:space-between; gap:16px;
  padding: 20px 22px; margin-bottom: 18px;
  background: rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius: 20px;
  box-shadow: 0 10px 28px rgba(0,0,0,.32);
}
.welcome-content h1{ font-size:28px; font-weight:900; margin:0 0 6px }
.welcome-content p{ color: rgba(255,255,255,.7); margin:0 }
.welcome-actions{ display:flex; gap:10px; flex-wrap:wrap }
.btn-new-appointment{
  padding:10px 16px; border-radius:999px; border:0; color:#fff; cursor:pointer;
  background: linear-gradient(135deg,#ff2a88,#7f3bf3);
  box-shadow: 0 12px 26px rgba(127,59,243,.35);
}
.btn-soft{
  padding:10px 16px; border-radius:999px; color:#fff; background:rgba(255,255,255,.09);
  border:1px solid rgba(255,255,255,.12)
}

/* Quick stats */
.quick-stats{
  display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:22px;
}
.stat-card{
  background: rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius: 18px; padding: 16px 18px;
  box-shadow: 0 10px 24px rgba(0,0,0,.28);
}
.stat-icon{ font-size:20px; margin-bottom:6px }
.stat-value{ font-weight:900; font-size:26px }
.stat-label{ color: rgba(255,255,255,.7) }

/* Agenda (reutiliza “appointment-card” del paciente) */
.appointments-section .section-title{ font-weight:800; margin: 10px 0 12px }
.appointment-card{
  background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
  border-radius: 16px; padding: 14px 16px; margin-bottom: 12px;
}
.appointment-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px }
.doctor-info{ display:flex; align-items:center; gap:12px }
.doctor-avatar{ width:36px; height:36px; border-radius:50%; display:grid; place-items:center; background: rgba(255,255,255,.08) }
.doctor-details h3{ margin:0; font-weight:800 }
.doctor-specialty{ font-size:12px; color: rgba(255,255,255,.7) }

.appointment-status{
  padding:6px 10px; border-radius:999px; font-size:12px; border:1px solid rgba(255,255,255,.12);
}
.status-pending{  background:rgba(251,191,36,.14); color:#fde68a; border-color:rgba(251,191,36,.35) }
.status-confirmed{ background:rgba(34,197,94,.15);  color:#86efac; border-color:rgba(34,197,94,.42) }
.status-done{      background:rgba(16,185,129,.15); color:#bbf7d0; border-color:rgba(16,185,129,.42) }
.status-cancel{    background:rgba(239,68,68,.15);  color:#fca5a5; border-color:rgba(239,68,68,.42) }

.appointment-details{ display:flex; gap:14px; flex-wrap:wrap; margin-bottom:10px; color:rgba(255,255,255,.9) }
.detail-item{ display:flex; align-items:center; gap:6px; padding:6px 10px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:10px }

.appointment-actions{ display:flex; gap:8px; flex-wrap:wrap }
.btn-action{
  padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06); color:#fff; cursor:pointer;
}
.btn-primary{ background: linear-gradient(135deg,#ff2a88,#7f3bf3); border-color: transparent; box-shadow:0 12px 26px rgba(127,59,243,.35) }
.btn-cancel{ background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.35); color:#fecaca }

@media (max-width: 900px){
  .quick-stats{ grid-template-columns:repeat(2,minmax(0,1fr)) }
  .welcome-section{ flex-direction:column; align-items:flex-start }
}
</style>
