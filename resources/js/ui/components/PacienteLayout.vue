<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900">
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <router-link to="/paciente/buscar" class="flex items-center gap-1 text-xl font-bold text-slate-900 shrink-0">
          <span class="text-blue-600">medi</span>Reserva
        </router-link>

        <nav class="hidden md:flex items-center gap-1">
          <router-link
            to="/paciente/buscar"
            class="px-4 py-2 rounded-full text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
            active-class="!text-blue-600 !bg-blue-50"
          >
            Buscar médicos
          </router-link>
          <router-link
            to="/paciente/citas"
            class="px-4 py-2 rounded-full text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
            active-class="!text-blue-600 !bg-blue-50"
          >
            Mis citas
          </router-link>
        </nav>

        <div class="relative">
          <button
            @click="menuOpen = !menuOpen"
            class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full border border-slate-200 hover:bg-slate-50 transition-colors"
          >
            <span class="h-8 w-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold text-sm">
              {{ initials }}
            </span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div v-if="menuOpen" class="fixed inset-0 z-10" @click="menuOpen = false"></div>
          <div v-if="menuOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-20">
            <div class="px-4 py-2 border-b border-slate-100">
              <p class="text-sm font-medium text-slate-900 truncate">{{ user?.email }}</p>
              <p class="text-xs text-slate-500">Paciente</p>
            </div>
            <button @click="handleLogout" class="w-full text-left px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
              Cerrar sesión
            </button>
          </div>
        </div>
      </div>

      <nav class="md:hidden flex border-t border-slate-100">
        <router-link to="/paciente/buscar" class="flex-1 text-center py-2.5 text-sm font-medium text-slate-600" active-class="!text-blue-600">
          Buscar
        </router-link>
        <router-link to="/paciente/citas" class="flex-1 text-center py-2.5 text-sm font-medium text-slate-600" active-class="!text-blue-600">
          Mis citas
        </router-link>
      </nav>
    </header>

    <main>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../composables/useAuth';

const router = useRouter();
const { user, logout } = useAuth();
const menuOpen = ref(false);

const initials = computed(() => (user.value?.email ? user.value.email.slice(0, 2).toUpperCase() : 'P'));

const handleLogout = async () => {
  await logout();
  router.push('/auth/login');
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
