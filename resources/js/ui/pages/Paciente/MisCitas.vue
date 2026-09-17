<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Mis citas</h1>

    <div class="flex gap-1 mb-6 border-b border-slate-200">
      <button
        @click="cambiarTab('proximas')"
        :class="[
          'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors',
          tab === 'proximas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700',
        ]"
      >
        Próximas
      </button>
      <button
        @click="cambiarTab('pasadas')"
        :class="[
          'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors',
          tab === 'pasadas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700',
        ]"
      >
        Historial
      </button>
    </div>

    <div v-if="loading" class="text-center py-16 text-slate-500">Cargando citas...</div>

    <div v-else-if="citas.length === 0" class="text-center py-16 text-slate-500">
      <p>{{ tab === 'proximas' ? 'No tienes citas próximas.' : 'Aún no tienes citas pasadas.' }}</p>
      <router-link v-if="tab === 'proximas'" to="/paciente/buscar" class="text-blue-600 font-medium hover:underline mt-2 inline-block">
        Buscar un médico
      </router-link>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="cita in citas"
        :key="cita.id"
        class="bg-white rounded-2xl border border-slate-100 p-5 flex flex-col sm:flex-row sm:items-center gap-4"
      >
        <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold shrink-0">
          {{ emailName(cita).slice(0, 2).toUpperCase() }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-slate-900">Dr(a). {{ emailName(cita) }}</p>
          <p class="text-sm text-slate-500">{{ cita.especialidad?.nombre }}</p>
          <p class="text-sm text-slate-700 mt-1">{{ formatFecha(cita.starts_at) }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="cita-badge" :class="'status-' + cita.estado">{{ estadoLabel(cita.estado) }}</span>
          <button
            v-if="tab === 'proximas' && !['cancelada', 'completada'].includes(cita.estado)"
            @click="cancelar(cita.id)"
            class="text-sm font-medium text-red-600 hover:underline"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../../axios';
import Swal from 'sweetalert2';

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
});

const ESTADOS = {
  pendiente: 'Pendiente',
  confirmada: 'Confirmada',
  completada: 'Completada',
  cancelada: 'Cancelada',
  no_asistio: 'No asistió',
};

const tab = ref('proximas');
const citas = ref([]);
const loading = ref(true);

const estadoLabel = (estado) => ESTADOS[estado] || estado;
const emailName = (cita) => cita.medico?.usuario?.email?.split('@')[0] || 'Médico';

const formatFecha = (value) => {
  if (!value) return '—';
  return new Date(value).toLocaleString('es-PE', { dateStyle: 'full', timeStyle: 'short' });
};

const fetchCitas = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/paciente/citas', { params: { periodo: tab.value } });
    citas.value = response.data;
  } catch (error) {
    console.error('Error cargando citas:', error);
    Toast.fire({ icon: 'error', title: 'Error al cargar tus citas' });
  } finally {
    loading.value = false;
  }
};

const cambiarTab = (nuevoTab) => {
  tab.value = nuevoTab;
  fetchCitas();
};

const cancelar = async (id) => {
  const result = await Swal.fire({
    title: '¿Cancelar esta cita?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    input: 'text',
    inputPlaceholder: 'Motivo (opcional)',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Sí, cancelar',
    cancelButtonText: 'Volver',
  });

  if (result.isConfirmed) {
    try {
      await axios.patch(`/paciente/citas/${id}/cancelar`, { cancel_reason: result.value || undefined });
      Toast.fire({ icon: 'success', title: 'Cita cancelada' });
      fetchCitas();
    } catch (error) {
      console.error('Error cancelando cita:', error);
      Toast.fire({ icon: 'error', title: error.response?.data?.message || 'Error al cancelar la cita' });
    }
  }
};

onMounted(fetchCitas);
</script>

<style scoped>
.cita-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  display: inline-block;
}

.status-pendiente { background-color: #fef3c7; color: #b45309; }
.status-confirmada { background-color: #dbeafe; color: #1d4ed8; }
.status-completada { background-color: #dcfce3; color: #15803d; }
.status-cancelada { background-color: #fee2e2; color: #b91c1c; }
.status-no_asistio { background-color: #f3f4f6; color: #374151; }
</style>
