<template>
  <section class="page-slot perfil-page">
    <div class="container">
      <header class="page-header">
        <div>
          <h1>Mi Perfil 🩺</h1>
          <p>Gestiona tu información profesional y especialidades médicas.</p>
        </div>
        <button class="btn-soft" @click="volver">← Volver al panel</button>
      </header>

      <div v-if="pageError" class="alert error">{{ pageError }}</div>
      <div v-if="successMessage" class="alert success">{{ successMessage }}</div>

      <!-- Sección de Especialidades -->
      <div class="section-card">
        <h2>Mis Especialidades</h2>
        <p class="section-description">
          Selecciona las especialidades médicas en las que te desempeñas. Los pacientes podrán encontrarte por estas especialidades.
        </p>

        <!-- Especialidades actuales -->
        <div v-if="misEspecialidades.length" class="especialidades-actuales">
          <h3>Especialidades asignadas:</h3>
          <div class="especialidades-list">
            <div
              v-for="esp in misEspecialidades"
              :key="esp.id"
              class="especialidad-badge"
            >
              <span>{{ esp.nombre }}</span>
              <button
                class="btn-remove"
                @click="eliminarEspecialidad(esp.id)"
                :disabled="saving"
                title="Eliminar especialidad"
              >
                ×
              </button>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <p>No tienes especialidades asignadas aún.</p>
        </div>

        <!-- Selector de especialidades -->
        <div class="selector-section">
          <h3>Agregar especialidad:</h3>
          <div class="selector-wrapper">
            <select
              v-model="nuevaEspecialidadId"
              class="select-especialidad"
              :disabled="saving || cargando"
            >
              <option value="">Selecciona una especialidad...</option>
              <option
                v-for="esp in especialidadesDisponibles"
                :key="esp.id"
                :value="esp.id"
                :disabled="yaTieneEspecialidad(esp.id)"
              >
                {{ esp.nombre }}
                <span v-if="yaTieneEspecialidad(esp.id)"> (ya asignada)</span>
              </option>
            </select>
            <button
              class="btn-add"
              @click="agregarEspecialidad"
              :disabled="!nuevaEspecialidadId || saving || cargando"
            >
              + Agregar
            </button>
          </div>
        </div>

        <!-- Botón para actualizar todas a la vez -->
        <div class="bulk-update-section">
          <h3>Actualizar todas las especialidades:</h3>
          <p class="section-description">
            Selecciona múltiples especialidades y actualízalas todas a la vez.
          </p>
          <div class="checkboxes-grid">
            <label
              v-for="esp in especialidadesDisponibles"
              :key="esp.id"
              class="checkbox-item"
            >
              <input
                type="checkbox"
                :value="esp.id"
                v-model="especialidadesSeleccionadas"
                :disabled="saving || cargando"
              />
              <span>{{ esp.nombre }}</span>
            </label>
          </div>
          <button
            class="btn-primary"
            @click="actualizarTodasEspecialidades"
            :disabled="saving || cargando"
          >
            {{ saving ? 'Guardando...' : 'Actualizar especialidades' }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import api from '../../services/api'
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Estado
const cargando = ref(false)
const saving = ref(false)
const pageError = ref(null)
const successMessage = ref(null)
const especialidadesDisponibles = ref([])
const misEspecialidades = ref([])
const nuevaEspecialidadId = ref('')
const especialidadesSeleccionadas = ref([])

// La instancia 'api' ya maneja la autenticación automáticamente

// Computed
const yaTieneEspecialidad = (id) => {
  return misEspecialidades.value.some(esp => esp.id === id)
}

// Funciones
function volver() {
  router.push('/medico')
}

function mostrarError(mensaje) {
  pageError.value = mensaje
  successMessage.value = null
  setTimeout(() => { pageError.value = null }, 5000)
}

function mostrarExito(mensaje) {
  successMessage.value = mensaje
  pageError.value = null
  setTimeout(() => { successMessage.value = null }, 5000)
}

async function cargarEspecialidadesDisponibles() {
  try {
    const { data } = await api.get('/public/especialidades')
    especialidadesDisponibles.value = data || []
  } catch (error) {
    console.error('Error al cargar especialidades:', error)
    mostrarError('No se pudieron cargar las especialidades disponibles')
  }
}

async function cargarMisEspecialidades() {
  cargando.value = true
  try {
    const token = localStorage.getItem('token') || localStorage.getItem('auth_token') || sessionStorage.getItem('token')
    console.log('Token disponible:', token ? 'Sí' : 'No')
    console.log('Llamando a /medico/especialidades')
    
    const { data } = await api.get('/medico/especialidades')
    console.log('Respuesta recibida:', data)
    
    misEspecialidades.value = data?.especialidades || []
    // Sincronizar las seleccionadas con las actuales
    especialidadesSeleccionadas.value = misEspecialidades.value.map(esp => esp.id)
  } catch (error) {
    console.error('Error al cargar mis especialidades:', error)
    console.error('URL completa:', error?.config?.url)
    console.error('Status:', error?.response?.status)
    console.error('Mensaje:', error?.response?.data)
    mostrarError(error?.response?.data?.message || 'No se pudieron cargar tus especialidades')
  } finally {
    cargando.value = false
  }
}

async function agregarEspecialidad() {
  if (!nuevaEspecialidadId.value) return

  saving.value = true
  try {
    const { data } = await api.post('/medico/especialidades', {
      especialidad_id: nuevaEspecialidadId.value
    })
    
    // Actualizar directamente desde la respuesta en lugar de hacer otro GET
    if (data?.especialidades) {
      misEspecialidades.value = data.especialidades
      especialidadesSeleccionadas.value = data.especialidades.map(esp => esp.id)
    } else {
      // Si no vienen en la respuesta, recargar
      await cargarMisEspecialidades()
    }
    
    mostrarExito('Especialidad agregada correctamente')
    nuevaEspecialidadId.value = ''
  } catch (error) {
    console.error('Error al agregar especialidad:', error)
    const mensaje = error?.response?.data?.message || 'Error al agregar la especialidad'
    mostrarError(mensaje)
  } finally {
    saving.value = false
  }
}

async function eliminarEspecialidad(especialidadId) {
  if (!confirm('¿Estás seguro de que deseas eliminar esta especialidad?')) {
    return
  }

  saving.value = true
  try {
    await api.delete(`/medico/especialidades/${especialidadId}`)
    mostrarExito('Especialidad eliminada correctamente')
    await cargarMisEspecialidades()
  } catch (error) {
    const mensaje = error?.response?.data?.message || 'Error al eliminar la especialidad'
    mostrarError(mensaje)
  } finally {
    saving.value = false
  }
}

async function actualizarTodasEspecialidades() {
  saving.value = true
  try {
    await api.put('/medico/especialidades', {
      especialidades: especialidadesSeleccionadas.value
    })
    mostrarExito('Especialidades actualizadas correctamente')
    await cargarMisEspecialidades()
  } catch (error) {
    const mensaje = error?.response?.data?.message || 'Error al actualizar las especialidades'
    mostrarError(mensaje)
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    cargarEspecialidadesDisponibles(),
    cargarMisEspecialidades()
  ])
})
</script>

<style scoped>
.perfil-page {
  padding: 20px;
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0118 0%, #1a0a2e 100%);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
}

.page-header h1 {
  margin: 0 0 8px;
  font-size: 32px;
  font-weight: 900;
  background: linear-gradient(135deg, #ff2a88 0%, #7f3bf3 45%, #00e5ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.page-header p {
  margin: 0;
  color: rgba(255, 255, 255, 0.7);
}

.btn-soft {
  padding: 10px 16px;
  border-radius: 999px;
  color: #fff;
  background: rgba(255, 255, 255, 0.09);
  border: 1px solid rgba(255, 255, 255, 0.12);
  cursor: pointer;
  font-size: 14px;
}

.btn-soft:hover {
  background: rgba(255, 255, 255, 0.12);
}

.section-card {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.32);
}

.section-card h2 {
  margin: 0 0 8px;
  font-size: 24px;
  font-weight: 800;
  color: #fff;
}

.section-description {
  color: rgba(255, 255, 255, 0.7);
  margin: 0 0 20px;
  font-size: 14px;
}

.especialidades-actuales {
  margin-bottom: 30px;
}

.especialidades-actuales h3 {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 12px;
}

.especialidades-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.especialidad-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  border-radius: 999px;
  color: #fff;
  font-weight: 600;
  font-size: 14px;
}

.btn-remove {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.btn-remove:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
}

.btn-remove:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  padding: 20px;
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  margin-bottom: 30px;
}

.selector-section {
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
}

.selector-section h3 {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 12px;
}

.selector-wrapper {
  display: flex;
  gap: 10px;
  align-items: center;
}

.select-especialidad {
  flex: 1;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
  font-size: 14px;
  outline: none;
}

.select-especialidad:focus {
  border-color: #7f3bf3;
  box-shadow: 0 0 0 3px rgba(127, 59, 243, 0.2);
}

.select-especialidad option {
  background: #1a0a2e;
  color: #fff;
}

.btn-add {
  padding: 12px 20px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  box-shadow: 0 4px 12px rgba(127, 59, 243, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-add:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(127, 59, 243, 0.4);
}

.btn-add:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.bulk-update-section {
  padding: 20px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
}

.bulk-update-section h3 {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 8px;
}

.checkboxes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
  margin-bottom: 20px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.checkbox-item:hover {
  background: rgba(255, 255, 255, 0.06);
}

.checkbox-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.checkbox-item span {
  color: #fff;
  font-size: 14px;
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #ff2a88, #7f3bf3);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  box-shadow: 0 4px 12px rgba(127, 59, 243, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(127, 59, 243, 0.4);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.alert {
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 14px;
}

.alert.error {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #fca5a5;
}

.alert.success {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.35);
  color: #86efac;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 16px;
  }

  .selector-wrapper {
    flex-direction: column;
  }

  .checkboxes-grid {
    grid-template-columns: 1fr;
  }
}
</style>