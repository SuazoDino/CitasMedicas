<template>
  <section class="max-w-6xl mx-auto px-6 py-10 text-white">
    <h1 class="text-3xl font-black mb-1">🩺 Panel del Médico</h1>
    <p class="text-sm text-white/60 mb-6">
      Estado de verificación: <strong :class="badgeClass">{{ verifStatus }}</strong>
    </p>

    <div class="grid md:grid-cols-3 gap-6">
      <article class="p-6 rounded-2xl bg-white/5 border border-white/10 md:col-span-2">
        <h3 class="font-semibold mb-3">Agenda de hoy</h3>
        <ul class="text-white/70 text-sm space-y-2">
          <li v-for="a in agenda" :key="a.id"><strong>{{ a.hora }}</strong> — {{ a.paciente }}</li>
          <li v-if="!agenda.length" class="italic text-white/50">Sin citas para hoy</li>
        </ul>
      </article>

      <article class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold mb-3">Acciones</h3>
        <div class="flex flex-col gap-3">
          <button class="px-4 py-2 rounded-full bg-gradient-to-r from-pink-500 to-violet-600">
            Configurar horarios
          </button>
          <button class="px-4 py-2 rounded-full bg-white/10 border border-white/10">
            Editar perfil
          </button>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const verifStatus = ref('pendiente')
const agenda = ref([])

const badgeClass = computed(() => ({
  'text-yellow-300': verifStatus.value === 'pendiente',
  'text-green-300': verifStatus.value === 'verificado',
  'text-red-300': verifStatus.value === 'rechazado',
}))

onMounted(async () => {
  try {
    const me = await axios.get('/api/auth/me')
    verifStatus.value = me.data?.user?.verif_status ?? 'pendiente'
  } catch {}
  try {
    const { data } = await axios.get('/api/doctor/agenda/today')
    agenda.value = data ?? []
  } catch {
    agenda.value = []
  }
})
</script>
