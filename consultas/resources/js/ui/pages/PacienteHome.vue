<template>
  <!-- HEADER SOLO PARA PACIENTE (sin Tailwind) -->
  <!-- HEADER DEL PANEL (estilo landing, sin Tailwind) -->
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
        <input placeholder="Buscar médicos, especialidades..." />
      </div>

      <!-- Usuario -->
      <button type="button" class="mr-userpill" @click.stop="menuOpen = !menuOpen">
        <span class="mr-avatar">👤</span>
        <span class="mr-usertext">
          <strong>{{ userName }}</strong>
          <small>Paciente</small>
        </span>
        <span class="mr-caret">▾</span>
      </button>

      <!-- Menú -->
      <div v-if="menuOpen" class="mr-menu">
        <a @click.prevent="$router.push('/me')">Mi panel</a>
        <a @click.prevent="$router.push('/me/perfil')">Perfil</a>
        <hr />
        <a @click.prevent="logout">Salir</a>
      </div>
    </div>
  </header>
  <section class="page-slot">
    <transition-group name="toast" tag="div" class="toast-container" aria-live="polite" aria-atomic="true">
      <div v-for="toast in toasts" :key="toast.id" class="toast" :class="`toast--${toast.type}`">
        {{ toast.message }}
      </div>
    </transition-group>
    <div class="container">

      <!-- WELCOME -->
      <div class="welcome-section">
        <div class="welcome-content">
          <h1>¡Hola, {{ userName }}! 👋</h1>
          <p>
            <span v-if="resumenLoading">Estamos preparando tu panel con las últimas métricas.</span>
            <span v-else>Tienes {{ stats.proximas }} citas próximas. Mantén tu salud al día.</span>
          </p>
        </div>
        <button class="btn-new-appointment" @click="$router.push('/me/reservar')">+ Nueva Cita</button>
      </div>

      <!-- QUICK STATS -->
      <div class="quick-stats">
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-value">
            <span v-if="resumenLoading || citasLoading" class="skeleton skeleton-number"></span>
            <span v-else>{{ stats.proximas }}</span>
          </div>
          <div class="stat-label">Citas Próximas</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-value">
            <span v-if="resumenLoading" class="skeleton skeleton-number"></span>
            <span v-else>{{ stats.completadas }}</span>
          </div>
          <div class="stat-label">Citas Completadas</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">👨‍⚕️</div>
          <div class="stat-value">
            <span v-if="resumenLoading" class="skeleton skeleton-number"></span>
            <span v-else>{{ stats.favoritos }}</span>
          </div>
          <div class="stat-label">Médicos Favoritos</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📄</div>
          <div class="stat-value">
            <span v-if="resumenLoading" class="skeleton skeleton-number"></span>
            <span v-else>{{ stats.historial }}</span>
          </div>
          <div class="stat-label">Historial Médico</div>
        </div>
      </div>

      <!-- MAIN -->
      <div class="main-content">
        <!-- APPOINTMENTS -->
        <div class="appointments-section">
          <div class="section-title">📅 Mis Próximas Citas</div>

          <div v-if="citasLoading" class="appointment-card skeleton-card">
            <div class="appointment-header">
              <div class="doctor-info">
                <div class="doctor-avatar skeleton skeleton-avatar"></div>
                <div class="doctor-details">
                  <div class="skeleton skeleton-line"></div>
                  <div class="skeleton skeleton-line small"></div>
                </div>
              </div>
              <div class="appointment-status skeleton skeleton-badge"></div>
            </div>
            <div class="appointment-details">
              <div class="detail-item"><span class="skeleton skeleton-line full"></span></div>
              <div class="detail-item"><span class="skeleton skeleton-line full"></span></div>
              <div class="detail-item"><span class="skeleton skeleton-line full"></span></div>
            </div>
            <div class="appointment-actions">
              <span class="skeleton skeleton-button"></span>
              <span class="skeleton skeleton-button"></span>
              <span class="skeleton skeleton-button"></span>
            </div>
          </div>

          <div v-else-if="!citas.length" class="appointment-card">
            <div class="doctor-details"><h3>Sin citas próximas</h3></div>
            <div class="appointment-details"><div class="detail-item">Programa tu próxima cita con “+ Nueva Cita”.</div></div>
          </div>

          <div v-else v-for="c in citas" :key="c.id" class="appointment-card">
            <div class="appointment-header">
              <div class="doctor-info">
                <div class="doctor-avatar">👨‍⚕️</div>
                <div class="doctor-details">
                  <h3>Dr(a). {{ c.medico }}</h3>
                  <div class="doctor-specialty">{{ c.especialidad }}</div>
                </div>
              </div>
              <div class="appointment-status" :class="statusClass(c.estado)">{{ c.estado }}</div>
            </div>

            <div class="appointment-details">
              <div class="detail-item"><span>📅</span><span>{{ c.fecha }}</span></div>
              <div class="detail-item"><span>🕐</span><span>{{ c.hora }}</span></div>
              <div class="detail-item" v-if="c.lugar"><span>📍</span><span>{{ c.lugar }}</span></div>
              <div class="detail-item" v-else><span>💻</span><span>Telemedicina</span></div>
            </div>

            <div class="appointment-actions">
              <button
                class="btn-action btn-cancel"
                :disabled="isCancelling(c.id)"
                @click="cancelCita(c)"
              >
                <span v-if="isCancelling(c.id)" class="btn-spinner"></span>
                <span v-else>Cancelar</span>
              </button>
              <button
                class="btn-action btn-reschedule"
                :disabled="isRescheduling(c.id)"
                @click="openReprogramModal(c)"
              >
                <span v-if="isRescheduling(c.id)" class="btn-spinner"></span>
                <span v-else>Reprogramar</span>
              </button>
              <button class="btn-action btn-video">🎥 Videollamada</button>
            </div>
          </div>
        </div>

        <!-- SIDEBAR -->
        <div class="sidebar">
          <div class="card">
            <div class="section-title">⭐ Recomendados para ti</div>
            <div class="recommended-doctors">
              <template v-if="resumenLoading">
                <div class="doctor-card skeleton-card" v-for="i in 2" :key="`rec-skeleton-${i}`">
                  <div class="doctor-card-header">
                    <div class="doctor-card-avatar skeleton skeleton-avatar"></div>
                    <div class="doctor-card-info">
                      <div class="skeleton skeleton-line"></div>
                      <div class="skeleton skeleton-line small"></div>
                      <div class="skeleton skeleton-line smaller"></div>
                    </div>
                  </div>
                  <div class="btn-book skeleton skeleton-button"></div>
                </div>
              </template>
              <p v-else-if="resumenError" class="empty-state">No pudimos cargar recomendaciones. Inténtalo más tarde.</p>
              <p v-else-if="!recomendados.length" class="empty-state">
                No tenemos recomendaciones todavía. Completa una cita para personalizar este espacio.
              </p>
              <div v-else class="doctor-card" v-for="d in recomendados" :key="d.id">
             
                <div class="doctor-card-header">
                  <div class="doctor-card-avatar">👨‍⚕️</div>
                  <div class="doctor-card-info">
                    <h4>{{ d.nombre }}</h4>
                    <div class="doctor-card-specialty">{{ d.especialidad }}</div>
                    <div class="rating" v-if="d.reviews > 0">⭐ {{ formatRating(d.rating) }} ({{ d.reviews }} reseñas)</div>
                    <div class="rating" v-else>Sin reseñas registradas</div>
                  </div>
                </div>
                <button class="btn-book">Agendar Cita</button>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="section-title">💡 Tip de Salud</div>
            <div v-if="resumenLoading" class="health-tip skeleton-card">
              <div class="tip-icon skeleton skeleton-avatar"></div>
              <div class="tip-body">
                <div class="skeleton skeleton-line"></div>
                <div class="skeleton skeleton-line full"></div>
                <div class="skeleton skeleton-line small"></div>
              </div>
            </div>
            <div v-else-if="resumenError" class="health-tip empty-tip">
              <div class="tip-icon">💡</div>
              <p>No pudimos cargar tus recomendaciones de salud. Intenta refrescar más tarde.</p>
            </div>
            <div v-else-if="hasTips" class="health-tip">
              <div class="tip-icon">💡</div>
              <div class="tip-body">
                <h4>{{ primaryTip }}</h4>
                <ul v-if="extraTips.length">
                  <li v-for="(tip, index) in extraTips" :key="`tip-${index}`">{{ tip }}</li>
                </ul>
              </div>
            </div>
            <div v-else class="health-tip empty-tip">
              <div class="tip-icon">💡</div>
              <p>Estamos recopilando información para ofrecerte recomendaciones personalizadas.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ReprogramarModal
      :visible="reprogramModal.visible"
      :medico-id="reprogramModal.cita?.medico_id ?? null"
      :current-start="reprogramModal.cita?.starts_at ?? null"
      :loading="reprogramModal.cita ? isRescheduling(reprogramModal.cita.id) : false"
      @close="closeReprogramModal"
      @select="handleReprogramSelect"
    />
  </section>
</template>

<script setup>

import { ref, computed, reactive, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import ReprogramarModal from '@/ui/components/ReprogramarModal.vue'

const userName = ref('')
const menuOpen = ref(false)
const router = useRouter()

const baseStats = Object.freeze({ proximas: 0, completadas: 0, favoritos: 0, historial: 0 })
const resumen = reactive({
  stats: { ...baseStats },
  recomendados: [],
  tips: []
})

const stats = computed(() => resumen.stats)
const recomendados = computed(() => resumen.recomendados)
const tipsList = computed(() => resumen.tips)
const citas = ref([])

const actionState = reactive({})
const toasts = ref([])
const reprogramModal = reactive({ visible: false, cita: null })

const resumenLoading = ref(true)
const citasLoading = ref(true)
const resumenError = ref(false)

const primaryTip = computed(() => tipsList.value[0] ?? null)
const extraTips = computed(() => tipsList.value.slice(1))
const hasTips = computed(() => tipsList.value.length > 0)

function handleOutside(e) {
  if (!e.target.closest('.mr-dh')) menuOpen.value = false
}
const ensureActionState = (id) => {
  if (!actionState[id]) actionState[id] = { cancel: false, reschedule: false }
  return actionState[id]
}

const isCancelling = (id) => ensureActionState(id).cancel
const isRescheduling = (id) => ensureActionState(id).reschedule

const toastDuration = 3200

function showToast(message, type = 'success') {
  const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`
  toasts.value.push({ id, message, type })
  setTimeout(() => removeToast(id), toastDuration)
}

function removeToast(id) {
  toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

function formatIsoDate(iso) {
  try {
    const date = new Date(iso)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  } catch (error) {
    return ''
  }
}

function formatIsoTime(iso) {
  try {
    const date = new Date(iso)
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    return `${hours}:${minutes}`
  } catch (error) {
    return ''
  }
}

function normalizeCita(row) {
  const start = row.starts_at ?? (row.fecha && row.hora ? `${row.fecha}T${row.hora}` : null)
  const normalized = { ...row, starts_at: start, ends_at: row.ends_at ?? null }
  if (start) {
    normalized.fecha = row.fecha ?? formatIsoDate(start)
    normalized.hora = row.hora ?? formatIsoTime(start)
  }
  return normalized
}

function sortCitasList() {
  citas.value = [...citas.value].sort((a, b) => {
    const aTime = a?.starts_at ? new Date(a.starts_at).getTime() : 0
    const bTime = b?.starts_at ? new Date(b.starts_at).getTime() : 0
    return aTime - bTime
  })
}

function updateProximasCount() {
  const current = resumen.stats ?? baseStats
  resumen.stats = { ...current, proximas: citas.value.length }
}


/* ---------- Cliente Axios central con Bearer automático ---------- */
onMounted(() => {
  document.addEventListener('click', handleOutside)
  loadPerfil()
  loadResumen()
  loadCitas()
})
/* ----------------------------------------------------------------- */

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutside)
})

async function loadPerfil() {
  try {
    const me = await api.get('/auth/me')
    if (me?.data?.user?.name) userName.value = me.data.user.name
  } catch (error) {
    // Ignora cuando no hay sesión
  }
}

async function loadResumen() {
  resumenLoading.value = true
  resumenError.value = false
  try {
    const { data } = await api.get('/paciente/resumen')
    const defaults = { ...baseStats }
    resumen.stats = { ...defaults, ...(data?.stats ?? {}) }
    resumen.recomendados = Array.isArray(data?.recomendados) ? data.recomendados : []
    resumen.tips = Array.isArray(data?.tips) ? data.tips : []
  } catch (error) {
    if (error?.response?.status === 401) {
      resumen.stats = { ...baseStats }
      resumen.recomendados = []
      resumen.tips = []
    } else {
      resumenError.value = true
    }
  } finally {
    resumenLoading.value = false
  }
}

async function loadCitas() {
  citasLoading.value = true
  try {
    const { data } = await api.get('/paciente/citas/proximas')
    const rows = Array.isArray(data) ? data : (data?.citas ?? [])
    citas.value = rows.map(normalizeCita)
    sortCitasList()
    updateProximasCount()
  } catch (error) {
    if (error?.response?.status === 401) {
      citas.value = []
    } else {
      citas.value = []
      // si prefieres, redirige al login:
      // router.push('/login')
    }
    updateProximasCount()
  } finally {
  citasLoading.value = false
  }
}

async function cancelCita(cita) {
  if (!cita?.id) return
  const state = ensureActionState(cita.id)
  if (state.cancel) return

  state.cancel = true
  try {
    await api.post(`/paciente/citas/${cita.id}/cancelar`)
    citas.value = citas.value.filter((row) => row.id !== cita.id)
    updateProximasCount()
    showToast('Cita cancelada correctamente.')
  } catch (error) {
    const message = error?.response?.data?.message ?? 'No se pudo cancelar la cita.'
    showToast(message, 'error')
  } finally {
    state.cancel = false
  }
}

function openReprogramModal(cita) {
  if (!cita) return
  reprogramModal.visible = true
  reprogramModal.cita = cita
}

function closeReprogramModal() {
  reprogramModal.visible = false
  reprogramModal.cita = null
}

async function handleReprogramSelect(slot) {
  const cita = reprogramModal.cita
  if (!cita?.id || !slot?.start) return

  const state = ensureActionState(cita.id)
  if (state.reschedule) return

  state.reschedule = true
  try {
    const payload = {
      starts_at: slot.start,
      medico_id: cita.medico_id,
      especialidad_id: cita.especialidad_id,
    }
    const { data } = await api.put(`/paciente/citas/${cita.id}`, payload)
    const newStart = data?.starts_at ?? slot.start
    const newEnd = data?.ends_at ?? slot.end
    cita.starts_at = newStart
    cita.ends_at = newEnd
    cita.fecha = formatIsoDate(newStart)
    cita.hora = formatIsoTime(newStart)
    cita.estado = data?.estado ?? 'pendiente'
    sortCitasList()
    updateProximasCount()
    closeReprogramModal()
    showToast('Cita reprogramada correctamente.')
  } catch (error) {
    const message = error?.response?.data?.message ?? 'No se pudo reprogramar la cita.'
    showToast(message, 'error')
  } finally {
    state.reschedule = false
  }
}

async function logout() {
  try {
    await api.post('/auth/logout')
  } catch (error) {
    // Ignora errores de red
  }
  localStorage.removeItem('token')
  localStorage.removeItem('auth_token')
  localStorage.removeItem('access_token')
  sessionStorage.removeItem('token')
  router.push('/')
}

const formatRating = (value) => {
  const num = typeof value === 'number' ? value : parseFloat(value)
  if (Number.isFinite(num)) return num.toFixed(1)
  return '4.5'
}

const statusClass = (s) => {
  const v = (s || '').toLowerCase()
  if (v.includes('confirm') || v.includes('complet')) return 'status-confirmed'
  if (v.includes('pend')) return 'status-pending'
  if (v.includes('cancel')) return 'status-cancelled'
  return 'status-pending'
}
</script>


<style scoped>
.toast-container{
  position:fixed;
  top:20px;right:20px;
  display:grid;gap:12px;
  z-index:1200;
}
.toast{
  min-width:220px;
  padding:12px 18px;
  border-radius:12px;
  background:rgba(37,206,209,.95);
  color:#0a0118;
  font-weight:600;
  box-shadow:0 18px 40px rgba(10,1,24,.28);
}
.toast--error{
  background:rgba(192,57,43,.95);
  color:#fff;
}
.toast-enter-active,.toast-leave-active{
  transition:all .25s ease;
}
.toast-enter-from,.toast-leave-to{
  opacity:0;
  transform:translateY(-10px);
}
.btn-action[disabled]{
  opacity:.6;
  cursor:not-allowed;
}
.btn-spinner{
  width:16px;height:16px;
  border-radius:50%;
  border:2px solid rgba(255,255,255,.4);
  border-top-color:#fff;
  display:inline-block;
  animation:btn-spin .75s linear infinite;
}
@keyframes btn-spin{
  from{transform:rotate(0deg);}
  to{transform:rotate(360deg);}
}
/* ===== Header del panel de Paciente (scoped) ===== */
.dash-header{position:sticky;top:0;z-index:20;margin:12px 0 24px}
.dh-inner{
  display:flex;align-items:center;justify-content:space-between;gap:16px;
  padding:12px 16px;border-radius:16px;
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);
  backdrop-filter:blur(16px); box-shadow:0 10px 30px rgba(0,0,0,.35);
}
.brand{
  font-weight:800;font-size:18px;
  background:linear-gradient(135deg,#ff006e,#8338ec,#00f5ff);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  display:flex;align-items:center;gap:8px;white-space:nowrap;
}
.search{
  flex:1; min-width:240px; max-width:460px;
  display:flex;align-items:center;gap:8px;
  border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.08);
  border-radius:999px; padding:8px 12px;
}
.search input{background:transparent;border:0;outline:0;color:#fff;width:100%;font-size:14px}
.search input::placeholder{color:rgba(255,255,255,.55)}
.user{display:flex;align-items:center;gap:10px}
.user .info{display:none}
@media (min-width:520px){ .user .info{display:block;text-align:right} }
.user .name{font-weight:700;font-size:13px;line-height:1}
.user .role{font-size:12px;color:rgba(255,255,255,.6);line-height:1}
.avatar{
  width:36px;height:36px;border-radius:50%;display:grid;place-items:center;
  background:linear-gradient(135deg,#ff006e,#8338ec);
}
.logout{background:transparent;border:0;color:#fff;opacity:.8;cursor:pointer}
.logout:hover{opacity:1;text-decoration:underline}

/* ====== Header estilo landing (scoped) ====== */
.mr-dashbar{
  position:sticky; top:0; z-index:30;
  background: linear-gradient(180deg,  0%, rgba(10,1,24,.90) 60%, rgba(10, 1, 24, 90) 100%);
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

/* Marca */
.mr-brand{ display:flex; align-items:center; gap:10px; white-space:nowrap }
.mr-bolt{ font-size:20px }
.mr-word{
  font-size:22px; font-weight:900;
  background: linear-gradient(135deg,#ff2a88 0%, #7f3bf3 45%, #00e5ff 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}

/* Buscador */
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

/* Pill de usuario */
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

/* Menú desplegable */
.mr-menu{
  position:absolute; right:24px; top:52px; min-width:180px;
  background: rgba(20,20,35,.96); color:#e8f0ff;
  border:1px solid rgba(255,255,255,.12); border-radius:14px;
  box-shadow: 0 10px 30px rgba(0,0,0,.45); overflow:hidden; backdrop-filter: blur(10px);
}
.mr-menu a{
  display:block; padding:10px 12px; text-decoration:none; color:inherit;
}
.mr-menu a:hover{ background: rgba(255,255,255,.06) }
.mr-menu hr{ border:0; border-top:1px solid rgba(255,255,255,.08); margin:4px 0 }

/* Responsive */
@media (max-width: 980px){
  .mr-search{ display:none }
}
.status-cancelled{
  background: rgba(255,107,107,.15);
  color: #ff9b9b;
}

.skeleton{
  position: relative;
  overflow: hidden;
  background: rgba(255,255,255,.08);
  display: inline-block;
}
.skeleton::after{
  content:'';
  position:absolute;
  inset:0;
  transform: translateX(-100%);
  background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.35), rgba(255,255,255,0));
  animation: shimmer 1.4s infinite;
}

.skeleton-number{ width:48px; height:22px; border-radius:12px; }
.skeleton-line{ width:80%; height:12px; border-radius:8px; margin:4px 0; display:block; }
.skeleton-line.full{ width:100%; }
.skeleton-line.small{ width:60%; }
.skeleton-line.smaller{ width:40%; }
.skeleton-avatar{ width:48px; height:48px; border-radius:50%; }
.skeleton-button{ width:100%; height:34px; border-radius:999px; display:block; margin-top:12px; }
.skeleton-badge{ width:90px; height:24px; border-radius:999px; display:inline-block; }
.skeleton-card{ border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.04); }

@keyframes shimmer{
  100%{ transform: translateX(100%); }
}

.empty-state{
  font-size:14px;
  color:rgba(255,255,255,.65);
  padding:12px;
  margin:0;
}

.health-tip.empty-tip{
  background: rgba(255,255,255,.04);
}

.health-tip .tip-body h4{ margin-bottom:6px; }
.health-tip .tip-body ul{
  margin:8px 0 0;
  padding-left:18px;
  display:grid;
  gap:6px;
  font-size:13px;
  color:rgba(255,255,255,.75);
}
.health-tip.empty-tip p{ margin:0; }

</style>