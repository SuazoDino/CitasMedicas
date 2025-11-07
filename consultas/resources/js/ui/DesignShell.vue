<template>
  <!-- ANIMATED BACKGROUND -->
  <div v-if="!isAuthPage" class="animated-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar" v-if="!isAppNav && !isAuthPage && !isAppPage">
    
    <div class="nav-container">
      <div class="logo">⚡ MediReserva</div>
      <div class="nav-links">
        <a href="#inicio" @click.prevent="scrollTo('#inicio')">Inicio</a>
        <a href="#especialidades" @click.prevent="scrollTo('#especialidades')"
        >Especialidades</a>
        <a href="#nosotros" @click.prevent="scrollTo('#nosotros')">Nosotros</a>
        <div class="bb1">
          <button class="btn-login" @click="go('/login')">Iniciar Sesión</button>
          <!-- <RouterLink to="/login" class="btn-login">Iniciar Sesión</RouterLink> -->

          <button class="btn-login" @click="go('/register/paciente')">Registrarse</button>
        </div>
      </div>
    </div>
  </nav>
  <nav class="navbar appnav" v-else-if="isAppNav">
    <div class="nav-container">
      <div class="logo">⚡ MediReserva</div>

      <div class="nav-right">
        <div class="search-bar">
          <span>🔍</span>
          <input type="text" placeholder="Buscar médicos, especialidades…" />
        </div>

        <!-- User pill -->
        <div class="appnav-user">
          <button class="user-pill" @click.stop="menuOpen = !menuOpen">
            <span class="user-avatar">👤</span>
            <span class="user-info">
              <strong>{{ userName }}</strong>
              <small>{{ roleLabel }}</small>
            </span>
            <span class="caret">▾</span>
          </button>

          <!-- Dropdown -->
          <div v-if="menuOpen" class="menu">
            <a @click.prevent="$router.push('/me')">Mi panel</a>
            <a @click.prevent="$router.push('/me/perfil')">Perfil</a>
            <hr />
            <a @click.prevent="logout">Salir</a>
          </div>
        </div>
      </div>
    </div>
  </nav>



  <!-- HERO -->
  <section class="hero" id="inicio" v-if="isLandingVisible">
    <div class="hero-content">
      <h1>El futuro de la <span class="accent">salud digital</span> está aquí</h1>
      <p>Conectamos pacientes con especialistas de clase mundial a través de tecnología de vanguardia. Tu bienestar, nuestra innovación.</p>
      <div class="hero-buttons">
        <button class="btn-primary" @click="go('/register/paciente')">Reservar Ahora</button>
        <button class="btn-secondary" @click="scrollTo('#especialidades')">Explorar Servicios</button>
      </div>
    </div>
    <div class="hero-visual">
      <div class="floating-card card-1">
        <div class="card-icon">❤️</div>
        <div class="card-title">Cardiología</div>
        <div class="card-value">45+</div>
      </div>
      <div class="floating-card card-2">
        <div class="card-icon">🧠</div>
        <div class="card-title">Neurología</div>
        <div class="card-value">32+</div>
      </div>
      <div class="floating-card card-3">
        <div class="card-icon">⭐</div>
        <div class="card-title">Rating</div>
        <div class="card-value">4.9</div>
      </div>
    </div>
  </section>

  <!-- STATS -->
  <section class="stats" v-if="isLandingVisible">
    <div class="stat-card"><div class="stat-number">500+</div><div class="stat-label">Médicos Especializados</div></div>
    <div class="stat-card"><div class="stat-number">50K+</div><div class="stat-label">Pacientes Satisfechos</div></div>
    <div class="stat-card"><div class="stat-number">30+</div><div class="stat-label">Especialidades Médicas</div></div>
    <div class="stat-card"><div class="stat-number">24/7</div><div class="stat-label">Atención Disponible</div></div>
  </section>

  <!-- SPECIALTIES -->
  <section class="specialties" id="especialidades" v-if="isLandingVisible">
    <h2 class="section-title">Especialidades de Vanguardia</h2>
    <p class="section-subtitle">Profesionales de élite en cada área de la medicina moderna</p>

    <div class="specialty-grid">
      <div class="specialty-card" @click="go('/register/paciente')">
        <div class="specialty-icon">❤️</div>
        <h3>Cardiología</h3>
        <p>Cuidado especializado del corazón con tecnología de diagnóstico avanzada</p>
        <div class="specialty-doctors">45 médicos disponibles →</div>
      </div>
      <div class="specialty-card" @click="go('/register/paciente')">
        <div class="specialty-icon">🧠</div>
        <h3>Neurología</h3>
        <p>Expertos en sistema nervioso con equipamiento neurológico de última generación</p>
        <div class="specialty-doctors">32 médicos disponibles →</div>
      </div>
      <div class="specialty-card" @click="go('/register/paciente')">
        <div class="specialty-icon">👶</div>
        <h3>Pediatría</h3>
        <p>Atención integral y personalizada para tus hijos</p>
        <div class="specialty-doctors">58 médicos disponibles →</div>
      </div>
      <div class="specialty-card" @click="go('/register/paciente')">
        <div class="specialty-icon">🦴</div>
        <h3>Traumatología</h3>
        <p>Rehabilitación y cirugía ortopédica avanzada</p>
        <div class="specialty-doctors">38 médicos disponibles →</div>
      </div>
      <div class="specialty-card" @click="go('/register/paciente')">
        <div class="specialty-icon">🦷</div>
        <h3>Odontología</h3>
        <p>Cuidado dental completo con tecnología digital</p>
        <div class="specialty-doctors">52 médicos disponibles →</div>
      </div>
      <div class="specialty-card" @click="go('/register/paciente')">
        <div class="specialty-icon">👁️</div>
        <h3>Oftalmología</h3>
        <p>Salud visual y cirugías láser precisas</p>
        <div class="specialty-doctors">28 médicos disponibles →</div>
      </div>
    </div>
  </section>
    <!-- ABOUT / NOSOTROS -->
  <section class="about" id="nosotros" v-if="isLandingVisible">
    <h2 class="section-title">Nosotros</h2>
    <p class="section-subtitle">
      Una plataforma independiente que conecta pacientes con médicos de consultorio privado.  
      Tecnología, confianza y atención humana.
    </p>

    <!-- Grid de equipo (similar a Specialties) -->
    <div class="team-grid">
      <div v-for="m in team" :key="m.id" class="team-card">
        <div class="avatar" :style="{ background: m.bg }">
          <span>{{ m.initials }}</span>
        </div>

        <h3 class="team-name">{{ m.name }}</h3>
        <p class="team-role">{{ m.role }}</p>

        <p class="team-bio">
          {{ m.bio }}
        </p>

        <div class="team-tags">
          <span v-for="t in m.tags" :key="t" class="chip">{{ t }}</span>
        </div>
      </div>
    </div>

    <!-- Valores / mini-cards -->
    <div class="values-grid">
      <div v-for="v in values" :key="v.title" class="value-card">
        <div class="value-icon">{{ v.icon }}</div>
        <h4>{{ v.title }}</h4>
        <p>{{ v.desc }}</p>
      </div>
    </div>
  </section>
  <!-- BOOKING SECTION (decorativa por ahora) -->
  <section class="booking-section" v-if="isLandingVisible">
    <div class="booking-container">
      <h2>Agenda Tu Cita Hoy</h2>
      <button class="submit-btn" @click="go('/register/paciente')">Comenzar Registro</button>
    </div>
  </section>

  <!-- FEATURES -->
<!--   <section class="features">
    <div class="feature-card">
      <div class="feature-icon">🔒</div>
      <h3>Seguridad</h3>
      <p>Protegemos tus datos personales y médicos.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🌐</div>
      <h3>Telemedicina</h3>
      <p>Consultas virtuales con especialistas desde casa.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">⚡</div>
      <h3>Tecnología AI</h3>
      <p>Recomendaciones inteligentes de especialistas.</p>
    </div>
  </section> -->

  <!-- FOOTER -->
  <footer v-if="isLandingVisible">
    <div class="footer-content">
      <div class="footer-brand">
        <h3>⚡ MediReserva</h3>
        <p>Revolucionando la atención médica con tecnología de vanguardia. Tu salud, nuestra misión digital.</p>
      </div>
      <div class="footer-links">
        <h4>Compañía</h4>
        <a href="#">Sobre Nosotros</a><a href="#">Equipo</a><a href="#">Carreras</a><a href="#">Blog</a>
      </div>
      <div class="footer-links">
        <h4>Servicios</h4>
        <a href="#">Especialidades</a><a href="#">Médicos</a><a href="#">Teleconsulta</a><a href="#">Emergencias</a>
      </div>
      <div class="footer-links">
        <h4>Legal</h4>
        <a href="#">Términos</a><a href="#">Privacidad</a><a href="#">Seguridad</a><a href="#">Contacto</a>
      </div>
    </div>
    <div class="footer-bottom"><p>© 2025 MediReserva. Innovación en Salud Digital.</p></div>
  </footer>

  <section v-if="isAuthPage" class="auth-page-slot">
    <RouterView />
  </section>

  <!-- 👇 NUEVO: área para páginas normales (medico, me, etc.) -->
  <section v-else class="page-slot">
    <RouterView />
  </section>

</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { onBeforeUnmount } from 'vue' // si no lo tenías

const menuOpen = ref(false)
const onAway = (e) => { if (!e.target.closest('.appnav-user')) menuOpen.value = false }
onMounted(() => document.addEventListener('click', onAway))
onBeforeUnmount(() => document.removeEventListener('click', onAway))

// --- Router (debe ir primero para que no haya TDZ/undefined) ---
const route = useRoute()
const router = useRouter()
router.afterEach(() => { menuOpen.value = false })

const AUTH_ROUTES = ['/login', '/forgot-password', '/reset-password', '/register/paciente', '/register/medico']

// --- ¿Página de autenticación? (pantalla completa)
const isAuthPage = computed(() => AUTH_ROUTES.includes(route.path))

// --- ¿Página interna de la app? (/me, /medico, o /doctor) ---
const isAppPage = computed(() =>
  route.path.startsWith('/me') || route.path.startsWith('/medico') || route.path.startsWith('/doctor')
)

// --- Navbar compacta del panel (app nav) - solo para médico, paciente tiene su propio header ---
const isAppNav = computed(() => !isAuthPage.value && route.path.startsWith('/medico'))

// --- ¿Mostrar landing (hero, stats, specialties, about, footer)? ---
const isLandingVisible = computed(() => !isAuthPage.value && !isAppPage.value)

// --- (opcional) etiqueta de rol para evitar warnings si la usas en el template ---
const roleLabel = computed(() =>
  route.path.startsWith('/medico') ? 'Médico'
  : route.path.startsWith('/me')   ? 'Paciente'
  : ''
)

// --- Sesión básica para el header ---
const userName = ref('Mi cuenta')

// helpers: toma un token si existe
const getToken = () =>
  localStorage.getItem('token') ||
  localStorage.getItem('auth_token') ||
  localStorage.getItem('access_token') ||
  sessionStorage.getItem('token') ||
  null;

onMounted(async () => {
  const t = getToken();

  // Sin token => modo front-only: usa fallback y no llames al back
  if (!t) {
    const cached = localStorage.getItem('user_name');
    if (cached) userName.value = cached;
    return;
  }

  try {
    const { data } = await axios.get('/api/auth/me', {
      headers: { Authorization: `Bearer ${t}` }
    });
    if (data?.user?.name) userName.value = data.user.name;
  } catch (err) {
    // Si el token no sirve, no molestes en consola y limpia el header
    if (err?.response?.status === 401) {
      delete axios.defaults.headers.common.Authorization;
      // opcional: limpia tokens inválidos
      // localStorage.removeItem('token'); localStorage.removeItem('auth_token'); sessionStorage.removeItem('token');
    }
    // silenciar: nada de console.error aquí
  }
});


async function logout () {
  try { await axios.post('/api/auth/logout') } catch {}
  localStorage.removeItem('token'); localStorage.removeItem('auth_token'); sessionStorage.removeItem('token')
  router.push('/')
}

// --- Utilidades UI ---
function go(path){
  if (route.path === path) return
  router.push(path)
}

function scrollTo(sel){ document.querySelector(sel)?.scrollIntoView({ behavior:'smooth' }) }

// --- Data "Nosotros" (igual a la tuya) ---
const team = [
  {
    id: 1,
    name: 'María Silva',
    initials: 'MS',
    role: 'Directora Médica',
    bio: 'Especialista en gestión clínica y calidad asistencial. Lidera la red de médicos.',
    tags: ['Calidad', 'Protocolos', 'Red Médica'],
    bg: 'linear-gradient(135deg,#ff006e,#ff7ac2)'
  },
  {
    id: 2,
    name: 'Juan Pérez',
    initials: 'JP',
    role: 'CTO',
    bio: 'Arquitecto de software y seguridad. Escala la plataforma y cuida tu privacidad.',
    tags: ['Seguridad', 'Arquitectura', 'DevOps'],
    bg: 'linear-gradient(135deg,#8338ec,#3ecbff)'
  },
  {
    id: 3,
    name: 'Carla Rojas',
    initials: 'CR',
    role: 'Experiencia de Usuario',
    bio: 'Diseña flujos simples y accesibles para pacientes y médicos.',
    tags: ['UX', 'Accesibilidad', 'Investigación'],
    bg: 'linear-gradient(135deg,#00f5ff,#36f3b9)'
  },
  {
    id: 4,
    name: 'Diego León',
    initials: 'DL',
    role: 'Operaciones',
    bio: 'Monitorea indicadores y soporte para que todo funcione 24/7.',
    tags: ['SLA', 'Soporte', 'Analítica'],
    bg: 'linear-gradient(135deg,#ff9a3c,#ff2a6d)'
  },
]

const values = [
  { icon:'🔒', title:'Privacidad primero', desc:'Encriptación y mínimos datos por defecto.' },
  { icon:'⚡',  title:'Rápido y simple',     desc:'Reservas en menos de 60 segundos.' },
  { icon:'🤝',  title:'Independencia',       desc:'Hecho para médicos de consultorio privado.' },
]
</script>


<style>
/* ======== TU CSS (idéntico al que mandaste) con agregado del overlay ======== */
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0118;color:#fff;overflow-x:hidden}
.animated-bg{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1;background:linear-gradient(135deg,#0a0118 0%,#1a0b2e 50%,#16213e 100%)}
.orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.4;animation:float 20s infinite ease-in-out}
.orb-1{width:500px;height:500px;background:#ff006e;top:-10%;left:-10%}
.orb-2{width:400px;height:400px;background:#8338ec;top:40%;right:-5%;animation-delay:5s}
.orb-3{width:350px;height:350px;background:#00f5ff;bottom:-10%;left:30%;animation-delay:10s}
@keyframes float{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(50px,-50px) scale(1.1)}66%{transform:translate(-30px,30px) scale(.9)}}
.navbar{background:rgba(10,1,24,.8);backdrop-filter:blur(20px);padding:20px 0;border-bottom:1px solid rgba(255,255,255,.1);position:sticky;top:0;z-index:100}
.bb1{
  display: flex;
  gap: 10px;
}
.nav-container{max-width:1400px;margin:0 auto;padding:0 40px;display:flex;justify-content:space-between;align-items:center}
.logo{font-size:28px;font-weight:800;background:linear-gradient(135deg,#ff006e,#8338ec,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;display:flex;align-items:center;gap:10px}
.nav-links{display:flex;gap:40px;align-items:center}
.nav-links a{text-decoration:none;color:rgba(255,255,255,.7);font-weight:500;transition:.3s;position:relative}
.nav-links a:hover{color:#00f5ff}
.nav-links a::after{content:'';position:absolute;bottom:-5px;left:0;width:0;height:2px;background:linear-gradient(90deg,#ff006e,#00f5ff);transition:width .3s}
.nav-links a:hover::after{width:100%}
.btn-login{padding:12px 28px;background:linear-gradient(135deg,#ff006e,#8338ec);border:0;border-radius:50px;color:#fff;font-weight:600;cursor:pointer;transition:.3s;box-shadow:0 0 30px rgba(255,0,110,.3)}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 0 40px rgba(255,0,110,.6)}
.hero{max-width:1400px;margin:100px auto;padding:0 40px;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}
.hero-content h1{font-size:64px;font-weight:900;line-height:1.1;margin-bottom:24px;background:linear-gradient(135deg,#fff,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-content .accent{background:linear-gradient(135deg,#ff006e,#8338ec);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-content p{font-size:20px;color:rgba(255,255,255,.7);margin-bottom:40px;line-height:1.7}
.hero-buttons{display:flex;gap:20px}
.btn-primary{padding:18px 40px;background:linear-gradient(135deg,#ff006e,#8338ec);color:#fff;border:0;border-radius:50px;font-size:16px;font-weight:700;cursor:pointer;transition:.3s;box-shadow:0 10px 40px rgba(255,0,110,.3)}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 15px 50px rgba(255,0,110,.5)}
.btn-secondary{padding:18px 40px;background:rgba(255,255,255,.1);backdrop-filter:blur(10px);color:#fff;border:2px solid rgba(255,255,255,.2);border-radius:50px;font-size:16px;font-weight:700;cursor:pointer;transition:.3s}
.btn-secondary:hover{background:rgba(255,255,255,.2);border-color:#00f5ff}
.hero-visual{position:relative;height:500px}
.floating-card{position:absolute;background:rgba(255,255,255,.1);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.2);border-radius:20px;padding:25px;animation:floatCard 6s infinite ease-in-out}
.card-1{top:50px;left:50px}
.card-2{top:200px;right:50px}
.card-3{bottom:100px;left:100px}
@keyframes floatCard{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
.card-icon{font-size:40px;margin-bottom:10px}
.card-title{font-size:16px;font-weight:700;margin-bottom:5px}
.card-value{font-size:24px;font-weight:900;background:linear-gradient(135deg,#ff006e,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
/* NOSOTROS */
.about{
  max-width: 1400px;
  margin: 100px auto;
  padding: 0 40px;
}

.team-grid{
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap:30px;
  margin-top: 40px;
}

.team-card{
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(20px);
  border:1px solid rgba(255,255,255,0.1);
  padding: 34px 28px;
  border-radius: 20px;
  text-align: center;
  transition: all .35s;
  position: relative;
  overflow: hidden;
}
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

.team-card::before{
  content:'';
  position:absolute; inset:0;
  background: radial-gradient(600px 120px at -10% -10%, rgba(255,0,110,.08), transparent 50%);
  opacity:0; transition: opacity .35s;
}
.team-card:hover{ transform: translateY(-8px); border-color:#00f5ff; box-shadow:0 20px 60px rgba(0,245,255,.12) }
.team-card:hover::before{ opacity:1 }

.avatar{
  width:84px; height:84px; border-radius:50%;
  margin: 0 auto 14px; display:grid; place-items:center;
  box-shadow: 0 8px 30px rgba(0,0,0,.35);
  border:2px solid rgba(255,255,255,.15);
}
.avatar span{ font-weight:900; color:#fff; font-size:28px; letter-spacing:.3px }

.team-name{ font-size:20px; margin-top:4px; font-weight:800 }
.team-role{ color: rgba(255,255,255,.7); font-size:14px; margin-bottom:8px }
.team-bio{ color: rgba(255,255,255,.65); font-size:14px; line-height:1.6; margin-top:4px }

.team-tags{ display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-top:14px }
.chip{
  font-size:12px; padding:8px 10px; border-radius:999px;
  background: rgba(255,255,255,.08);
  border:1px solid rgba(255,255,255,.12);
}

.values-grid{
  display:grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr));
  gap:24px; margin-top: 36px;
}
.value-card{
  text-align:center; padding:28px;
  background: rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  border-radius:16px; transition: all .3s;
}
.value-card:hover{ transform: translateY(-6px); border-color:#8338ec }
.value-icon{ font-size:36px; margin-bottom:10px }
.value-card h4{ margin-bottom:6px; font-weight:800 }
.value-card p{ color: rgba(255,255,255,.65) }

.stats{max-width:1400px;margin:100px auto;padding:0 40px;display:grid;grid-template-columns:repeat(4,1fr);gap:30px}
.stat-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);padding:40px;border-radius:20px;text-align:center;transition:.3s}
.stat-card:hover{background:rgba(255,255,255,.1);border-color:#00f5ff;transform:translateY(-5px)}
.stat-number{font-size:48px;font-weight:900;background:linear-gradient(135deg,#ff006e,#8338ec,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:10px}
.stat-label{color:rgba(255,255,255,.6);font-size:14px;font-weight:500}

.specialties{max-width:1400px;margin:100px auto;padding:0 40px}
.section-title{font-size:48px;font-weight:900;text-align:center;margin-bottom:20px;background:linear-gradient(135deg,#fff,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.section-subtitle{text-align:center;color:rgba(255,255,255,.6);font-size:18px;margin-bottom:60px}
.specialty-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px}
.specialty-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);padding:40px 30px;border-radius:20px;text-align:center;cursor:pointer;transition:.4s;position:relative;overflow:hidden}
.specialty-card::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,0,110,.1),transparent);transition:left .5s}
.specialty-card:hover::before{left:100%}
.specialty-card:hover{transform:translateY(-10px);border-color:#ff006e;box-shadow:0 20px 60px rgba(255,0,110,.3)}
.specialty-icon{font-size:60px;margin-bottom:20px;filter:drop-shadow(0 0 20px rgba(255,0,110,.5))}
.specialty-card h3{font-size:24px;margin-bottom:12px}
.specialty-card p{color:rgba(255,255,255,.6);font-size:14px;line-height:1.6;margin-bottom:20px}
.specialty-doctors{color:#00f5ff;font-size:13px;font-weight:600}

.booking-section{background:linear-gradient(135deg,rgba(255,0,110,.1),rgba(131,56,236,.1));padding:100px 40px;margin:100px 0;border-top:1px solid rgba(255,255,255,.1);border-bottom:1px solid rgba(255,255,255,.1)}
.booking-container{max-width:900px;margin:0 auto;background:rgba(255,255,255,.05);backdrop-filter:blur(30px);border:1px solid rgba(255,255,255,.1);padding:50px;border-radius:30px;box-shadow:0 30px 80px rgba(0,0,0,.5)}
.booking-container h2{font-size:40px;margin-bottom:40px;text-align:center;background:linear-gradient(135deg,#ff006e,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.submit-btn{width:100%;padding:20px;background:linear-gradient(135deg,#ff006e,#8338ec);color:#fff;border:0;border-radius:50px;font-size:18px;font-weight:700;cursor:pointer;margin-top:20px;transition:.3s;box-shadow:0 10px 40px rgba(255,0,110,.4)}
.submit-btn:hover{transform:translateY(-3px);box-shadow:0 15px 50px rgba(255,0,110,.6)}

.features{max-width:1400px;margin:100px auto;padding:0 40px;display:grid;grid-template-columns:repeat(3,1fr);gap:40px}
.feature-card{text-align:center;padding:40px;background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:20px;transition:.3s}
.feature-card:hover{transform:translateY(-5px);border-color:#8338ec}
.feature-icon{width:100px;height:100px;background:linear-gradient(135deg,#ff006e,#8338ec);border-radius:25px;display:flex;align-items:center;justify-content:center;font-size:50px;margin:0 auto 25px;box-shadow:0 10px 40px rgba(255,0,110,.4)}

footer{background:rgba(10,1,24,.8);backdrop-filter:blur(20px);border-top:1px solid rgba(255,255,255,.1);color:#fff;padding:60px 40px 30px}
.footer-content{max-width:1400px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:60px;margin-bottom:40px}
.footer-brand h3{font-size:24px;margin-bottom:15px;background:linear-gradient(135deg,#ff006e,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.footer-brand p{color:rgba(255,255,255,.6);line-height:1.6}
.footer-links h4{margin-bottom:20px;font-size:16px}
.footer-links a{display:block;color:rgba(255,255,255,.6);text-decoration:none;margin-bottom:12px;transition:.3s}
.footer-links a:hover{color:#00f5ff;padding-left:5px}
.footer-bottom{max-width:1400px;margin:0 auto;padding-top:30px;border-top:1px solid rgba(255,255,255,.1);text-align:center;color:rgba(255,255,255,.4)}

@media (max-width:1024px){
  .hero{grid-template-columns:1fr;gap:40px}
  .stats{grid-template-columns:repeat(2,1fr)}
  .footer-content{grid-template-columns:1fr 1fr}
}
@media (max-width:768px){
  .hero-content h1{font-size:40px}
  .stats{grid-template-columns:1fr}
  .nav-links{display:none}
}

/* ==== PÁGINAS INTERNAS (slot) ==== */
.auth-page-slot{min-height:100vh;display:flex;flex-direction:column;align-items:stretch;justify-content:stretch;background:#070212}
.auth-page-slot>*{flex:1 1 auto}
.page-slot{
  max-width:1200px;
  margin:0 auto;
  padding:40px 24px;
  color:#fff;
}

/* Títulos y textos dentro de páginas internas */
.page-slot h1{font-size:32px;font-weight:900;margin-bottom:12px}
.page-slot p{color:rgba(255,255,255,.70)}

/* Listas: quitar viñetas y márgenes (adiós puntitos) */
.page-slot ul{list-style:none;margin:0;padding:0}

/* Tarjetas reutilizables (glass) si las usas */
.page-slot .section{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.10);
  border-radius:20px;
  padding:24px;
  margin-bottom:24px;
}

/* Filas de lista con separador */
.page-slot .row{
  display:flex;align-items:center;justify-content:space-between;
  padding:10px 0;border-bottom:1px dashed rgba(255,255,255,.08)
}
.page-slot .row:last-child{border-bottom:0}

/* Badge / contador */
.page-slot .badge{
  display:inline-block;padding:6px 10px;border-radius:999px;
  background:linear-gradient(135deg,#ff006e,#8338ec)
}
.page-slot ul, .page-slot ol { list-style: none !important; margin: 0; padding: 0; }
.page-slot li { list-style: none !important; }
.page-slot li::marker { content: '' !important; }  /* por si algún navegador insiste */
.page-slot li:empty { display: none; }              /* evita puntitos de <li> vacíos */

/* ==== UI comunes para páginas internas ==== */
.page-slot h1{
  font-size:36px;font-weight:900;margin-bottom:16px;
  background:linear-gradient(135deg,#fff,#00f5ff);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}

/* Tarjeta glass */
.page-slot .card{
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius:20px; padding:24px;
  backdrop-filter:blur(10px);
  box-shadow:0 10px 40px rgba(0,0,0,.35);
  transition:transform .2s ease,border-color .2s ease;
}
.page-slot .card:hover{ transform:translateY(-3px); border-color:rgba(0,245,255,.35) }

/* Lista bonita */
.page-slot .list{ list-style:none; margin:0; padding:0 }
.page-slot .list li{
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 0; border-bottom:1px dashed rgba(255,255,255,.10)
}
.page-slot .list li:last-child{ border-bottom:0 }

/* Botones */
.page-slot .btn{
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  padding:12px 18px; border-radius:999px; font-weight:700;
  border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.08); color:#fff;
  cursor:pointer; transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.page-slot .btn:hover{ transform:translateY(-2px); box-shadow:0 10px 40px rgba(0,0,0,.35); border-color:rgba(255,255,255,.25) }
.page-slot .btn-primary{ background:linear-gradient(135deg,#ff006e,#8338ec); border-color:rgba(255,255,255,.15) }
.page-slot .btn-ghost{ background:rgba(255,255,255,.08) }

/* === PATIENT DASHBOARD (scoped por .page-slot) === */
.page-slot .container{max-width:1400px;margin:40px auto;padding:0 40px}
.page-slot .welcome-section{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:40px;margin-bottom:40px;display:flex;justify-content:space-between;align-items:center}
.page-slot .welcome-content h1{font-size:36px;margin-bottom:10px;background:linear-gradient(135deg,#fff,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.page-slot .welcome-content p{color:rgba(255,255,255,.6);font-size:16px}
.page-slot .btn-new-appointment{padding:16px 32px;background:linear-gradient(135deg,#ff006e,#8338ec);border:0;border-radius:50px;color:#fff;font-weight:700;cursor:pointer;transition:.3s;box-shadow:0 10px 40px rgba(255,0,110,.4);font-size:16px}
.page-slot .btn-new-appointment:hover{transform:translateY(-3px);box-shadow:0 15px 50px rgba(255,0,110,.6)}

.page-slot .quick-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:40px}
.page-slot .stat-card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);padding:30px;border-radius:20px;transition:.3s}
.page-slot .stat-card:hover{background:rgba(255,255,255,.1);border-color:#00f5ff;transform:translateY(-5px)}
.page-slot .stat-icon{font-size:36px;margin-bottom:15px}
.page-slot .stat-value{font-size:32px;font-weight:900;background:linear-gradient(135deg,#ff006e,#00f5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:5px}
.page-slot .stat-label{color:rgba(255,255,255,.6);font-size:14px}

.page-slot .main-content{display:grid;grid-template-columns:2fr 1fr;gap:30px}
.page-slot .section-title{font-size:24px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px}

.page-slot .appointments-section{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:30px}
.page-slot .appointment-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:25px;margin-bottom:20px;transition:.3s}
.page-slot .appointment-card:hover{background:rgba(255,255,255,.1);border-color:#8338ec;transform:translateX(5px)}
.page-slot .appointment-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.page-slot .doctor-info{display:flex;gap:15px;align-items:center}
.page-slot .doctor-avatar{width:60px;height:60px;background:linear-gradient(135deg,#ff006e,#8338ec);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px}
.page-slot .doctor-details h3{font-size:18px;margin-bottom:5px}
.page-slot .doctor-specialty{color:rgba(255,255,255,.6);font-size:14px}
.page-slot .appointment-status{padding:8px 16px;border-radius:50px;font-size:12px;font-weight:600}
.page-slot .status-confirmed{background:rgba(0,245,255,.2);color:#00f5ff}
.page-slot .status-pending{background:rgba(255,177,0,.2);color:#ffb100}
.page-slot .appointment-details{display:flex;gap:30px;margin-bottom:20px;padding:15px;background:rgba(255,255,255,.03);border-radius:12px}
.page-slot .detail-item{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.8);font-size:14px}
.page-slot .appointment-actions{display:flex;gap:10px}
.page-slot .btn-action{flex:1;padding:10px 20px;border-radius:10px;border:0;font-weight:600;cursor:pointer;transition:.3s;font-size:14px}
.page-slot .btn-cancel{background:rgba(255,0,110,.2);color:#ff006e;border:1px solid rgba(255,0,110,.3)}
.page-slot .btn-cancel:hover{background:rgba(255,0,110,.3)}
.page-slot .btn-reschedule{background:rgba(131,56,236,.2);color:#8338ec;border:1px solid rgba(131,56,236,.3)}
.page-slot .btn-reschedule:hover{background:rgba(131,56,236,.3)}
.page-slot .btn-video{background:linear-gradient(135deg,#ff006e,#8338ec);color:#fff;border:0}
.page-slot .btn-video:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(255,0,110,.4)}

.page-slot .sidebar{display:flex;flex-direction:column;gap:30px}
.page-slot .card{background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:30px}
.page-slot .recommended-doctors{display:flex;flex-direction:column;gap:15px}
.page-slot .doctor-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:20px;cursor:pointer;transition:.3s}
.page-slot .doctor-card:hover{background:rgba(255,255,255,.1);border-color:#00f5ff;transform:translateY(-3px)}
.page-slot .doctor-card-header{display:flex;gap:15px;margin-bottom:15px}
.page-slot .doctor-card-avatar{width:50px;height:50px;background:linear-gradient(135deg,#ff006e,#8338ec);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px}
.page-slot .doctor-card-info h4{font-size:16px;margin-bottom:5px}
.page-slot .doctor-card-specialty{color:rgba(255,255,255,.6);font-size:13px;margin-bottom:5px}
.page-slot .rating{display:flex;align-items:center;gap:5px;color:#ffb100;font-size:13px}
.page-slot .btn-book{width:100%;padding:12px;background:rgba(131,56,236,.2);border:1px solid rgba(131,56,236,.3);border-radius:10px;color:#8338ec;font-weight:600;cursor:pointer;transition:.3s}
.page-slot .btn-book:hover{background:linear-gradient(135deg,#ff006e,#8338ec);color:#fff;border:0}

.page-slot .health-tip{background:linear-gradient(135deg,rgba(255,0,110,.1),rgba(131,56,236,.1));border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:20px}
.page-slot .tip-icon{font-size:32px;margin-bottom:15px}
.page-slot .health-tip h4{font-size:16px;margin-bottom:10px}
.page-slot .health-tip p{color:rgba(255,255,255,.7);font-size:14px;line-height:1.6}

@media (max-width:1024px){
  .page-slot .main-content{grid-template-columns:1fr}
  .page-slot .quick-stats{grid-template-columns:repeat(2,1fr)}
}
@media (max-width:768px){
  .page-slot .welcome-section{flex-direction:column;text-align:center;gap:20px}
  .page-slot .quick-stats{grid-template-columns:1fr}
}

/* ====== Header de app (panel interno) ====== */
.appnav .nav-right{display:flex;align-items:center;gap:30px}
.appnav .search-bar{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:10px 16px;display:flex;align-items:center;gap:10px;width:300px}
.appnav .search-bar input{background:transparent;border:0;outline:0;color:#fff;width:100%}
.appnav .search-bar input::placeholder{color:rgba(255,255,255,.5)}
.appnav .user-menu{display:flex;align-items:center;gap:12px;padding:8px 12px;background:rgba(255,255,255,.06);border-radius:999px;border:1px solid rgba(255,255,255,.1)}
.appnav .user-avatar{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#ff006e,#8338ec)}
.appnav .user-info h4{font-size:14px;font-weight:700}
.appnav .user-info p{font-size:12px;color:rgba(255,255,255,.6)}
.appnav .btn-logout{background:transparent;border:0;color:#fff;opacity:.75;cursor:pointer}
.appnav .btn-logout:hover{opacity:1;text-decoration:underline}

/* anclar el menú correctamente */
.appnav .nav-container{ position:relative }  /* para anclar el dropdown */
.appnav .nav-right{ display:flex; align-items:center; gap:16px }

.appnav .appnav-user{ position:relative }
.appnav .user-pill{
  display:flex; align-items:center; gap:10px;
  height:44px; padding:6px 14px; cursor:pointer;
  border:1px solid rgba(255,255,255,.16); border-radius:999px;
  background:rgba(255,255,255,.06); color:#fff;
}
.appnav .user-avatar{
  width:28px; height:28px; border-radius:50%;
  display:grid; place-items:center; background:linear-gradient(135deg,#ff006e,#8338ec);
  font-size:14px;
}
.appnav .user-info{ display:flex; flex-direction:column; line-height:1.05 }
.appnav .user-info strong{ font-size:12px }
.appnav .user-info small{ font-size:11px; color:rgba(255,255,255,.7) }
.appnav .caret{ opacity:.8 }

.appnav .menu{
  position:absolute; top:56px; right:0; z-index:300;
  min-width:200px; padding:6px 0;
  background:rgba(20,20,35,.96); border:1px solid rgba(255,255,255,.12);
  border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.45); backdrop-filter:blur(10px);
}
.appnav .menu a{
  display:block; padding:10px 12px; text-decoration:none; color:#e8f0ff;
}
.appnav .menu a:hover{ background:rgba(255,255,255,.06) }
.appnav .menu hr{ border:0; border-top:1px solid rgba(255,255,255,.08); margin:4px 0 }

</style>