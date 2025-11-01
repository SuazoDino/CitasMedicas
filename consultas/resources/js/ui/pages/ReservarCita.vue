<template>
  <section class="page-slot">
    <div class="container reserva">
      <div class="section-title">🩺 Reservar Cita</div>

      <!-- Paso 1: Especialidad -->
      <div class="card">
        <div class="field">
          <label>Especialidad</label>
          <select v-model="especialidadId" @change="cargarMedicos">
            <option value="">Selecciona…</option>
            <option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
      </div>

      <!-- Paso 2: Médico -->
      <div class="card" v-if="especialidadId">
        <div class="section-sub">Médicos disponibles</div>
        <div class="med-grid">
          <button
            v-for="m in medicos"
            :key="m.id"
            class="med-card"
            :class="{active: m.id===medicoId}"
            @click="selectMedico(m.id)"
          >
            <div class="med-avatar">🩺</div>
            <div class="med-name">{{ m.nombre }}</div>
          </button>
        </div>
        <div v-if="!medicos.length" class="muted">No hay médicos para esta especialidad.</div>
      </div>

      <!-- Paso 3: Fecha y Horario -->
      <div class="card" v-if="medicoId">
        <div class="slot-head">
          <div>
            <div class="section-sub">Selecciona fecha</div>
            <input type="date" v-model="fecha" @change="cargarSlots" />
          </div>
          <button class="btn-soft" @click="cargarSlots">Actualizar</button>
        </div>

        <div class="slots-grid">
          <button
            v-for="s in slotsDelDia"
            :key="s.start"
            class="slot"
            :class="{ taken: s.taken, active: s.start===starts_at }"
            :disabled="s.taken"
            @click="pickSlot(s)"
          >{{ toTime(s.start) }}</button>
        </div>

        <div v-if="!slotsDelDia.length" class="muted">No hay horarios configurados para esta fecha.</div>
      </div>

      <!-- Confirmación -->
      <div class="card" v-if="starts_at">
        <div class="resume">
          <div><b>Médico:</b> {{ medicos.find(x=>x.id===medicoId)?.nombre }}</div>
          <div><b>Fecha:</b> {{ toDate(starts_at) }}</div>
          <div><b>Hora:</b>  {{ toTime(starts_at) }}</div>
        </div>
        <div class="cta">
          <button class="btn-primary" :disabled="saving" @click="crearCita">
            {{ saving ? 'Guardando…' : 'Confirmar Reserva' }}
          </button>
          <button class="btn-soft" @click="$router.push({ name: 'paciente.home' })">Cancelar</button>
        </div>
        <p v-if="error" class="err">{{ error }}</p>
        <p v-if="ok" class="ok">¡Cita creada!</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../auth/api'
import { userHorariosStore } from '../stores/horarios'

const router = useRouter()
const especialidades = ref([])
const especialidadId = ref('')
const medicos = ref([])
const medicoId = ref(null)
const fecha = ref(new Date().toISOString().slice(0,10))
const slots = ref([])
const starts_at = ref(null)
const saving = ref(false)
const error = ref('')
const ok = ref(false)
const horariosStore = userHorariosStore()

function toTime(iso){ return new Date(iso).toTimeString().slice(0,5) }
function toDate(iso){ return new Date(iso).toLocaleDateString() }

const slotsDelDia = computed(()=>{
  return slots.value.filter(s => s.start.slice(0,10) === fecha.value)
})

async function cargarEspecialidades(){
  const { data } = await api.get('public/especialidades')
  especialidades.value = Array.isArray(data) ? data : []
}
async function cargarMedicos(){
  if(!especialidadId.value){
    medicos.value = []
    return
  }
  const { data } = await api.get('public/medicos', { params:{ especialidad_id: especialidadId.value } })
  medicos.value = Array.isArray(data) ? data : []
  medicoId.value = null; starts_at.value = null; slots.value = []
}
function selectMedico(id){ medicoId.value = id; cargarSlots() }

async function cargarSlots(){
  if(!medicoId.value) return
  const desde = fecha.value
  const d2 = new Date(fecha.value); d2.setDate(d2.getDate()+6)
  const hasta = d2.toISOString().slice(0,10)
  const { data } = await api.get(`public/medicos/${medicoId.value}/slots`, { params:{ desde, hasta } })
  slots.value = Array.isArray(data) ? data : []
  starts_at.value = null
}
function pickSlot(s){ starts_at.value = s.start }

watch(
  () => horariosStore.versionOf(medicoId.value || 0),
  (version, previous) => {
    if (!medicoId.value) return
    if (typeof previous === 'undefined') return
    if (version !== previous) cargarSlots()
  }
)

async function crearCita(){
  if(!medicoId.value || !starts_at.value) return
  saving.value = true; error.value = ''; ok.value = false
  try{
    const payload = {
      medico_id: medicoId.value,
      especialidad_id: especialidadId.value || null,
      starts_at: starts_at.value,
    }
    const { data } = await api.post('paciente/citas', payload)
    if (data?.id) {
      ok.value = true
      // Ir al panel del paciente
      setTimeout(()=> { ok.value=false; router.replace({ name: 'paciente.home' }) }, 600)
    }else{
      error.value = data?.message || 'No se pudo crear la cita.'
    }
  }catch(e){
    error.value = e?.response?.data?.message || 'No se pudo crear la cita.'
  }finally{ saving.value=false }
}

onMounted(async ()=>{
  await cargarEspecialidades()
})
</script>

<style scoped>
.reserva .section-title{ font-size:22px; font-weight:900; margin:8px 0 12px }
.section-sub{ font-weight:800; margin-bottom:8px }
.card{
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 18px;
  padding: 14px 16px;
  margin-bottom: 12px;
}
.field{ display:grid; gap:8px; max-width:420px }
select, input[type="date"]{
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.18);
  color: #fff; padding:10px 12px; border-radius: 12px; outline:0;
}
.med-grid{ display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:10px }
.med-card{
  display:flex; align-items:center; gap:10px; text-align:left; cursor:pointer;
  background: rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12);
  border-radius:14px; padding:10px 12px;
}
.med-card.active{ outline:2px solid #7f3bf3 }
.med-avatar{ width:36px; height:36px; display:grid; place-items:center; border-radius:50%; background:rgba(255,255,255,.08) }
.med-name{ font-weight:800 }

.slot-head{ display:flex; align-items:end; justify-content:space-between; gap:10px; margin-bottom:8px }
.slots-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(90px,1fr)); gap:8px }
.slot{
  padding:8px; border-radius:10px; border:1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.06); color:#fff;
}
.slot.taken{ opacity:.4; cursor:not-allowed }
.slot.active{ outline:2px solid #ff2a88 }

.resume{ display:grid; gap:6px; margin-bottom:10px }
.cta{ display:flex; gap:8px; flex-wrap:wrap }

.btn-primary{
  padding:10px 14px; border-radius:12px; border:0; color:#fff; cursor:pointer;
  background: linear-gradient(135deg,#ff2a88,#7f3bf3);
  box-shadow: 0 12px 26px rgba(127,59,243,.35);
}
.btn-soft{ padding:10px 14px; border-radius:12px; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.08); color:#fff }
.muted{ color: rgba(255,255,255,.65) }
.err{ color:#fecaca } .ok{ color:#86efac }
</style>
