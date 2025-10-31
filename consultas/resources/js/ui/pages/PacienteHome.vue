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
    <div class="container">

      <!-- WELCOME -->
      <div class="welcome-section">
        <div class="welcome-content">
          <h1>¡Hola, {{ userName }}! 👋</h1>
          <p>Tienes {{ citas.length }} citas próximas. Mantén tu salud al día.</p>
        </div>
        <button class="btn-new-appointment" @click="$router.push('/me/reservar')">+ Nueva Cita</button>
      </div>

      <!-- QUICK STATS -->
      <div class="quick-stats">
        <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-value">{{ citas.length }}</div><div class="stat-label">Citas Próximas</div></div>
        <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-value">{{ stats.completadas }}</div><div class="stat-label">Citas Completadas</div></div>
        <div class="stat-card"><div class="stat-icon">👨‍⚕️</div><div class="stat-value">{{ stats.favoritos }}</div><div class="stat-label">Médicos Favoritos</div></div>
        <div class="stat-card"><div class="stat-icon">📄</div><div class="stat-value">{{ stats.historial }}</div><div class="stat-label">Historial Médico</div></div>
      </div>

      <!-- MAIN -->
      <div class="main-content">
        <!-- APPOINTMENTS -->
        <div class="appointments-section">
          <div class="section-title">📅 Mis Próximas Citas</div>

          <div v-if="!citas.length" class="appointment-card">
            <div class="doctor-details"><h3>Sin citas próximas</h3></div>
            <div class="appointment-details"><div class="detail-item">Programa tu próxima cita con “+ Nueva Cita”.</div></div>
          </div>

          <div v-for="c in citas" :key="c.id" class="appointment-card">
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
              <button class="btn-action btn-cancel">Cancelar</button>
              <button class="btn-action btn-reschedule">Reprogramar</button>
              <button class="btn-action btn-video">🎥 Videollamada</button>
            </div>
          </div>
        </div>

        <!-- SIDEBAR -->
        <div class="sidebar">
          <div class="card">
            <div class="section-title">⭐ Recomendados para ti</div>
            <div class="recommended-doctors">
              <div class="doctor-card" v-for="d in recomendados" :key="d.id">
                <div class="doctor-card-header">
                  <div class="doctor-card-avatar">👨‍⚕️</div>
                  <div class="doctor-card-info">
                    <h4>{{ d.nombre }}</h4>
                    <div class="doctor-card-specialty">{{ d.especialidad }}</div>
                    <div class="rating">⭐ {{ d.rating }} ({{ d.reviews }} reviews)</div>
                  </div>
                </div>
                <button class="btn-book">Agendar Cita</button>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="section-title">💡 Tip de Salud</div>
            <div class="health-tip">
              <div class="tip-icon">💧</div>
              <h4>Mantente Hidratado</h4>
              <p>Bebe al menos 8 vasos de agua al día. La hidratación adecuada mejora tu energía, concentración y salud general.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'

const userName = ref('Carlos Martínez')
const menuOpen = ref(false)
const router = useRouter()

function handleOutside(e){ if (!e.target.closest('.mr-dh')) menuOpen.value = false }
onMounted(()=> document.addEventListener('click', handleOutside))
onBeforeUnmount(()=> document.removeEventListener('click', handleOutside))

async function logout () {
  try { await api.post('/auth/logout') } catch {}
  localStorage.removeItem('token')
  localStorage.removeItem('auth_token')
  localStorage.removeItem('access_token')
  sessionStorage.removeItem('token')
  router.push('/')
}

/* ---------- Cliente Axios central con Bearer automático ---------- */
const getToken = () =>
  localStorage.getItem('token') ||
  localStorage.getItem('auth_token') ||
  localStorage.getItem('access_token') ||
  sessionStorage.getItem('token') ||
  null

const api = axios.create({ baseURL: '/api', withCredentials: true })
api.interceptors.request.use((cfg) => {
  const t = getToken()
  if (t) cfg.headers.Authorization = `Bearer ${t}`
  return cfg
})
/* ----------------------------------------------------------------- */

const stats = ref({ completadas: 0, favoritos: 0, historial: 0 })
const recomendados = ref([
  { id:1, nombre:'Dr. Roberto García', especialidad:'Neurología',   rating:'4.9', reviews:120 },
  { id:2, nombre:'Dra. Ana Torres',    especialidad:'Dermatología', rating:'4.8', reviews:95  },
  { id:3, nombre:'Dr. Diego Sánchez',  especialidad:'Traumatología',rating:'4.9', reviews:150 },
])

const statusClass = (s) => {
  const v = (s||'').toLowerCase()
  if (v.includes('confirm') || v.includes('complet')) return 'status-confirmed'
  if (v.includes('pend')) return 'status-pending'
  return 'status-pending'
}

const citas = ref([])

onMounted(async () => {
  try {
    // (opcional) si quieres refrescar el nombre desde el backend:
    try {
      const me = await api.get('/auth/me')
      if (me?.data?.user?.name) userName.value = me.data.user.name
    } catch (_) { /* ignora si no hay sesión */ }

    const { data } = await api.get('/paciente/citas/proximas')
    citas.value = Array.isArray(data) ? data : (data?.citas ?? [])
  } catch (e) {
    // 401 => token ausente/inválido: no rompas la UI
    if (e?.response?.status === 401) {
      citas.value = []
      // si prefieres, redirige al login:
      // router.push('/login')
    }
    // silenciado: nada de console.error
  }
})
</script>


<style scoped>
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

</style>