<template>
  <teleport to="body">
    <div v-if="visible" class="reprogram-modal__overlay" @click.self="emitClose">
      <div class="reprogram-modal">
        <header class="reprogram-modal__header">
          <h2>Reprogramar cita</h2>
          <button type="button" class="reprogram-modal__close" @click="emitClose" aria-label="Cerrar">×</button>
        </header>
        <section class="reprogram-modal__body">
          <p class="reprogram-modal__helper">
            Selecciona uno de los horarios disponibles para reagendar tu cita.
          </p>
          <div v-if="fetching" class="reprogram-modal__loading">
            <span class="spinner"></span>
            <span>Buscando horarios disponibles...</span>
          </div>
          <p v-else-if="error" class="reprogram-modal__error">{{ error }}</p>
          <div v-else-if="availableSlots.length" class="reprogram-modal__slots">
            <button
              v-for="slot in availableSlots"
              :key="slot.start"
              type="button"
              class="reprogram-modal__slot"
              :class="{ 'reprogram-modal__slot--current': isCurrent(slot), 'reprogram-modal__slot--disabled': loading }"
              :disabled="loading"
              @click="selectSlot(slot)"
            >
              <strong>{{ formatDate(slot.start) }}</strong>
              <small>{{ formatTimeRange(slot.start, slot.end) }}</small>
            </button>
          </div>
          <p v-else class="reprogram-modal__empty">
            No encontramos horarios disponibles para los próximos días. Intenta más tarde o comunícate con tu médico.
          </p>
        </section>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import api from '@/services/api'

const props = defineProps({
  visible: { type: Boolean, default: false },
  medicoId: { type: [Number, String], default: null },
  currentStart: { type: String, default: null },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'select'])

const slots = ref([])
const fetching = ref(false)
const error = ref('')

const availableSlots = computed(() => slots.value.filter((slot) => !slot.taken))
const normalizedCurrent = computed(() => {
  if (!props.currentStart) return null
  try {
    return new Date(props.currentStart).toISOString()
  } catch (error) {
    return null
  }
})

watch(
  () => [props.visible, props.medicoId],
  ([visible, medicoId]) => {
    if (visible && medicoId) {
      loadSlots(medicoId)
    }
    if (!visible) {
      slots.value = []
      error.value = ''
    }
  },
  { immediate: false }
)

async function loadSlots(medicoId) {
  fetching.value = true
  error.value = ''
  try {
    const { data } = await api.get(`/public/medicos/${medicoId}/slots`)
    slots.value = Array.isArray(data) ? data : []
  } catch (err) {
    error.value = err?.response?.data?.message ?? 'No pudimos cargar los horarios disponibles.'
    slots.value = []
  } finally {
    fetching.value = false
  }
}

function emitClose() {
  if (props.loading) return
  emit('close')
}

function selectSlot(slot) {
  if (props.loading) return
  emit('select', slot)
}

function isCurrent(slot) {
  if (!normalizedCurrent.value) return false
  try {
    return new Date(slot.start).toISOString() === normalizedCurrent.value
  } catch (error) {
    return false
  }
}

function formatDate(value) {
  const date = new Date(value)
  return date.toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  })
}

function formatTimeRange(start, end) {
  const from = new Date(start)
  const to = new Date(end)
  const localeOptions = { hour: '2-digit', minute: '2-digit' }
  return `${from.toLocaleTimeString([], localeOptions)} - ${to.toLocaleTimeString([], localeOptions)}`
}
</script>

<style scoped>
.reprogram-modal__overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 1, 24, 0.65);
  backdrop-filter: blur(4px);
  display: grid;
  place-items: center;
  padding: 24px;
  z-index: 1000;
}

.reprogram-modal {
  width: min(520px, 100%);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 24px 48px rgba(10, 1, 24, 0.28);
  overflow: hidden;
  color: #1f1f1f;
}

.reprogram-modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  background: linear-gradient(135deg, #623cea, #25ced1);
  color: #fff;
}

.reprogram-modal__close {
  background: transparent;
  border: none;
  color: inherit;
  font-size: 24px;
  cursor: pointer;
  line-height: 1;
}

.reprogram-modal__body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.reprogram-modal__helper {
  margin: 0;
  color: #4a4a4a;
  font-size: 15px;
}

.reprogram-modal__loading,
.reprogram-modal__empty,
.reprogram-modal__error {
  display: grid;
  gap: 8px;
  justify-items: center;
  text-align: center;
  color: #4a4a4a;
}

.reprogram-modal__error {
  color: #c0392b;
}

.reprogram-modal__slots {
  display: grid;
  gap: 12px;
  max-height: 320px;
  overflow-y: auto;
  padding-right: 4px;
}

.reprogram-modal__slot {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  padding: 12px 16px;
  border-radius: 12px;
  border: 1px solid rgba(98, 60, 234, 0.2);
  background: rgba(98, 60, 234, 0.08);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.reprogram-modal__slot strong {
  font-size: 15px;
  color: #222;
}

.reprogram-modal__slot small {
  font-size: 13px;
  color: #5b5b5b;
}

.reprogram-modal__slot:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(98, 60, 234, 0.18);
}

.reprogram-modal__slot--current {
  border-color: rgba(37, 206, 209, 0.8);
  background: rgba(37, 206, 209, 0.1);
}

.reprogram-modal__slot--disabled,
.reprogram-modal__slot--disabled:hover {
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
  opacity: 0.6;
}

.spinner {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid rgba(98, 60, 234, 0.2);
  border-top-color: #623cea;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>