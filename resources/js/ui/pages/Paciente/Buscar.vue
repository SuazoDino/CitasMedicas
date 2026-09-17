<template>
  <div>
    <!-- Hero de búsqueda -->
    <section class="bg-gradient-to-b from-blue-600 to-blue-700 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <h1 class="text-3xl sm:text-4xl font-bold mb-2">Encuentra al médico ideal</h1>
        <p class="text-blue-100 mb-8">Busca por especialidad y agenda tu cita en minutos.</p>

        <div class="bg-white rounded-2xl shadow-xl p-2.5 flex flex-col sm:flex-row gap-2">
          <div class="flex-1 flex items-center gap-2 px-3">
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <input
              v-model="filters.search"
              @keyup.enter="buscar"
              type="text"
              placeholder="Nombre del médico o especialidad..."
              class="w-full py-3 outline-none text-slate-800 placeholder-slate-400"
            />
          </div>
          <div class="sm:w-56 border-t sm:border-t-0 sm:border-l border-slate-200 flex items-center gap-2 px-3 relative">
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            <select
              v-model="filters.especialidad_id"
              @change="buscar"
              style="color-scheme: light"
              class="w-full py-3 pr-6 outline-none text-slate-800 bg-white appearance-none cursor-pointer"
            >
              <option value="" class="bg-white text-slate-800">Todas las especialidades</option>
              <option v-for="esp in especialidades" :key="esp.id" :value="esp.id" class="bg-white text-slate-800">
                {{ esp.nombre }}
              </option>
            </select>
            <svg class="w-4 h-4 text-slate-400 shrink-0 absolute right-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <button
            @click="buscar"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors"
          >
            Buscar
          </button>
        </div>
      </div>
    </section>

    <!-- Resultados -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
      <h2 class="text-lg font-semibold text-slate-800 mb-4">
        {{ loading ? 'Buscando médicos...' : `${pagination.total} médico(s) encontrado(s)` }}
      </h2>

      <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="n in 6" :key="n" class="bg-white rounded-2xl border border-slate-100 p-5 animate-pulse h-44"></div>
      </div>

      <div v-else-if="medicos.length === 0" class="text-center py-16 text-slate-500">
        No encontramos médicos con esos filtros. Intenta con otra especialidad.
      </div>

      <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <router-link
          v-for="medico in medicos"
          :key="medico.id"
          :to="`/paciente/medicos/${medico.id}`"
          class="bg-white rounded-2xl border border-slate-100 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all block"
        >
          <div class="flex items-start gap-4">
            <div class="h-14 w-14 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg shrink-0">
              {{ initials(medico) }}
            </div>
            <div class="min-w-0">
              <p class="font-semibold text-slate-900 truncate">Dr(a). {{ emailName(medico) }}</p>
              <div class="flex flex-wrap gap-1 mt-1">
                <span
                  v-for="esp in medico.especialidades"
                  :key="esp.id"
                  class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full"
                >
                  {{ esp.nombre }}
                </span>
              </div>
              <div class="flex items-center gap-1 mt-2 text-amber-500 text-sm">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                  <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                </svg>
                <span class="font-medium text-slate-700">
                  {{ medico.calificacion_promedio ? Number(medico.calificacion_promedio).toFixed(1) : 'Nuevo' }}
                </span>
                <span class="text-slate-400" v-if="medico.resenas_count">({{ medico.resenas_count }} reseñas)</span>
              </div>
            </div>
          </div>
          <button class="mt-4 w-full bg-slate-900 text-white text-sm font-medium py-2 rounded-lg hover:bg-slate-800 transition-colors">
            Ver perfil y horarios
          </button>
        </router-link>
      </div>

      <div v-if="pagination.last_page > 1" class="flex justify-center items-center gap-4 mt-8">
        <button
          :disabled="pagination.current_page === 1"
          @click="fetchMedicos(pagination.current_page - 1)"
          class="px-4 py-2 rounded-lg border border-slate-200 disabled:opacity-40 text-sm font-medium"
        >
          Anterior
        </button>
        <span class="text-sm text-slate-500">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
        <button
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchMedicos(pagination.current_page + 1)"
          class="px-4 py-2 rounded-lg border border-slate-200 disabled:opacity-40 text-sm font-medium"
        >
          Siguiente
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../../axios';

const especialidades = ref([]);
const medicos = ref([]);
const loading = ref(true);

const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const filters = ref({ search: '', especialidad_id: '' });

const emailName = (medico) => medico.usuario?.email?.split('@')[0] || `Médico #${medico.id}`;
const initials = (medico) => emailName(medico).slice(0, 2).toUpperCase();

const fetchEspecialidades = async () => {
  try {
    const response = await axios.get('/paciente/especialidades');
    especialidades.value = response.data;
  } catch (error) {
    console.error('Error cargando especialidades:', error);
  }
};

const fetchMedicos = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/paciente/medicos', {
      params: { page, search: filters.value.search, especialidad_id: filters.value.especialidad_id },
    });
    medicos.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total,
    };
  } catch (error) {
    console.error('Error cargando médicos:', error);
  } finally {
    loading.value = false;
  }
};

const buscar = () => fetchMedicos(1);

onMounted(() => {
  fetchEspecialidades();
  fetchMedicos();
});
</script>
