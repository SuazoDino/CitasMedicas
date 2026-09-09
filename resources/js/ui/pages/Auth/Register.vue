<template>
  <AuthLayout
    title="Crear Cuenta"
    subtitle="Selecciona tu tipo de cuenta y completa tu información."
    :error-msg="errorMsg"
    :is-wide="true"
  >
    <!-- Progress -->
    <div class="auth-steps">
      <div class="auth-steps__bar">
        <span :class="{ active: step >= 1 }"></span>
        <span :class="{ active: step >= 2 }"></span>
        <span :class="{ active: step >= 3 }"></span>
      </div>
      <span class="auth-steps__label">
        Paso {{ step }} de 3 —
        <template v-if="step === 1">Tipo de cuenta</template>
        <template v-else-if="step === 2">Datos personales</template>
        <template v-else>Credenciales de acceso</template>
      </span>
    </div>

    <!-- Formulario -->
    <form class="auth-form" @submit.prevent="handleSubmit" id="register-form">

      <!-- PASO 1: Rol -->
      <template v-if="step === 1">
        <div class="auth-roles">
          <button
            type="button"
            class="auth-role"
            :class="{ selected: form.tipo === 'paciente' }"
            @click="form.tipo = 'paciente'"
          >
            <span class="auth-role__icon">🩺</span>
            <div class="auth-role__info">
              <span class="auth-role__title">Paciente</span>
              <span class="auth-role__desc">Quiero buscar médicos y agendar citas para mi salud.</span>
            </div>
          </button>
          <button
            type="button"
            class="auth-role"
            :class="{ selected: form.tipo === 'medico' }"
            @click="form.tipo = 'medico'"
          >
            <span class="auth-role__icon">👨‍⚕️</span>
            <div class="auth-role__info">
              <span class="auth-role__title">Profesional Médico</span>
              <span class="auth-role__desc">Soy médico y quiero gestionar mis citas y pacientes.</span>
            </div>
          </button>
        </div>
        <span v-if="errors.tipo" class="auth-field__error">{{ errors.tipo }}</span>
        <button type="button" class="auth-btn auth-btn--primary" @click="nextStep">
          Continuar
        </button>
      </template>

      <!-- PASO 2: Datos personales -->
      <template v-if="step === 2">
        <span class="auth-section">Información personal</span>

        <div class="auth-field__row">
          <div class="auth-field">
            <label class="auth-field__label" for="reg-nombres">Nombres</label>
            <input id="reg-nombres" v-model="form.nombres" type="text" class="auth-field__input" placeholder="Ej: Juan Carlos" required />
            <span v-if="errors.nombres" class="auth-field__error">{{ errors.nombres }}</span>
          </div>
          <div class="auth-field">
            <label class="auth-field__label" for="reg-apellidos">Apellidos</label>
            <input id="reg-apellidos" v-model="form.apellidos" type="text" class="auth-field__input" placeholder="Ej: García López" required />
            <span v-if="errors.apellidos" class="auth-field__error">{{ errors.apellidos }}</span>
          </div>
        </div>

        <div class="auth-field">
          <label class="auth-field__label" for="reg-dni">DNI / Documento de Identidad</label>
          <input id="reg-dni" v-model="form.dni" type="text" class="auth-field__input" placeholder="Ej: 12345678" required />
          <span v-if="errors.dni" class="auth-field__error">{{ errors.dni }}</span>
        </div>

        <!-- Solo pacientes -->
        <template v-if="form.tipo === 'paciente'">
          <div class="auth-field__row">
            <div class="auth-field">
              <label class="auth-field__label" for="reg-nacimiento">Fecha de Nacimiento</label>
              <input id="reg-nacimiento" v-model="form.fecha_nacimiento" type="date" class="auth-field__input" required />
              <span v-if="errors.fecha_nacimiento" class="auth-field__error">{{ errors.fecha_nacimiento }}</span>
            </div>
            <div class="auth-field">
              <label class="auth-field__label" for="reg-genero">Género</label>
              <select id="reg-genero" v-model="form.genero" class="auth-field__input">
                <option value="">Seleccionar...</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
              </select>
            </div>
          </div>
        </template>

        <!-- Solo médicos -->
        <template v-if="form.tipo === 'medico'">
          <div class="auth-field__row">
            <div class="auth-field">
              <label class="auth-field__label" for="reg-colegiatura">Número de Colegiatura</label>
              <input id="reg-colegiatura" v-model="form.numero_colegiatura" type="text" class="auth-field__input" placeholder="Ej: CMP-12345" required />
              <span v-if="errors.numero_colegiatura" class="auth-field__error">{{ errors.numero_colegiatura }}</span>
            </div>
            <div class="auth-field">
              <label class="auth-field__label" for="reg-especialidad">Especialidad</label>
              <select id="reg-especialidad" v-model="form.especialidad_id" class="auth-field__input">
                <option value="">Seleccionar...</option>
                <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">
                  {{ esp.nombre_especialidad }}
                </option>
              </select>
              <span v-if="errors.especialidad_id" class="auth-field__error">{{ errors.especialidad_id }}</span>
            </div>
          </div>
        </template>

        <div class="auth-field">
          <label class="auth-field__label" for="reg-telefono">Teléfono (opcional)</label>
          <input id="reg-telefono" v-model="form.telefono" type="tel" class="auth-field__input" placeholder="Ej: +51 987 654 321" />
        </div>

        <div class="auth-actions">
          <button type="button" class="auth-btn auth-btn--ghost" @click="step = 1">← Atrás</button>
          <button type="button" class="auth-btn auth-btn--primary" @click="nextStep">Continuar</button>
        </div>
      </template>

      <!-- PASO 3: Credenciales -->
      <template v-if="step === 3">
        <span class="auth-section">Credenciales de acceso</span>

        <div class="auth-field">
          <label class="auth-field__label" for="reg-email">Correo Electrónico</label>
          <input id="reg-email" v-model="form.email" type="email" class="auth-field__input" placeholder="tu@correo.com" required autocomplete="email" />
          <span v-if="errors.email" class="auth-field__error">{{ errors.email }}</span>
        </div>

        <div class="auth-field">
          <div class="auth-field__header">
            <label class="auth-field__label" for="reg-password">Contraseña</label>
            <label class="auth-field__toggle">
              <input type="checkbox" v-model="showPassword" />
              Mostrar
            </label>
          </div>
          <input id="reg-password" v-model="form.password" :type="showPassword ? 'text' : 'password'" class="auth-field__input" placeholder="Mínimo 8 caracteres" required />
          <span v-if="errors.password" class="auth-field__error">{{ errors.password }}</span>
        </div>

        <div class="auth-field">
          <label class="auth-field__label" for="reg-password-confirm">Confirmar Contraseña</label>
          <input id="reg-password-confirm" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" class="auth-field__input" placeholder="Repite tu contraseña" required />
          <span v-if="errors.password_confirmation" class="auth-field__error">{{ errors.password_confirmation }}</span>
        </div>

        <div class="auth-actions">
          <button type="button" class="auth-btn auth-btn--ghost" @click="step = 2">← Atrás</button>
          <button type="submit" class="auth-btn auth-btn--primary" :disabled="loading" id="register-submit">
            <span v-if="loading">Creando cuenta...</span>
            <span v-else>Crear Cuenta</span>
          </button>
        </div>
      </template>
    </form>

    <template #footer>
      <span>¿Ya tienes una cuenta?</span>
      <router-link to="/auth/login" class="auth-link">Inicia sesión</router-link>
    </template>
  </AuthLayout>
</template>

<script>
import AuthLayout from '../../components/AuthLayout.vue'
import axios from '../../../axios'

export default {
  name: 'RegisterPage',
  components: {
    AuthLayout
  },
  data() {
    return {
      step: 1,
      form: {
        tipo: '', nombres: '', apellidos: '', dni: '', fecha_nacimiento: '',
        genero: '', telefono: '', numero_colegiatura: '', especialidad_id: '',
        email: '', password: '', password_confirmation: '',
      },
      errors: {},
      errorMsg: '',
      loading: false,
      showPassword: false,
      especialidades: [
        { id: 1, nombre_especialidad: 'Medicina General' },
        { id: 2, nombre_especialidad: 'Pediatría' },
        { id: 3, nombre_especialidad: 'Cardiología' },
        { id: 4, nombre_especialidad: 'Dermatología' },
        { id: 5, nombre_especialidad: 'Traumatología' },
      ],
    }
  },
  methods: {
    validateStep() {
      this.errors = {}
      if (this.step === 1 && !this.form.tipo) this.errors.tipo = 'Selecciona un tipo de cuenta.'
      if (this.step === 2) {
        if (!this.form.nombres) this.errors.nombres = 'Obligatorio.'
        if (!this.form.apellidos) this.errors.apellidos = 'Obligatorio.'
        if (!this.form.dni) this.errors.dni = 'Obligatorio.'
        if (this.form.tipo === 'paciente' && !this.form.fecha_nacimiento) this.errors.fecha_nacimiento = 'Obligatorio.'
        if (this.form.tipo === 'medico') {
          if (!this.form.numero_colegiatura) this.errors.numero_colegiatura = 'Obligatorio.'
          if (!this.form.especialidad_id) this.errors.especialidad_id = 'Obligatorio.'
        }
      }
      if (this.step === 3) {
        if (!this.form.email) this.errors.email = 'El correo es obligatorio.'
        if (!this.form.password) this.errors.password = 'La contraseña es obligatoria.'
        else if (this.form.password.length < 8) this.errors.password = 'Mínimo 8 caracteres.'
        if (this.form.password !== this.form.password_confirmation) this.errors.password_confirmation = 'Las contraseñas no coinciden.'
      }
      return Object.keys(this.errors).length === 0
    },
    nextStep() {
      this.errorMsg = ''
      if (!this.validateStep()) return
      if (this.step < 3) this.step++
    },
    async handleSubmit() {
      this.errorMsg = ''
      if (!this.validateStep()) return
      this.loading = true
      
      try {
        await axios.post('/register', this.form);
        this.$router.push({ path: '/auth/login', query: { msg: 'Cuenta creada exitosamente. Revisa tu correo para confirmar tu cuenta.' } })
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const apiErrors = error.response.data;
          // Asignar errores de la API al estado de validación
          for (let key in apiErrors) {
            this.errors[key] = apiErrors[key][0];
          }
          this.errorMsg = 'Por favor, corrige los errores en el formulario.';
        } else {
          this.errorMsg = 'Ocurrió un error inesperado al intentar registrar tu cuenta.';
        }
      } finally {
        this.loading = false
      }
    },
  },
}
</script>
