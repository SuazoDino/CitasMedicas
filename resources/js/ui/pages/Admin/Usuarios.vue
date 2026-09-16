<template>
  <div class="admin-usuarios-page">
    <div class="page-header">
      <h2>Gestión de Usuarios</h2>
      <button class="btn-primary" @click="openCreateModal">
        + Nuevo Usuario
      </button>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <input 
        type="text" 
        v-model="filters.search" 
        placeholder="Buscar por email..." 
        @keyup.enter="fetchUsuarios(1)"
        class="input-control"
      />
      <select v-model="filters.estado" class="input-control" @change="fetchUsuarios(1)">
        <option value="">Todos los estados</option>
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
        <option value="suspendido">Suspendido</option>
      </select>
      <button class="btn-secondary" @click="fetchUsuarios(1)">Filtrar</button>
    </div>

    <!-- Tabla -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th class="text-center">Rol</th>
            <th class="text-center">Estado</th>
            <th class="text-center">Fecha Registro</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center">Cargando usuarios...</td>
          </tr>
          <tr v-else-if="usuarios.length === 0">
            <td colspan="6" class="text-center">No se encontraron usuarios.</td>
          </tr>
          <tr v-for="user in usuarios" :key="user.id" v-else>
            <td>{{ user.id }}</td>
            <td>{{ user.email }}</td>
            <td class="text-center">
              <span class="usuario-badge" :class="'role-' + user.rol_id">
                {{ user.roles ? user.roles.name : 'Rol ' + user.rol_id }}
              </span>
            </td>
            <td class="text-center">
              <span class="usuario-badge" :class="'status-' + user.estado">
                {{ user.estado }}
              </span>
            </td>
            <td class="text-center">{{ new Date(user.created_at).toLocaleDateString() }}</td>
            <td class="text-center">
              <button class="btn-icon edit" @click="openEditModal(user)" title="Editar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
              </button>
              <button class="btn-icon delete" @click="deleteUsuario(user.id)" :disabled="user.id === currentUser.id" title="Eliminar">
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
        @click="fetchUsuarios(pagination.current_page - 1)"
        class="btn-page"
      >
        &laquo; Anterior
      </button>
      
      <span class="page-info">
        Página {{ pagination.current_page }} de {{ pagination.last_page }}
      </span>

      <button 
        :disabled="pagination.current_page === pagination.last_page" 
        @click="fetchUsuarios(pagination.current_page + 1)"
        class="btn-page"
      >
        Siguiente &raquo;
      </button>
    </div>

    <!-- Modal Form (Crear/Editar) -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-content">
        <h3>{{ isEditing ? 'Editar Usuario' : 'Crear Usuario' }}</h3>
        
        <form @submit.prevent="submitForm">
          <div class="form-group" v-if="!isEditing">
            <label>Email</label>
            <input type="email" v-model="form.email" required class="input-control" />
          </div>

          <div class="form-group" v-if="!isEditing">
            <label>Contraseña</label>
            <div style="display: flex; gap: 0.5rem;">
              <input type="text" v-model="form.password" required minlength="6" class="input-control" style="flex: 1;" placeholder="Ingresa o genera una" />
              <button type="button" class="btn-secondary" @click="generatePassword" style="padding: 0.5rem; font-size: 0.85rem;">Aleatoria</button>
            </div>
            <small style="color: #64748b; margin-top: 4px; display: block;">Copia esta contraseña y entrégala al usuario.</small>
          </div>

          <div class="form-group">
            <label>Rol</label>
            <select v-model="form.rol_id" required class="input-control">
              <option value="1">Administrador</option>
              <option value="2">Paciente</option>
              <option value="3">Médico</option>
            </select>
          </div>

          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.estado" required class="input-control">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
              <option value="suspendido">Suspendido</option>
            </select>
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
import { ref, onMounted, computed } from 'vue';
import axios from '../../../axios';
import { useAuth } from '../../../composables/useAuth';
import Swal from 'sweetalert2';

// Configuración para notificaciones tipo Toast
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

const { user: currentUser } = useAuth();

const usuarios = ref([]);
const loading = ref(true);
const saving = ref(false);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const filters = ref({
  search: '',
  estado: ''
});

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const currentUserId = ref(null);

const form = ref({
  email: '',
  password: '',
  rol_id: '2',
  estado: 'activo'
});

const fetchUsuarios = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/admin/usuarios', {
      params: {
        page,
        search: filters.value.search,
        estado: filters.value.estado
      }
    });
    
    usuarios.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total
    };
  } catch (error) {
    console.error('Error cargando usuarios:', error);
    Toast.fire({
      icon: 'error',
      title: 'Error al cargar usuarios'
    });
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { email: '', password: '', rol_id: '2', estado: 'activo' };
  showModal.value = true;
};

const openEditModal = (user) => {
  isEditing.value = true;
  currentUserId.value = user.id;
  form.value = {
    email: user.email, // Solo lectura o no se envía, pero se muestra
    rol_id: user.rol_id,
    estado: user.estado
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const generatePassword = () => {
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
  let pass = '';
  for (let i = 0; i < 10; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  form.value.password = pass;
};

const submitForm = async () => {
  saving.value = true;
  try {
    if (isEditing.value) {
      await axios.put(`/admin/usuarios/${currentUserId.value}`, {
        rol_id: form.value.rol_id,
        estado: form.value.estado
      });
    } else {
      await axios.post('/admin/usuarios', form.value);
    }
    
    closeModal();
    saving.value = false;
    
    Toast.fire({
      icon: 'success',
      title: isEditing.value ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente'
    });
    
    fetchUsuarios(pagination.value.current_page);
    
  } catch (error) {
    saving.value = false;
    console.error('Error guardando usuario:', error);
    Toast.fire({
      icon: 'error',
      title: error.response?.data?.message || 'Error al guardar el usuario'
    });
  }
};

const deleteUsuario = async (id) => {
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
      await axios.delete(`/admin/usuarios/${id}`);
      Toast.fire({
        icon: 'success',
        title: 'Usuario eliminado correctamente'
      });
      fetchUsuarios(pagination.value.current_page);
    } catch (error) {
      console.error('Error eliminando usuario:', error);
      Toast.fire({
        icon: 'error',
        title: 'Error al eliminar el usuario'
      });
    }
  }
};

onMounted(() => {
  fetchUsuarios();
});
</script>

<style scoped>
.admin-usuarios-page {
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
}

.filters-card .input-control {
  flex: 1;
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

.usuario-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  display: inline-block;
  background-image: none !important;
}

.role-1 { background-color: #e0e7ff !important; color: #4338ca !important; }
.role-2 { background-color: #f3e8ff !important; color: #7e22ce !important; }
.role-3 { background-color: #dbeafe !important; color: #1d4ed8 !important; }

.status-activo { background-color: #dcfce3 !important; color: #15803d !important; }
.status-inactivo { background-color: #fee2e2 !important; color: #b91c1c !important; }
.status-suspendido { background-color: #f3f4f6 !important; color: #374151 !important; }

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

.btn-icon:disabled {
  opacity: 0.3;
  cursor: not-allowed;
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
  max-width: 450px;
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

.form-group input, .form-group select {
  width: 100%;
  box-sizing: border-box;
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
