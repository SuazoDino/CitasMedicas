<template>
  <div class="me-wrap">
    <header class="me-head">
      <div>
        <h2>¡Hola, {{ user.name || 'Usuario' }}!</h2>
        <p class="muted">Bienvenido a tu panel. Rol: <b>{{ roleLabel }}</b></p>
      </div>
      <button class="btn" @click="goHome">Ir a la portada</button>
    </header>

    <!-- Cards principales -->
    <section class="grid">
      <!-- PACIENTE -->
      <template v-if="isPaciente">
        <article class="card">
          <h3>Próximas citas</h3>
          <ul class="list" v-if="appointments.length">
            <li v-for="a in appointments" :key="a.id">
              <div>
                <b>{{ a.fecha_hum }}</b>
                <div class="muted">{{ a.especialidad }} • {{ a.medico }}</div>
              </div>
              <button class="ghost" @click="cancel(a.id)">Cancelar</button>
            </li>
          </ul>
          <p v-else class="muted">No tienes citas. ¡Agenda una ahora!</p>
          <div class="row">
            <button class="btn-grad" @click="reservar">Reservar cita</button>
          </div>
        </article>

        <article class="card">
          <h3>Recomendado</h3>
          <p class="muted">Especialidades populares: <b>Cardiología</b>, <b>Pediatría</b>, <b>Traumatología</b>.</p>
          <button class="ghost" @click="scroll('#especialidades')">Explorar especialidades</button>
        </article>
      </template>

      <!-- MÉDICO -->
      <template v-else-if="isMedico">
        <article class="card">
          <h3>Agenda de hoy</h3>
          <ul class="list" v-if="agenda.length">
            <li v-for="c in agenda" :key="c.id">
              <div>
                <b>{{ c.hora }}</b>
                <div class="muted">{{ c.paciente }} — {{ c.motivo }}</div>
              </div>
              <button class="ghost">Ver ficha</button>
            </li>
          </ul>
          <p v-else class="muted">Sin citas hoy.</p>
        </article>

        <article class="card">
          <h3>Disponibilidad</h3>
          <p class="muted">Configura tus horarios visibles para los pacientes.</p>
          <div class="row">
            <button class="btn-grad" @click="configurarHorarios">Configurar horarios</button>
            <button class="ghost" @click="verMiPerfil">Mi perfil</button>
          </div>
          <p class="note" v-if="user.verif_status !== 'verificado'">
            Estado de verificación: <b>{{ user.verif_status || 'pendiente' }}</b>
          </p>
        </article>
      </template>

      <!-- ADMIN -->
      <template v-else-if="isAdmin">
        <article class="card">
          <h3>Resumen</h3>
          <p class="muted">Usuarios: {{ kpis.usuarios }} • Médicos: {{ kpis.medicos }} • Citas (hoy): {{ kpis.citasHoy }}</p>
          <div class="row">
            <button class="btn-grad">Ver reportes</button>
            <button class="ghost">Usuarios</button>
          </div>
        </article>
      </template>
    </section>
  </div>
</template>

<script setup>
import { onMounted, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../auth/api'
import { auth } from '../../auth/store'

const router = useRouter()
const state = reactive({
  user: {},
  roles: [],
  appointments: [],
  agenda: [],
  kpis: { usuarios: 0, medicos: 0, citasHoy: 0 },
})

// helpers de rol
const isPaciente = computed(() => state.roles.includes('paciente'))
const isMedico   = computed(() => state.roles.includes('medico'))
const isAdmin    = computed(() => state.roles.includes('admin'))
const roleLabel  = computed(() => isPaciente.value ? 'Paciente' : isMedico.value ? 'Médico' : isAdmin.value ? 'Admin' : '—')

// accesibles desde template
const user = state.user
const appointments = state.appointments
const agenda = state.agenda
const kpis = state.kpis

onMounted(load)

async function load(){
  try{
    const me = await api.get('/auth/me')
    state.user  = me.data.user
    state.roles = me.data.roles || []
  }catch{
    // fallback si el API aún no está listo
    state.user  = { id: 1, name: 'Demo', verif_status:'pendiente' }
    state.roles = ['paciente']  // cámbialo a ['medico'] o ['admin'] para ver otras vistas
  }

  // Data por rol
  if (isPaciente.value){
    try{
      const { data } = await api.get('/patient/appointments/upcoming')
      state.appointments = data
    }catch{
      state.appointments = [
        { id: 101, fecha_hum:'Vie 31 · 09:00', especialidad:'Cardiología', medico:'Dra. Silva' },
        { id: 102, fecha_hum:'Lun 03 · 16:00', especialidad:'Pediatría',  medico:'Dr. Rojas' },
      ]
    }
  }

  if (isMedico.value){
    try{
      const { data } = await api.get('/doctor/agenda/today')
      state.agenda = data
    }catch{
      state.agenda = [
        { id: 201, hora:'09:00', paciente:'Juan P.', motivo:'Chequeo' },
        { id: 202, hora:'10:00', paciente:'Carla R.', motivo:'Dolor lumbar' },
      ]
    }
  }

  if (isAdmin.value){
    try{
      const { data } = await api.get('/admin/kpis/mini')
      state.kpis = data
    }catch{
      state.kpis = { usuarios: 128, medicos: 34, citasHoy: 12 }
    }
  }
}

function goHome(){ router.push('/') }
function reservar(){ router.push('/register/paciente') }
function verMiPerfil(){ /* futura pantalla de perfil */ }
function configurarHorarios(){ /* futuro módulo */ }
function scroll(sel){ document.querySelector(sel)?.scrollIntoView({ behavior:'smooth' }) }

async function cancel(id){
  try{
    await api.delete(`/patient/appointments/${id}`)
    state.appointments = state.appointments.filter(x => x.id !== id)
  }catch{ /* toast */ }
}
</script>

<style scoped>
.me-wrap{ max-width:1100px; margin:60px auto; padding:0 24px }
.me-head{ display:flex; align-items:end; justify-content:space-between; margin-bottom:18px }
.me-head h2{ font-size:28px; font-weight:900; background:linear-gradient(135deg,#fff,#00f5ff); -webkit-background-clip:text; -webkit-text-fill-color:transparent }
.muted{ color:rgba(255,255,255,.65) }
.grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(320px,1fr)); gap:22px }
.card{
  background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
  border-radius:16px; padding:20px; backdrop-filter: blur(20px);
}
.card h3{ margin-bottom:8px; font-weight:800 }
.list{ display:flex; flex-direction:column; gap:12px; margin:12px 0 }
.list li{ display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px dashed rgba(255,255,255,.08) }
.row{ display:flex; gap:10px; margin-top:10px }
.btn{ padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.15); background:transparent; color:#fff; cursor:pointer }
.btn-grad{
  padding:12px 16px; border-radius:999px; border:0; cursor:pointer; color:#fff; font-weight:800;
  background:linear-gradient(135deg,#ff2a6d,#9b4dff);
  box-shadow:0 8px 32px rgba(255,42,109,.4);
}
.ghost{ background:transparent; color:#9edcff; border:1px solid rgba(158,220,255,.25); border-radius:10px; padding:8px 12px; cursor:pointer }
.note{ margin-top:10px; color:#ffd6a1 }
@media (max-width:640px){ .me-head{ flex-direction:column; gap:10px; align-items:flex-start } }
</style>
