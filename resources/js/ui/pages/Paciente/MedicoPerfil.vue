<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8" v-if="medico">
    <router-link to="/paciente/buscar" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 mb-4">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Volver a la búsqueda
    </router-link>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Información del médico -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
          <div class="flex items-start gap-4">
            <div class="h-20 w-20 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-2xl shrink-0">
              {{ initials }}
            </div>
            <div>
              <h1 class="text-2xl font-bold text-slate-900">Dr(a). {{ emailName }}</h1>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span
                  v-for="esp in medico.especialidades"
                  :key="esp.id"
                  class="text-xs bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full font-medium"
                >
                  {{ esp.nombre }}
                </span>
              </div>
              <div class="flex flex-wrap items-center gap-2 mt-3 text-sm">
                <div class="flex items-center gap-1 text-amber-500">
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                  </svg>
                  <span class="font-semibold text-slate-800">
                    {{ medico.calificacion_promedio ? Number(medico.calificacion_promedio).toFixed(1) : 'Nuevo' }}
                  </span>
                </div>
                <span class="text-slate-400">·</span>
                <span class="text-slate-500">{{ medico.resenas_count || 0 }} reseñas</span>
                <template v-if="medico.verif_status === 'verificado'">
                  <span class="text-slate-400">·</span>
                  <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Verificado
                  </span>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Reseñas -->
        <div class="bg-white rounded-2xl border border-slate-100 p-6">
          <h2 class="font-semibold text-slate-900 mb-4">Opiniones de pacientes</h2>
          <div v-if="!medico.resenas || medico.resenas.length === 0" class="text-sm text-slate-500">
            Este médico aún no tiene reseñas.
          </div>
          <div v-else class="space-y-4">
            <div v-for="r in medico.resenas" :key="r.id" class="pb-4 border-b border-slate-100 last:border-0 last:pb-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-800">
                  {{ r.paciente?.usuario?.email?.split('@')[0] || 'Paciente' }}
                </p>
                <div class="flex items-center gap-1 text-amber-500 text-sm">
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                  </svg>
                  {{ r.calificacion }}
                </div>
              </div>
              <p class="text-sm text-slate-600 mt-1">{{ r.comentario }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Widget de reserva -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-100 p-5 lg:sticky lg:top-20">
          <h3 class="font-semibold text-slate-900 mb-3">Agendar cita</h3>

          <div class="mb-4" v-if="medico.especialidades.length > 1">
            <label class="text-xs font-medium text-slate-500 block mb-1">Especialidad</label>
            <select v-model="especialidadSeleccionada" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
              <option v-for="esp in medico.especialidades" :key="esp.id" :value="esp.id">{{ esp.nombre }}</option>
            </select>
          </div>

          <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-1 px-1">
            <button
              v-for="dia in proximosDias"
              :key="dia.value"
              @click="seleccionarFecha(dia.value)"
              :class="[
                'flex flex-col items-center justify-center min-w-[52px] py-2 rounded-xl border text-xs font-medium transition-colors shrink-0',
                fechaSeleccionada === dia.value ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-600 hover:border-blue-300',
              ]"
            >
              <span>{{ dia.diaLabel }}</span>
              <span class="text-sm font-bold">{{ dia.numero }}</span>
            </button>
          </div>

          <div v-if="loadingSlots" class="text-sm text-slate-500 text-center py-6">Buscando horarios...</div>
          <div v-else-if="slots.length === 0" class="text-sm text-slate-500 text-center py-6">
            No hay horarios disponibles este día.
          </div>
          <div v-else class="grid grid-cols-3 gap-2 mb-4">
            <button
              v-for="slot in slots"
              :key="slot.inicio"
              @click="slotSeleccionado = slot"
              :class="[
                'py-2 rounded-lg border text-sm font-medium transition-colors',
                slotSeleccionado?.inicio === slot.inicio ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-700 hover:border-blue-300',
              ]"
            >
              {{ slot.hora_label }}
            </button>
          </div>

          <div v-if="slotSeleccionado" class="mb-4">
            <label class="text-xs font-medium text-slate-500 block mb-1">Motivo de la consulta (opcional)</label>
            <textarea
              v-model="motivo"
              rows="2"
              maxlength="140"
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm"
              placeholder="Ej. Dolor de cabeza persistente"
            ></textarea>
          </div>

          <button
            :disabled="!slotSeleccionado || agendando"
            @click="confirmarCita"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 text-white font-semibold py-3 rounded-xl transition-colors"
          >
            {{ agendando ? 'Agendando...' : 'Confirmar cita' }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="text-center py-20 text-slate-500">Cargando perfil del médico...</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../../../axios';
import Swal from 'sweetalert2';

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
});

const route = useRoute();
const router = useRouter();
const medicoId = route.params.id;

const medico = ref(null);
const especialidadSeleccionada = ref('');
const proximosDias = ref([]);
const fechaSeleccionada = ref('');
const slots = ref([]);
const loadingSlots = ref(false);
const slotSeleccionado = ref(null);
const motivo = ref('');
const agendando = ref(false);

const emailName = computed(() => medico.value?.usuario?.email?.split('@')[0] || `Médico #${medicoId}`);
const initials = computed(() => emailName.value.slice(0, 2).toUpperCase());

const construirProximosDias = () => {
  const dias = [];
  const formatoDia = new Intl.DateTimeFormat('es-PE', { weekday: 'short' });
  for (let i = 0; i < 14; i++) {
    const fecha = new Date();
    fecha.setDate(fecha.getDate() + i);
    const value = fecha.toISOString().slice(0, 10);
    dias.push({
      value,
      diaLabel: formatoDia.format(fecha).replace('.', ''),
      numero: fecha.getDate(),
    });
  }
  proximosDias.value = dias;
};

const fetchMedico = async () => {
  try {
    const response = await axios.get(`/paciente/medicos/${medicoId}`);
    medico.value = response.data;
    especialidadSeleccionada.value = response.data.especialidades[0]?.id || '';
  } catch (error) {
    console.error('Error cargando médico:', error);
    Toast.fire({ icon: 'error', title: 'No se pudo cargar el perfil del médico' });
  }
};

const fetchSlots = async (fecha) => {
  loadingSlots.value = true;
  slotSeleccionado.value = null;
  try {
    const response = await axios.get(`/paciente/medicos/${medicoId}/disponibilidad`, { params: { fecha } });
    slots.value = response.data.slots;
  } catch (error) {
    console.error('Error cargando disponibilidad:', error);
    slots.value = [];
  } finally {
    loadingSlots.value = false;
  }
};

const seleccionarFecha = (fecha) => {
  fechaSeleccionada.value = fecha;
  fetchSlots(fecha);
};

const confirmarCita = async () => {
  agendando.value = true;
  try {
    await axios.post('/paciente/citas', {
      medico_id: medicoId,
      especialidad_id: especialidadSeleccionada.value,
      starts_at: slotSeleccionado.value.inicio,
      ends_at: slotSeleccionado.value.fin,
      motivo: motivo.value,
    });

    await Swal.fire({
      icon: 'success',
      title: 'Cita agendada',
      text: 'Tu cita ha sido reservada correctamente.',
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'Ver mis citas',
    });

    router.push('/paciente/citas');
  } catch (error) {
    console.error('Error agendando cita:', error);
    Toast.fire({ icon: 'error', title: error.response?.data?.message || 'Error al agendar la cita' });
    if (fechaSeleccionada.value) fetchSlots(fechaSeleccionada.value);
  } finally {
    agendando.value = false;
  }
};

onMounted(async () => {
  construirProximosDias();
  await fetchMedico();
  seleccionarFecha(proximosDias.value[0].value);
});
</script>
