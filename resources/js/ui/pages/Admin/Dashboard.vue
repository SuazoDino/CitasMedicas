<template>
  <div class="space-y-6">

    <!-- Loading Skeleton -->
    <template v-if="loading">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="i in 4" :key="i" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 animate-pulse">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-gray-200"></div>
            <div class="space-y-2 flex-1">
              <div class="h-3 bg-gray-200 rounded w-24"></div>
              <div class="h-6 bg-gray-200 rounded w-16"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2 p-6 animate-pulse">
          <div class="h-5 bg-gray-200 rounded w-48 mb-6"></div>
          <div class="space-y-4">
            <div v-for="i in 3" :key="i" class="h-10 bg-gray-100 rounded"></div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 animate-pulse">
          <div class="h-5 bg-gray-200 rounded w-40 mb-6"></div>
          <div class="space-y-4">
            <div v-for="i in 3" :key="i" class="h-12 bg-gray-100 rounded"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- Real Content -->
    <template v-else>
      <!-- Header Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card: Total Pacientes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
          <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Total Pacientes</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_pacientes }}</h3>
          </div>
        </div>

        <!-- Card: Citas Hoy -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
          <div class="h-12 w-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Citas Hoy</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.citas_hoy }}</h3>
          </div>
        </div>

        <!-- Card: Médicos Activos -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
          <div class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Médicos Activos</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.medicos_activos }}</h3>
          </div>
        </div>

        <!-- Card: Total Ingresos -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
          <div class="h-12 w-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Total Ingresos</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">S/ {{ stats.total_ingresos }}</h3>
          </div>
        </div>

      </div>

      <!-- Main Content Area -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Últimas Citas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
          <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Últimas Citas Reservadas</h2>
          </div>
          <div class="p-6">
            <!-- Empty state -->
            <div v-if="recentAppointments.length === 0" class="text-center py-10">
              <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              <p class="text-gray-400 text-sm">No hay citas registradas aún.</p>
            </div>
            <!-- Table -->
            <div v-else class="overflow-x-auto">
              <table class="w-full text-left text-sm text-gray-500">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50/50">
                  <tr>
                    <th scope="col" class="px-4 py-3 rounded-l-lg">Paciente</th>
                    <th scope="col" class="px-4 py-3">Médico</th>
                    <th scope="col" class="px-4 py-3">Fecha/Hora</th>
                    <th scope="col" class="px-4 py-3 rounded-r-lg">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="cita in recentAppointments"
                    :key="cita.id"
                    class="border-b border-gray-50 last:border-0 hover:bg-gray-50/30 transition-colors"
                  >
                    <td class="px-4 py-4 font-medium text-gray-900">{{ cita.paciente_nombre }}</td>
                    <td class="px-4 py-4">{{ cita.profesional_nombre }}</td>
                    <td class="px-4 py-4">{{ cita.fecha_hora }}</td>
                    <td class="px-4 py-4">
                      <span
                        class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium"
                        :class="estadoClass(cita.estado)"
                      >
                        <span class="h-1.5 w-1.5 rounded-full" :class="estadoDotClass(cita.estado)"></span>
                        {{ estadoLabel(cita.estado) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col">
          <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Actividad Reciente</h2>
          </div>
          <div class="p-6 flex-1 flex flex-col gap-6">
            <!-- Empty state -->
            <div v-if="recentActivity.length === 0" class="text-center py-10">
              <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <p class="text-gray-400 text-sm">Sin actividad reciente.</p>
            </div>
            <!-- Activity items -->
            <div v-for="log in recentActivity" :key="log.id" class="flex gap-4">
              <div class="h-8 w-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ log.accion }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Tabla: {{ log.tabla_afectada }} — {{ log.admin_nombre }}</p>
                <span class="text-xs text-gray-400 mt-1 block">{{ log.fecha }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from '../../../axios';

export default {
  name: 'AdminDashboard',
  setup() {
    const loading = ref(true);
    const stats = ref({
      total_pacientes: 0,
      citas_hoy: 0,
      medicos_activos: 0,
      total_ingresos: '0.00',
    });
    const recentAppointments = ref([]);
    const recentActivity = ref([]);

    const fetchDashboardData = async () => {
      try {
        const response = await axios.get('/admin/dashboard');
        stats.value = response.data.stats;
        recentAppointments.value = response.data.recent_appointments;
        recentActivity.value = response.data.recent_activity;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        loading.value = false;
      }
    };

    const estadoClass = (estado) => {
      const map = {
        pendiente: 'bg-amber-50 text-amber-700',
        confirmada: 'bg-emerald-50 text-emerald-700',
        en_curso: 'bg-blue-50 text-blue-700',
        completada: 'bg-green-50 text-green-700',
        cancelada: 'bg-red-50 text-red-700',
        no_asistio: 'bg-gray-100 text-gray-600',
      };
      return map[estado] || 'bg-gray-100 text-gray-600';
    };

    const estadoDotClass = (estado) => {
      const map = {
        pendiente: 'bg-amber-500',
        confirmada: 'bg-emerald-500',
        en_curso: 'bg-blue-500',
        completada: 'bg-green-500',
        cancelada: 'bg-red-500',
        no_asistio: 'bg-gray-400',
      };
      return map[estado] || 'bg-gray-400';
    };

    const estadoLabel = (estado) => {
      const map = {
        pendiente: 'Pendiente',
        confirmada: 'Confirmada',
        en_curso: 'En Curso',
        completada: 'Completada',
        cancelada: 'Cancelada',
        no_asistio: 'No Asistió',
      };
      return map[estado] || estado;
    };

    onMounted(() => {
      fetchDashboardData();
    });

    return {
      loading,
      stats,
      recentAppointments,
      recentActivity,
      estadoClass,
      estadoDotClass,
      estadoLabel,
    };
  },
}
</script>
