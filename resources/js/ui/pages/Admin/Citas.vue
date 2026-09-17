<template>
  <div class="admin-citas-page">
    <div class="page-header">
      <h2>Gestión de Citas Médicas</h2>
      <button class="btn-primary" @click="openCreateModal">
        + Nueva Cita
      </button>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <input
        type="text"
        v-model="filters.search"
        placeholder="Buscar por motivo..."
        @keyup.enter="fetchCitas(1)"
        class="input-control"
      />
      <select v-model="filters.estado" class="input-control" @change="fetchCitas(1)">
        <option value="">Todos los estados</option>
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="completada">Completada</option>
        <option value="cancelada">Cancelada</option>
        <option value="no_asistio">No asistió</option>
      </select>
      <select v-model="filters.medico_id" class="input-control" @change="fetchCitas(1)">
        <option value="">Todos los médicos</option>
        <option v-for="medico in medicos" :key="medico.id" :value="medico.id">{{ medico.label }}</option>
      </select>
      <button class="btn-secondary" @click="fetchCitas(1)">Filtrar</button>
    </div>

    <!-- Tabla -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Paciente</th>
            <th>Médico</th>
            <th>Especialidad</th>
            <th class="text-center">Inicio</th>
            <th class="text-center">Fin</th>
            <th class="text-center">Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="8" class="text-center">Cargando citas...</td>
          </tr>
          <tr v-else-if="citas.length === 0">
            <td colspan="8" class="text-center">No se encontraron citas.</td>
          </tr>
          <tr v-for="cita in citas" :key="cita.id" v-else>
            <td>{{ cita.id }}</td>
            <td>{{ cita.paciente?.usuario?.email || '—' }}</td>
            <td>{{ cita.medico?.usuario?.email || '—' }}</td>
            <td>{{ cita.especialidad?.nombre || '—' }}</td>
            <td class="text-center">{{ formatDate(cita.starts_at) }}</td>
            <td class="text-center">{{ formatDate(cita.ends_at) }}</td>
            <td class="text-center">
              <span class="cita-badge" :class="'status-' + cita.estado">
                {{ estadoLabel(cita.estado) }}
              </span>
            </td>
            <td class="text-center">
              <button class="btn-icon edit" @click="openEditModal(cita)" title="Editar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
              </button>
              <button class="btn-icon delete" @click="deleteCita(cita.id)" title="Eliminar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="pagination-controls" v-if="pagination.last_page > 1">
      <button
        :disabled="pagination.current_page === 1"
        @click="fetchCitas(pagination.current_page - 1)"
        class="btn-page"
      >
        &laquo; Anterior
      </button>

      <span class="page-info">
        Página {{ pagination.current_page }} de {{ pagination.last_page }}
      </span>

      <button
        :disabled="pagination.current_page === pagination.last_page"
        @click="fetchCitas(pagination.current_page + 1)"
        class="btn-page"
      >
        Siguiente &raquo;
      </button>
    </div>

    <!-- Modal Form (Crear/Editar) -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-content">
        <h3>{{ isEditing ? 'Editar Cita' : 'Nueva Cita' }}</h3>

        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Paciente</label>
            <select v-model="form.paciente_id" required class="input-control">
              <option value="" disabled>Selecciona un paciente</option>
              <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id">{{ paciente.label }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Médico</label>
            <select v-model="form.medico_id" required class="input-control">
              <option value="" disabled>Selecciona un médico</option>
              <option v-for="medico in medicos" :key="medico.id" :value="medico.id">{{ medico.label }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Especialidad</label>
            <select v-model="form.especialidad_id" required class="input-control">
              <option value="" disabled>Selecciona una especialidad</option>
              <option v-for="especialidad in especialidades" :key="especialidad.id" :value="especialidad.id">{{ especialidad.nombre }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Fecha y hora de inicio</label>
            <input type="datetime-local" v-model="form.starts_at" required class="input-control" />
          </div>

          <div class="form-group">
            <label>Fecha y hora de fin</label>
            <input type="datetime-local" v-model="form.ends_at" required class="input-control" />
          </div>

          <div class="form-group">
            <label>Motivo</label>
            <input type="text" v-model="form.motivo" maxlength="140" class="input-control" placeholder="Motivo de la consulta" />
          </div>

          <div class="form-group">
            <label>Notas</label>
            <textarea v-model="form.notas" class="input-control" rows="3"></textarea>
          </div>

          <div class="form-group" v-if="isEditing">
            <label>Estado</label>
            <select v-model="form.estado" required class="input-control">
              <option value="pendiente">Pendiente</option>
              <option value="confirmada">Confirmada</option>
              <option value="completada">Completada</option>
              <option value="cancelada">Cancelada</option>
              <option value="no_asistio">No asistió</option>
            </select>
          </div>

          <div class="form-group" v-if="isEditing && form.estado === 'cancelada'">
            <label>Motivo de cancelación</label>
            <input type="text" v-model="form.cancel_reason" maxlength="180" required class="input-control" />
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeModal">Cancelar</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
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
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});

const ESTADOS = {
  pendiente: 'Pendiente',
  confirmada: 'Confirmada',
  completada: 'Completada',
  cancelada: 'Cancelada',
  no_asistio: 'No asistió',
};

const citas = ref([]);
const medicos = ref([]);
const pacientes = ref([]);
const especialidades = ref([]);
const loading = ref(true);
const saving = ref(false);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const filters = ref({
  search: '',
  estado: '',
  medico_id: ''
});

const showModal = ref(false);
const isEditing = ref(false);
const currentCitaId = ref(null);

const emptyForm = () => ({
  paciente_id: '',
  medico_id: '',
  especialidad_id: '',
  starts_at: '',
  ends_at: '',
  motivo: '',
  notas: '',
  estado: 'pendiente',
  cancel_reason: ''
});

const form = ref(emptyForm());

const estadoLabel = (estado) => ESTADOS[estado] || estado;

const formatDate = (value) => {
  if (!value) return '—';
  return new Date(value).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' });
};

// Convierte un valor ISO del backend a formato datetime-local (YYYY-MM-DDTHH:mm)
const toDatetimeLocal = (value) => {
  if (!value) return '';
  const date = new Date(value);
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const fetchLookups = async () => {
  try {
    const response = await axios.get('/admin/citas-opciones');
    medicos.value = response.data.medicos;
    pacientes.value = response.data.pacientes;
    especialidades.value = response.data.especialidades;
  } catch (error) {
    console.error('Error cargando opciones:', error);
    Toast.fire({ icon: 'error', title: 'Error al cargar médicos/pacientes/especialidades' });
  }
};

const fetchCitas = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/citas', {
      params: {
        page,
        search: filters.value.search,
        estado: filters.value.estado,
        medico_id: filters.value.medico_id
      }
    });

    citas.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total
    };
  } catch (error) {
    console.error('Error cargando citas:', error);
    Toast.fire({ icon: 'error', title: 'Error al cargar citas' });
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = emptyForm();
  showModal.value = true;
};

const openEditModal = (cita) => {
  isEditing.value = true;
  currentCitaId.value = cita.id;
  form.value = {
    paciente_id: cita.paciente_id,
    medico_id: cita.medico_id,
    especialidad_id: cita.especialidad_id,
    starts_at: toDatetimeLocal(cita.starts_at),
    ends_at: toDatetimeLocal(cita.ends_at),
    motivo: cita.motivo || '',
    notas: cita.notas || '',
    estado: cita.estado,
    cancel_reason: cita.cancel_reason || ''
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = async () => {
  saving.value = true;
  try {
    if (isEditing.value) {
      await axios.put(`/admin/citas/${currentCitaId.value}`, form.value);
    } else {
      const { estado, cancel_reason, ...payload } = form.value;
      await axios.post('/admin/citas', payload);
    }

    closeModal();
    saving.value = false;

    Toast.fire({
      icon: 'success',
      title: isEditing.value ? 'Cita actualizada correctamente' : 'Cita creada correctamente'
    });

    fetchCitas(pagination.value.current_page);
  } catch (error) {
    saving.value = false;
    console.error('Error guardando cita:', error);
    Toast.fire({
      icon: 'error',
      title: error.response?.data?.message || 'Error al guardar la cita'
    });
  }
};

const deleteCita = async (id) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción no se puede deshacer.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/admin/citas/${id}`);
      Toast.fire({ icon: 'success', title: 'Cita eliminada correctamente' });
      fetchCitas(pagination.value.current_page);
    } catch (error) {
      console.error('Error eliminando cita:', error);
      Toast.fire({ icon: 'error', title: 'Error al eliminar la cita' });
    }
  }
};

onMounted(() => {
  fetchLookups();
  fetchCitas();
});
</script>

<style scoped>
.admin-citas-page {
  padding: 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h2 {
  font-size: 1.75rem;
  color: #1a1a2e;
  margin: 0;
}

.filters-card {
  display: flex;
  gap: 1rem;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.filters-card .input-control {
  flex: 1;
  min-width: 160px;
}

.input-control {
  padding: 0.6rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}

select.input-control {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.2em;
  padding-right: 2.5rem;
}

.input-control:focus {
  border-color: #4f46e5;
}

.btn-primary {
  background-color: #4f46e5;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: #4338ca;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background-color: #e2e8f0;
}

.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
  margin-bottom: 1.5rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th, .data-table td {
  padding: 1rem 1.5rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
  white-space: nowrap;
}

.data-table th {
  background-color: #f8fafc;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
}

.text-center {
  text-align: center;
}

.cita-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  display: inline-block;
}

.status-pendiente { background-color: #fef3c7 !important; color: #b45309 !important; }
.status-confirmada { background-color: #dbeafe !important; color: #1d4ed8 !important; }
.status-completada { background-color: #dcfce3 !important; color: #15803d !important; }
.status-cancelada { background-color: #fee2e2 !important; color: #b91c1c !important; }
.status-no_asistio { background-color: #f3f4f6 !important; color: #374151 !important; }

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  margin-right: 0.5rem;
  opacity: 0.6;
  transition: opacity 0.2s, color 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-icon svg {
  width: 1.25rem;
  height: 1.25rem;
}

.btn-icon.edit:hover {
  opacity: 1;
  color: #2563eb;
}

.btn-icon.delete:hover {
  opacity: 1;
  color: #dc2626;
}

.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.btn-page {
  background-color: white;
  border: 1px solid #cbd5e1;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-page:hover:not(:disabled) {
  background-color: #f8fafc;
  border-color: #94a3b8;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.9rem;
  color: #475569;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
  padding: 1rem;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-content h3 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
  color: #1a1a2e;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
}

.form-group input, .form-group select, .form-group textarea {
  width: 100%;
  box-sizing: border-box;
  font-family: inherit;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .filters-card {
    flex-direction: column;
  }

  .pagination-controls {
    flex-direction: column;
    gap: 0.5rem;
  }

  .btn-page {
    width: 100%;
  }

  .data-table th, .data-table td {
    padding: 0.75rem 0.5rem;
    font-size: 0.85rem;
  }
}
</style>
