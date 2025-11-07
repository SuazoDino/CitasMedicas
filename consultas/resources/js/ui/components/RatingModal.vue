<template>
  <div v-if="visible" class="modal-overlay" @click="cerrar">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Calificar Cita ⭐</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>
      <div class="modal-body">
        <div class="cita-info">
          <div class="medico-info">
            <span class="medico-icon">👨‍⚕️</span>
            <div>
              <h3>{{ cita?.medico_nombre || 'Médico' }}</h3>
              <p>{{ cita?.especialidad_nombre || 'Especialidad' }}</p>
            </div>
          </div>
          <div class="fecha-info">
            <span class="fecha-icon">📅</span>
            <span>{{ cita?.fecha_completa || '' }}</span>
          </div>
        </div>

        <div class="rating-section">
          <label class="rating-label">¿Cómo calificarías esta atención?</label>
          <div class="stars-container">
            <button
              v-for="star in 5"
              :key="star"
              class="star-btn"
              :class="{ active: star <= rating, hover: star <= hoverRating }"
              @click="seleccionarRating(star)"
              @mouseenter="hoverRating = star"
              @mouseleave="hoverRating = 0"
            >
              <span class="star-icon">{{ star <= rating ? '⭐' : '☆' }}</span>
            </button>
          </div>
          <div class="rating-text">
            <span v-if="rating === 0">Selecciona una calificación</span>
            <span v-else-if="rating === 1">Muy mala</span>
            <span v-else-if="rating === 2">Mala</span>
            <span v-else-if="rating === 3">Regular</span>
            <span v-else-if="rating === 4">Buena</span>
            <span v-else-if="rating === 5">Excelente</span>
          </div>
        </div>

        <div class="review-section">
          <label class="review-label">
            <span class="label-icon">💬</span>
            Comentario (opcional)
          </label>
          <textarea
            v-model="review"
            placeholder="Comparte tu experiencia con este médico..."
            class="review-input"
            rows="4"
            maxlength="500"
          ></textarea>
          <div class="char-count">{{ review.length }}/500</div>
        </div>

        <div v-if="error" class="error-message">
          <span class="error-icon">⚠️</span>
          <span>{{ error }}</span>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="cerrar" :disabled="guardando">
            Cancelar
          </button>
          <button
            class="btn-submit"
            @click="guardar"
            :disabled="guardando || rating === 0"
          >
            <span v-if="guardando" class="btn-spinner">⏳</span>
            <span v-else>{{ esEdicion ? 'Actualizar' : 'Enviar' }} Calificación</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '../../services/api'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  cita: {
    type: Object,
    default: null,
  },
  existingRating: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const rating = ref(0)
const hoverRating = ref(0)
const review = ref('')
const guardando = ref(false)
const error = ref('')
const esEdicion = ref(false)

watch(() => props.visible, (newVal) => {
  if (newVal) {
    if (props.existingRating) {
      rating.value = props.existingRating.rating || 0
      review.value = props.existingRating.review || ''
      esEdicion.value = true
    } else {
      rating.value = 0
      review.value = ''
      esEdicion.value = false
    }
    error.value = ''
    hoverRating.value = 0
  }
})

function seleccionarRating(value) {
  rating.value = value
}

async function guardar() {
  if (rating.value === 0) {
    error.value = 'Por favor selecciona una calificación'
    return
  }

  guardando.value = true
  error.value = ''

  try {
    const endpoint = `/paciente/citas/${props.cita.id}/rating`
    const method = esEdicion.value ? 'put' : 'post'
    
    await api[method](endpoint, {
      rating: rating.value,
      review: review.value.trim() || null,
    })

    emit('saved', {
      rating: rating.value,
      review: review.value.trim() || null,
    })
    cerrar()
  } catch (err) {
    console.error('Error al guardar calificación:', err)
    error.value = err?.response?.data?.message || 'No se pudo guardar la calificación'
  } finally {
    guardando.value = false
  }
}

function cerrar() {
  emit('close')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.8);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: rgba(20,20,35,0.98);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.modal-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.1);
  color: rgba(234,246,255,0.9);
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-close:hover {
  background: rgba(255,255,255,0.2);
}

.modal-body {
  padding: 24px;
}

.cita-info {
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255,255,255,0.03);
  border-radius: 12px;
}

.medico-info {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.medico-icon {
  font-size: 40px;
}

.medico-info h3 {
  font-size: 18px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 4px;
}

.medico-info p {
  font-size: 14px;
  color: rgba(234,246,255,0.6);
}

.fecha-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: rgba(234,246,255,0.7);
}

.fecha-icon {
  font-size: 18px;
}

.rating-section {
  margin-bottom: 30px;
}

.rating-label {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: rgba(234,246,255,0.98);
  margin-bottom: 16px;
}

.stars-container {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
  justify-content: center;
}

.star-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 8px;
  transition: all 0.2s;
  font-size: 40px;
  line-height: 1;
}

.star-btn:hover {
  transform: scale(1.1);
}

.star-btn.active .star-icon {
  filter: drop-shadow(0 0 8px rgba(255,215,0,0.6));
}

.star-btn.hover .star-icon {
  transform: scale(1.1);
}

.rating-text {
  text-align: center;
  font-size: 14px;
  color: rgba(234,246,255,0.7);
  font-weight: 600;
}

.review-section {
  margin-bottom: 24px;
}

.review-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: rgba(234,246,255,0.98);
  margin-bottom: 12px;
}

.label-icon {
  font-size: 18px;
}

.review-input {
  width: 100%;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: #fff;
  font-size: 15px;
  font-family: inherit;
  resize: vertical;
  transition: all 0.2s;
}

.review-input:focus {
  outline: none;
  border-color: #7f3bf3;
  background: rgba(255,255,255,0.08);
}

.char-count {
  text-align: right;
  font-size: 12px;
  color: rgba(234,246,255,0.5);
  margin-top: 4px;
}

.error-message {
  padding: 12px 16px;
  border-radius: 10px;
  background: rgba(239,68,68,0.15);
  border: 1px solid rgba(239,68,68,0.3);
  color: #fca5a5;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  margin-bottom: 20px;
}

.error-icon {
  font-size: 18px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-cancel,
.btn-submit {
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: rgba(255,255,255,0.1);
  color: rgba(234,246,255,0.9);
  border: 1px solid rgba(255,255,255,0.2);
}

.btn-cancel:hover:not(:disabled) {
  background: rgba(255,255,255,0.15);
}

.btn-submit {
  background: linear-gradient(135deg, #7f3bf3, #ff2a88);
  color: #fff;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127,59,243,0.4);
}

.btn-cancel:disabled,
.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-spinner {
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
