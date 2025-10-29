<template>
  <section class="max-w-6xl mx-auto px-6 py-10 text-white">
    <h1 class="text-3xl font-black mb-6">👤 Panel del Paciente</h1>

    <div class="grid md:grid-cols-3 gap-6">
      <article class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold mb-2">Próximas citas</h3>
        <ul class="text-white/70 text-sm space-y-2">
          <li v-for="c in citas" :key="c.id">
            <strong>{{ c.fecha }} {{ c.hora }}</strong> • {{ c.medico }}
          </li>
          <li v-if="!citas.length" class="italic text-white/50">No tienes citas próximas</li>
        </ul>
      </article>

      <article class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold mb-2">Historial rápido</h3>
        <p class="text-white/70 text-sm">Aquí podrás ver tus atenciones anteriores.</p>
      </article>

      <article class="p-6 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="font-semibold mb-2">Acciones</h3>
        <div class="flex flex-col gap-3">
          <button class="px-4 py-2 rounded-full bg-gradient-to-r from-pink-500 to-violet-600">
            Reservar nueva cita
          </button>
          <button class="px-4 py-2 rounded-full bg-white/10 border border-white/10">
            Ver mi perfil
          </button>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const citas = ref([])

onMounted(async () => {
  // dummy por ahora; cuando tengas endpoint real, cambia el GET
  try {
    const { data } = await axios.get('/api/patient/appointments/upcoming')
    citas.value = data ?? []
  } catch {
    citas.value = []
  }
})
</script>
