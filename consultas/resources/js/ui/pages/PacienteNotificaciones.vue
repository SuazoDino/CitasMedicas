<template>
  <section class="page-slot notificaciones-page">
    <div class="container">
      <header class="page-header">
        <div>
          <h1>Configuración de Notificaciones 🔔</h1>
          <p>Gestiona cómo y cuándo recibes notificaciones sobre tus citas médicas.</p>
        </div>
        <button class="btn-soft" @click="volver">← Volver</button>
      </header>

      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Cargando configuración...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <span class="error-icon">⚠️</span>
        <h2>Error al cargar la configuración</h2>
        <p>{{ error }}</p>
        <button class="btn-primary" @click="cargarPreferencias">Reintentar</button>
      </div>

      <div v-else class="config-content">
        <!-- Email -->
        <div class="config-section">
          <div class="section-header">
            <div class="section-icon">📧</div>
            <div class="section-info">
              <h2>Notificaciones por Email</h2>
              <p>Recibe recordatorios y actualizaciones por correo electrónico</p>
            </div>
            <label class="toggle-switch">
              <input
                type="checkbox"
                v-model="preferencias.email_opt_in"
                @change="guardarPreferencias"
              />
              <span class="toggle-slider"></span>
            </label>
          </div>
          <div class="section-details">
            <p class="detail-text">
              Recibirás emails con recordatorios de citas próximas, confirmaciones y cambios en tus citas.
            </p>
            <div class="detail-info">
              <span class="info-icon">📧</span>
              <span>{{ userEmail }}</span>
            </div>
          </div>
        </div>

        <!-- SMS -->
        <div class="config-section">
          <div class="section-header">
            <div class="section-icon">📱</div>
            <div class="section-info">
              <h2>Notificaciones por SMS</h2>
              <p>Recibe recordatorios por mensaje de texto</p>
            </div>
            <label class="toggle-switch">
              <input
                type="checkbox"
                v-model="preferencias.sms_opt_in"
                @change="guardarPreferencias"
              />
              <span class="toggle-slider"></span>
            </label>
          </div>
          <div class="section-details">
            <p class="detail-text">
              Recibirás mensajes de texto con recordatorios importantes sobre tus citas.
            </p>
            <div class="detail-input">
              <label class="input-label">Número de teléfono para SMS</label>
              <input
                type="tel"
                v-model="preferencias.sms_number"
                @blur="guardarPreferencias"
                placeholder="+51999999999"
                class="form-input"
                :disabled="!preferencias.sms_opt_in"
              />
              <span class="input-hint">
                {{ preferencias.sms_opt_in 
                  ? 'Ingresa tu número de teléfono con código de país' 
                  : 'Activa SMS para configurar tu número' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Push (Futuro) -->
        <div class="config-section disabled">
          <div class="section-header">
            <div class="section-icon">🔔</div>
            <div class="section-info">
              <h2>Notificaciones Push</h2>
              <p>Recibe notificaciones en tiempo real en tu dispositivo</p>
              <span class="coming-soon">Próximamente</span>
            </div>
            <label class="toggle-switch disabled">
              <input
                type="checkbox"
                v-model="preferencias.push_opt_in"
                disabled
              />
              <span class="toggle-slider"></span>
            </label>
          </div>
          <div class="section-details">
            <p class="detail-text">
              Las notificaciones push estarán disponibles próximamente en la aplicación móvil.
            </p>
          </div>
        </div>

        <!-- Resumen -->
        <div class="summary-section">
          <h3>📋 Resumen de Preferencias</h3>
          <div class="summary-list">
            <div class="summary-item">
              <span class="summary-icon">📧</span>
              <span class="summary-label">Email:</span>
              <span class="summary-value" :class="{ active: preferencias.email_opt_in }">
                {{ preferencias.email_opt_in ? 'Activado' : 'Desactivado' }}
              </span>
            </div>
            <div class="summary-item">
              <span class="summary-icon">📱</span>
              <span class="summary-label">SMS:</span>
              <span class="summary-value" :class="{ active: preferencias.sms_opt_in }">
                {{ preferencias.sms_opt_in ? 'Activado' : 'Desactivado' }}
              </span>
              <span v-if="preferencias.sms_opt_in && preferencias.sms_number" class="summary-phone">
                ({{ preferencias.sms_number }})
              </span>
            </div>
            <div class="summary-item">
              <span class="summary-icon">🔔</span>
              <span class="summary-label">Push:</span>
              <span class="summary-value disabled">No disponible</span>
            </div>
          </div>
        </div>

        <div v-if="successMessage" class="success-message">
          <span class="message-icon">✅</span>
          <span>{{ successMessage }}</span>
        </div>
        <div v-if="errorMessage" class="error-message">
          <span class="message-icon">⚠️</span>
          <span>{{ errorMessage }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

const loading = ref(true)
const error = ref(null)
const preferencias = ref({
  email_opt_in: true,
  sms_opt_in: false,
  sms_number: '',
  push_opt_in: false,
})
const userEmail = ref('')
const successMessage = ref('')
const errorMessage = ref('')
const saving = ref(false)

onMounted(() => {
  cargarPreferencias()
})

async function cargarPreferencias() {
  loading.value = true
  error.value = null

  try {
    const [prefsRes, userRes] = await Promise.all([
      api.get('/paciente/notificaciones/preferencias'),
      api.get('/auth/me'),
    ])

    preferencias.value = {
      email_opt_in: prefsRes.data.email_opt_in ?? true,
      sms_opt_in: prefsRes.data.sms_opt_in ?? false,
      sms_number: prefsRes.data.sms_number || '',
      push_opt_in: prefsRes.data.push_opt_in ?? false,
    }

    userEmail.value = userRes.data?.user?.email || userRes.data?.email || ''
  } catch (err) {
    console.error('Error al cargar preferencias:', err)
    error.value = err?.response?.data?.message || 'No se pudieron cargar las preferencias'
  } finally {
    loading.value = false
  }
}

async function guardarPreferencias() {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.put('/paciente/notificaciones/preferencias', preferencias.value)
    successMessage.value = 'Preferencias guardadas correctamente'
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    console.error('Error al guardar preferencias:', err)
    errorMessage.value = err?.response?.data?.message || 'No se pudieron guardar las preferencias'
    setTimeout(() => {
      errorMessage.value = ''
    }, 5000)
  } finally {
    saving.value = false
  }
}

function volver() {
  router.push({ name: 'paciente.home' })
}
</script>

<style scoped>
.notificaciones-page {
  padding: 40px 20px;
  min-height: 100vh;
}

.container {
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  gap: 20px;
}

.page-header h1 {
  font-size: 32px;
  font-weight: 700;
  background: linear-gradient(135deg, #00f5ff, #7f3bf3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 8px;
}

.page-header p {
  color: rgba(234,246,255,0.7);
  font-size: 16px;
}

.config-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.config-section {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 30px;
  transition: all 0.3s;
}

.config-section:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(127,59,243,0.3);
}

.config-section.disabled {
  opacity: 0.6;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.section-icon {
  font-size: 36px;
  flex-shrink: 0;
}

.section-info {
  flex: 1;
}

.section-info h2 {
  font-size: 22px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 6px;
}

.section-info p {
  font-size: 14px;
  color: rgba(234,246,255,0.6);
}

.coming-soon {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 8px;
  background: rgba(251,191,36,0.15);
  color: #fde68a;
  font-size: 12px;
  font-weight: 600;
  margin-top: 8px;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 56px;
  height: 32px;
  flex-shrink: 0;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255,255,255,0.1);
  transition: 0.3s;
  border-radius: 32px;
  border: 1px solid rgba(255,255,255,0.2);
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 24px;
  width: 24px;
  left: 4px;
  bottom: 3px;
  background-color: #fff;
  transition: 0.3s;
  border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
  background: linear-gradient(135deg, #7f3bf3, #ff2a88);
  border-color: transparent;
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

.toggle-switch.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.section-details {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.detail-text {
  font-size: 14px;
  color: rgba(234,246,255,0.7);
  margin-bottom: 16px;
}

.detail-info {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  background: rgba(255,255,255,0.03);
  border-radius: 10px;
  font-size: 14px;
  color: rgba(234,246,255,0.9);
}

.info-icon {
  font-size: 18px;
}

.detail-input {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-label {
  font-size: 13px;
  font-weight: 600;
  color: rgba(234,246,255,0.9);
}

.form-input {
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: #fff;
  font-size: 15px;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #7f3bf3;
  background: rgba(255,255,255,0.08);
}

.form-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.input-hint {
  font-size: 12px;
  color: rgba(234,246,255,0.5);
}

.summary-section {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 30px;
  margin-top: 20px;
}

.summary-section h3 {
  font-size: 20px;
  font-weight: 700;
  color: rgba(234,246,255,0.98);
  margin-bottom: 20px;
}

.summary-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255,255,255,0.03);
  border-radius: 10px;
}

.summary-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.summary-label {
  font-size: 15px;
  font-weight: 600;
  color: rgba(234,246,255,0.9);
  min-width: 80px;
}

.summary-value {
  font-size: 14px;
  color: rgba(234,246,255,0.6);
  font-weight: 600;
}

.summary-value.active {
  color: #86efac;
}

.summary-value.disabled {
  color: rgba(234,246,255,0.4);
}

.summary-phone {
  font-size: 13px;
  color: rgba(234,246,255,0.5);
  margin-left: auto;
}

.success-message,
.error-message {
  padding: 16px 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 600;
  margin-top: 20px;
}

.success-message {
  background: rgba(34,197,94,0.15);
  border: 1px solid rgba(34,197,94,0.3);
  color: #86efac;
}

.error-message {
  background: rgba(239,68,68,0.15);
  border: 1px solid rgba(239,68,68,0.3);
  color: #fca5a5;
}

.message-icon {
  font-size: 20px;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(127,59,243,0.2);
  border-top-color: #7f3bf3;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 20px;
  opacity: 0.5;
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #7f3bf3, #ff2a88);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(127,59,243,0.4);
}

.btn-soft {
  padding: 10px 20px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: rgba(234,246,255,0.9);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-soft:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.2);
}
</style>