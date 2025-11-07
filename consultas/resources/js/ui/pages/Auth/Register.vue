<template>
  <div class="doctoralia-auth">
    <!-- Logo centrado arriba -->
    <div class="auth-logo">
      <span class="auth-logo__icon">⚡</span>
      <span class="auth-logo__text">MediReserva</span>
      </div>

    <!-- Formulario centrado -->
    <div class="auth-container">
      <form @submit.prevent="onSubmit" class="auth-form">
        <h1 class="auth-title">Crear una cuenta gratuita</h1>

        <!-- Mensaje de éxito -->
        <div v-if="successMessage" class="auth-message auth-message--success">
          <strong>¡Cuenta creada exitosamente! 🎉</strong>
          <p>{{ successMessage }}</p>
        </div>

        <!-- Selección de tipo de cuenta (solo en paso 1) -->
        <div v-if="!stepMode || currentStep === 0" class="auth-role-selector">
          <label class="auth-role-option" :class="{ 'is-selected': roleLabel === 'paciente' }" @click="selectRole('paciente')">
            <input type="radio" name="role" value="paciente" :checked="roleLabel === 'paciente'" class="auth-role-input" @change="selectRole('paciente')" />
            <div class="auth-role-card">
              <div class="auth-role-icon">👤</div>
              <div class="auth-role-content">
                <h3>Soy paciente</h3>
                <p>Comparte información básica con tu especialista antes de la visita</p>
              </div>
            </div>
            </label>

          <label class="auth-role-option" :class="{ 'is-selected': roleLabel === 'medico' }" @click="selectRole('medico')">
            <input type="radio" name="role" value="medico" :checked="roleLabel === 'medico'" class="auth-role-input" @change="selectRole('medico')" />
            <div class="auth-role-card">
              <div class="auth-role-icon">👨‍⚕️</div>
              <div class="auth-role-content">
                <h3>Soy especialista</h3>
                <p>Consigue que tus pacientes te conozcan, confíen en ti y reserven contigo</p>
              </div>
            </div>
            </label>
          <p v-if="role.errorMessage" class="auth-error">{{ role.errorMessage }}</p>
          
          <!-- Indicador visual cuando se selecciona médico -->
          <div v-if="roleLabel === 'medico'" class="auth-role-notice">
            <p>Como especialista, necesitarás completar tus datos de identidad y licencia profesional en el paso 3.</p>
          </div>
        </div>

        <!-- Paso 1: Perfil -->
        <div v-if="!stepMode || currentStep === 0" class="auth-step">
        <div class="auth-field">
          <input
            id="reg-full-name"
            name="full_name"
            :value="getFieldValue(fullName)"
            @input="fullName.setValue($event.target.value)"
            @blur="fullName.handleBlur"
            type="text"
            autocomplete="name"
            class="auth-input"
            placeholder="Nombre completo"
          />
          <p v-if="fullName.errorMessage" class="auth-error">{{ fullName.errorMessage }}</p>
        </div>

        <div class="auth-field">
          <input
            id="reg-email"
            name="email"
            :value="getFieldValue(email)"
            @input="email.setValue($event.target.value)"
            @blur="email.handleBlur"
            type="email"
            autocomplete="email"
            class="auth-input"
            placeholder="Correo electrónico"
          />
          <p v-if="email.errorMessage" class="auth-error">{{ email.errorMessage }}</p>
          </div>
        </div>

        <!-- Paso 2: Seguridad -->
        <div v-if="!stepMode || currentStep === 1" class="auth-step">
        <div class="auth-field">
          <input
            id="reg-phone"
            name="phone"
            :value="getFieldValue(phone)"
            @input="phone.setValue($event.target.value)"
            @blur="phone.handleBlur"
            type="tel"
            autocomplete="tel"
            class="auth-input"
            placeholder="Teléfono de contacto (opcional)"
          />
          <p v-if="phone.errorMessage" class="auth-error">{{ phone.errorMessage }}</p>
        </div>

        <div class="auth-field">
          <input
            id="reg-password"
            name="password"
            :value="getFieldValue(password)"
            @input="password.setValue($event.target.value)"
            @blur="password.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            class="auth-input"
            placeholder="Crea una contraseña (mínimo 6 caracteres)"
          />
          <p v-if="password.errorMessage" class="auth-error">{{ password.errorMessage }}</p>
        </div>

        <div class="auth-field">
          <input
            id="reg-password-confirm"
            name="password_confirmation"
            :value="getFieldValue(passwordConfirmation)"
            @input="passwordConfirmation.setValue($event.target.value)"
            @blur="passwordConfirmation.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            class="auth-input"
            placeholder="Repite la contraseña"
          />
          <p v-if="passwordConfirmation.errorMessage" class="auth-error">{{ passwordConfirmation.errorMessage }}</p>
            <label class="auth-checkbox-label">
              <input type="checkbox" v-model="showPassword" />
              <span>Mostrar contraseñas</span>
          </label>
        </div>
        </div>

        <!-- Paso 3: Credenciales/Personalización -->
        <div v-if="!stepMode || currentStep === 2" class="auth-step">
        <template v-if="roleLabel === 'medico'">
            <div class="auth-step-header">
              <h2 class="auth-step-title">Datos profesionales</h2>
              <p class="auth-hint-text">Confirma tus credenciales para validar tu cuenta profesional. Todos los campos son obligatorios.</p>
            </div>
            
          <div class="auth-field">
              <label class="auth-label">Tipo de documento de identidad *</label>
              <select
              id="reg-id-doc-tipo"
              name="id_doc_tipo"
              :value="getFieldValue(idDocTipo)"
              @change="idDocTipo.setValue($event.target.value)"
              @blur="idDocTipo.handleBlur"
              class="auth-input"
              >
                <option value="">Selecciona el tipo</option>
                <option value="DNI">DNI</option>
                <option value="CE">Cédula de Extranjería</option>
                <option value="PAS">Pasaporte</option>
              </select>
            <p v-if="idDocTipo.errorMessage" class="auth-error">{{ idDocTipo.errorMessage }}</p>
          </div>
            
          <div class="auth-field">
              <label class="auth-label">Número de documento *</label>
            <input
              id="reg-id-doc-numero"
              name="id_doc_numero"
              :value="getFieldValue(idDocNumero)"
              @input="idDocNumero.setValue($event.target.value)"
              @blur="idDocNumero.handleBlur"
              type="text"
                maxlength="32"
              class="auth-input"
              placeholder="Ej. 12345678"
            />
            <p v-if="idDocNumero.errorMessage" class="auth-error">{{ idDocNumero.errorMessage }}</p>
          </div>
            
          <div class="auth-field">
              <label class="auth-label">Tipo de licencia profesional *</label>
              <select
              id="reg-lic-tipo"
              name="lic_tipo"
              :value="getFieldValue(licTipo)"
              @change="licTipo.setValue($event.target.value)"
              @blur="licTipo.handleBlur"
              class="auth-input"
              >
                <option value="">Selecciona el tipo</option>
                <option value="CMP">CMP (Colegio Médico del Perú)</option>
                <option value="COP">COP (Colegio Odontológico del Perú)</option>
                <option value="Registro">Registro Nacional</option>
                <option value="Otro">Otro</option>
              </select>
            <p v-if="licTipo.errorMessage" class="auth-error">{{ licTipo.errorMessage }}</p>
          </div>
            
          <div class="auth-field">
              <label class="auth-label">Número de licencia *</label>
            <input
              id="reg-lic-numero"
              name="lic_numero"
              :value="getFieldValue(licNumero)"
              @input="licNumero.setValue($event.target.value)"
              @blur="licNumero.handleBlur"
              type="text"
                maxlength="32"
              class="auth-input"
              placeholder="Ej. 12345"
            />
            <p v-if="licNumero.errorMessage" class="auth-error">{{ licNumero.errorMessage }}</p>
          </div>
            
          <div class="auth-field">
              <label class="auth-label">País de expedición *</label>
            <input
              id="reg-lic-pais"
              name="lic_pais"
              :value="getFieldValue(licPais)"
              @input="handlePaisInput"
              @blur="licPais.handleBlur"
              type="text"
                maxlength="2"
              class="auth-input"
                placeholder="Código ISO (Ej: PE)"
                style="text-transform: uppercase;"
            />
            <p v-if="licPais.errorMessage" class="auth-error">{{ licPais.errorMessage }}</p>
              <p class="auth-hint-text">Ingresa el código de 2 letras del país (PE, CO, MX, etc.)</p>
          </div>
        </template>
        <template v-else>
            <p class="auth-hint-text">Estos datos nos ayudan a personalizar tus citas (opcional)</p>
          <div class="auth-field">
            <input
              id="reg-doc-tipo"
              name="doc_tipo"
              :value="getFieldValue(docTipo)"
              @input="docTipo.setValue($event.target.value)"
              @blur="docTipo.handleBlur"
              type="text"
              class="auth-input"
                placeholder="Tipo de documento (opcional)"
            />
          </div>
          <div class="auth-field">
            <input
              id="reg-doc-numero"
              name="doc_numero"
              :value="getFieldValue(docNumero)"
              @input="docNumero.setValue($event.target.value)"
              @blur="docNumero.handleBlur"
              type="text"
              class="auth-input"
                placeholder="Número de documento (opcional)"
            />
          </div>
          <div class="auth-field">
            <input
              id="reg-birthdate"
              name="birthdate"
              :value="getFieldValue(birthdate)"
              @input="birthdate.setValue($event.target.value)"
              @blur="birthdate.handleBlur"
              type="date"
              class="auth-input"
            />
          </div>
          <div class="auth-field">
              <select id="reg-gender" name="gender" :value="getFieldValue(gender)" @change="gender.setValue($event.target.value)" class="auth-input">
                <option value="">Género (opcional)</option>
              <option value="femenino">Femenino</option>
              <option value="masculino">Masculino</option>
              <option value="no-binario">No binario</option>
              <option value="prefiero-no-decirlo">Prefiero no decirlo</option>
            </select>
          </div>
        </template>
      </div>

        <!-- Navegación de pasos -->
        <div v-if="stepMode" class="auth-steps-nav">
        <button
          v-if="currentStep > 0"
          type="button"
            class="auth-btn-secondary"
          @click="previousStep"
        >
          Volver
        </button>
        <button
          v-if="currentStep < steps.length - 1"
          type="button"
            class="auth-btn-primary"
          @click="goToNextStep"
        >
          Continuar
        </button>
      </div>

        <!-- Botón de envío -->
        <button
          v-if="!stepMode || currentStep === steps.length - 1"
          class="auth-submit"
          type="submit"
          :disabled="isSubmitting"
          @click="(e) => { 
            try {
              console.log('🔘 Botón clickeado!', { 
                stepMode: stepMode.value, 
                currentStep: currentStep.value, 
                stepsLength: steps?.value?.length || 0, 
                isSubmitting: isSubmitting.value,
                buttonVisible: !stepMode.value || currentStep.value === (steps?.value?.length || 0) - 1
              })
            } catch (err) {
              console.error('Error en click handler:', err)
            }
            // No prevenir el submit aquí, dejar que vee-validate lo maneje
          }"
        >
        <span v-if="isSubmitting">Creando tu cuenta…</span>
        <span v-else>Crear cuenta</span>
      </button>

        <!-- Footer -->
        <p class="auth-footer">
          ¿Ya tienes cuenta?
          <RouterLink class="auth-link" :to="{ name: 'login', query: { email: getFieldValue(email) } }">
            Iniciar sesión
          </RouterLink>
        </p>
    </form>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm, useField, defineRule } from '@vee-validate/core'
import { required, email as emailRule, min, max, confirmed, one_of as oneOf } from '@vee-validate/rules'
import api from '../../../auth/api'
import { auth } from '../../../auth/store'
import { getFieldValue } from './getFieldValue'

const props = defineProps({
  defaultRole: {
    type: String,
    default: 'paciente',
    validator: (value) => ['paciente', 'medico'].includes(value),
  },
})

defineRule('required', required)
defineRule('email', emailRule)
defineRule('min', min)
defineRule('max', max)
defineRule('confirmed', confirmed)
defineRule('one_of', oneOf)

const router = useRouter()
const route = useRoute()
const backendIssue = ref(null)
const showPassword = ref(false)
const successMessage = ref('')

const initialRole = typeof route.query.role === 'string' ? route.query.role : props.defaultRole

// Función helper para obtener valores seguros de la query
const getQueryValue = (key, defaultValue = '') => {
  const value = route.query[key]
  if (typeof value === 'string') return value
  if (value == null) return defaultValue
  return String(value)
}

const { handleSubmit, isSubmitting, setErrors } = useForm({
  initialValues: {
    full_name: getQueryValue('name', ''),
    email: getQueryValue('email', ''),
    phone: '',
    role: String(initialRole),
    password: '',
    password_confirmation: '',
    doc_tipo: '',
    doc_numero: '',
    birthdate: '',
    gender: '',
    id_doc_tipo: '',
    id_doc_numero: '',
    lic_tipo: '',
    lic_numero: '',
    lic_pais: '',
  },
  validateOnInput: true,
  validateOnBlur: true,
})

const fullName = useField('full_name', 'required|min:3|max:120')
const email = useField('email', 'required|email|max:190')
const phone = useField('phone', (value) => {
  if (!value) return true
  if (value.replace(/\D/g, '').length < 7) return 'Ingresa un teléfono válido'
  return value.length <= 30 ? true : 'Máximo 30 caracteres'
})
const role = useField('role', 'required|one_of:paciente,medico')
const password = useField('password', 'required|min:6')
const passwordConfirmation = useField('password_confirmation', 'required|confirmed:password')

// Definir roleLabel ANTES de usarlo en las validaciones
const roleLabel = computed(() => role.value.value || 'paciente')

const docTipo = useField('doc_tipo')
const docNumero = useField('doc_numero')
const birthdate = useField('birthdate')
const gender = useField('gender')

// Validación condicional para campos de médico
const idDocTipo = useField('id_doc_tipo', (value) => {
  if (roleLabel.value !== 'medico') return true
  if (!value || value.trim() === '') return 'El tipo de documento es obligatorio para especialistas'
  if (value.length > 10) return 'Máximo 10 caracteres'
  return true
})

const idDocNumero = useField('id_doc_numero', (value) => {
  if (roleLabel.value !== 'medico') return true
  if (!value || value.trim() === '') return 'El número de documento es obligatorio para especialistas'
  if (value.length > 32) return 'Máximo 32 caracteres'
  return true
})

const licTipo = useField('lic_tipo', (value) => {
  if (roleLabel.value !== 'medico') return true
  if (!value || value.trim() === '') return 'El tipo de licencia es obligatorio para especialistas'
  if (value.length > 16) return 'Máximo 16 caracteres'
  return true
})

const licNumero = useField('lic_numero', (value) => {
  if (roleLabel.value !== 'medico') return true
  if (!value || value.trim() === '') return 'El número de licencia es obligatorio para especialistas'
  if (value.length > 32) return 'Máximo 32 caracteres'
  return true
})

const licPais = useField('lic_pais', (value) => {
  if (roleLabel.value !== 'medico') return true
  if (!value || value.trim() === '') return 'El país de expedición es obligatorio para especialistas'
  const trimmed = value.trim().toUpperCase()
  if (trimmed.length !== 2) return 'Debe ser un código ISO de 2 letras (Ej: PE, CO, MX)'
  if (!/^[A-Z]{2}$/.test(trimmed)) return 'Solo se permiten letras (código ISO)'
  return true
})

const stepMode = ref(true)
const currentStep = ref(0)

const steps = computed(() => {
  const base = [
    { id: 'perfil' },
    { id: 'seguridad' },
  ]
  base.push({ id: roleLabel.value === 'medico' ? 'credenciales' : 'personalizacion' })
  return base
})

watch(roleLabel, (value) => {
  if (currentStep.value >= steps.value.length) {
    currentStep.value = steps.value.length - 1
  }
  backendIssue.value = null
  
  // Limpiar campos del otro rol cuando se cambia
  if (value === 'medico') {
    docTipo.setValue('')
    docNumero.setValue('')
    birthdate.setValue('')
    gender.setValue('')
  } else {
    idDocTipo.setValue('')
    idDocNumero.setValue('')
    licTipo.setValue('')
    licNumero.setValue('')
    licPais.setValue('')
  }
}, { immediate: false })

const selectRole = (roleValue) => {
  console.log('🔄 Cambiando rol a:', roleValue)
  role.setValue(String(roleValue))
  // Forzar actualización inmediata
  role.handleBlur()
}

const handlePaisInput = (event) => {
  const value = String(event.target.value || '').toUpperCase().replace(/[^A-Z]/g, '')
  licPais.setValue(value)
}

const toggleStepMode = () => {
  stepMode.value = !stepMode.value
  if (!stepMode.value) {
    currentStep.value = 0
  }
}

const goToNextStep = async () => {
  if (!stepMode.value) return
  const valid = await validateStep(currentStep.value)
  if (valid) {
    currentStep.value = Math.min(currentStep.value + 1, steps.value.length - 1)
  }
}

const previousStep = () => {
  currentStep.value = Math.max(currentStep.value - 1, 0)
}

async function validateStep(index) {
  const validators = {
    full_name: fullName,
    email,
    role,
    phone,
    password,
    password_confirmation: passwordConfirmation,
    doc_tipo: docTipo,
    doc_numero: docNumero,
    birthdate,
    gender,
    id_doc_tipo: idDocTipo,
    id_doc_numero: idDocNumero,
    lic_tipo: licTipo,
    lic_numero: licNumero,
    lic_pais: licPais,
  }

  const stepFieldsMap = [
    ['full_name', 'email', 'role'],
    ['phone', 'password', 'password_confirmation'],
    roleLabel.value === 'medico'
      ? ['id_doc_tipo', 'id_doc_numero', 'lic_tipo', 'lic_numero', 'lic_pais']
      : ['doc_tipo', 'doc_numero', 'birthdate', 'gender'],
  ]

  const currentFields = stepFieldsMap[index] ?? []
  const results = await Promise.all(
    currentFields.map(async (field) => {
      const controller = validators[field]
      if (!controller) return true
      const res = await controller.validate()
      return res.valid
    })
  )
  return results.every(Boolean)
}

const normalizeString = (value) => {
  if (value == null) return ''
  if (typeof value === 'string') return value.trim()
  if (typeof value === 'object') return '' // Si es objeto, retornar string vacío
  return String(value).trim()
}

const emptyToNull = (value) => {
  const normalized = normalizeString(value)
  return normalized === '' ? null : normalized
}

const onSubmit = handleSubmit(async (formValues, helpers) => {
  console.log('🚀 onSubmit ejecutado!')
  console.log('formValues:', formValues)
  console.log('stepMode:', stepMode.value)
  console.log('currentStep:', currentStep.value)
  console.log('steps:', steps.value)
  console.log('steps.length:', steps?.value?.length || 0)
  
  backendIssue.value = null
  successMessage.value = '' // Limpiar mensaje de éxito al iniciar nuevo submit

  if (stepMode.value && steps.value && currentStep.value < steps.value.length - 1) {
    console.log('⏭️ Avanzando al siguiente paso...')
    const ok = await validateStep(currentStep.value)
    if (ok) {
      currentStep.value += 1
    }
    return
  }
  
  console.log('✅ Procediendo con el registro...')

  let payload = null
  
  try {
    // Obtener valores directamente de los campos (más confiable que formValues)
    const fullNameValue = normalizeString(getFieldValue(fullName) || '')
    const emailValue = normalizeString(getFieldValue(email) || '')
    const passwordValue = getFieldValue(password) || ''
    const passwordConfValue = getFieldValue(passwordConfirmation) || ''
    const roleValue = normalizeString(getFieldValue(role) || 'paciente')
    
    console.log('📋 Valores obtenidos de los campos:', {
      fullNameValue,
      emailValue,
      passwordValue: passwordValue ? '***' : '',
      roleValue
    })
    
    if (!fullNameValue || fullNameValue.trim() === '') {
      helpers.setErrors({ full_name: 'El nombre completo es obligatorio' })
      return
    }
    
    if (!emailValue || emailValue.trim() === '') {
      helpers.setErrors({ email: 'El correo electrónico es obligatorio' })
      return
    }
    
    if (!passwordValue || passwordValue.length < 6) {
      helpers.setErrors({ password: 'La contraseña debe tener al menos 6 caracteres' })
      return
    }

    payload = {
      full_name: fullNameValue.trim(),
      email: emailValue.trim(),
      phone: emptyToNull(getFieldValue(phone)),
      password: passwordValue,
      password_confirmation: passwordConfValue,
      role: roleValue || 'paciente',
    }

    let endpoint = '/auth/register/paciente'
    if (roleValue === 'medico') {
      endpoint = '/auth/register/medico'
      
      // Obtener valores directamente de los campos, asegurándonos de que sean strings
      const idDocTipoRaw = getFieldValue(idDocTipo)
      const idDocNumeroRaw = getFieldValue(idDocNumero)
      const licTipoRaw = getFieldValue(licTipo)
      const licNumeroRaw = getFieldValue(licNumero)
      const licPaisRaw = getFieldValue(licPais)
      
      const idDocTipoValue = normalizeString(idDocTipoRaw || '')
      const idDocNumeroValue = normalizeString(idDocNumeroRaw || '')
      const licTipoValue = normalizeString(licTipoRaw || '')
      const licNumeroValue = normalizeString(licNumeroRaw || '')
      const licPaisValue = normalizeString(licPaisRaw || '').toUpperCase()
      
      console.log('🔍 Valores de campos de médico (raw):', {
        idDocTipoRaw,
        idDocNumeroRaw,
        licTipoRaw,
        licNumeroRaw,
        licPaisRaw,
      })
      
      console.log('🔍 Valores de campos de médico (normalized):', {
        idDocTipoValue,
        idDocNumeroValue,
        licTipoValue,
        licNumeroValue,
        licPaisValue,
      })
      
      if (!idDocTipoValue || !idDocNumeroValue || !licTipoValue || !licNumeroValue || !licPaisValue) {
        console.warn('⚠️ Validación fallida - campos faltantes:', {
          idDocTipoValue: !!idDocTipoValue,
          idDocNumeroValue: !!idDocNumeroValue,
          licTipoValue: !!licTipoValue,
          licNumeroValue: !!licNumeroValue,
          licPaisValue: !!licPaisValue,
        })
        
        const errors = {}
        if (!idDocTipoValue) errors.id_doc_tipo = 'El tipo de documento es obligatorio'
        if (!idDocNumeroValue) errors.id_doc_numero = 'El número de documento es obligatorio'
        if (!licTipoValue) errors.lic_tipo = 'El tipo de licencia es obligatorio'
        if (!licNumeroValue) errors.lic_numero = 'El número de licencia es obligatorio'
        if (!licPaisValue) errors.lic_pais = 'El país de expedición es obligatorio'
        
        helpers.setErrors(errors)
        
        // Scroll al campo con error
        const firstErrorField = Object.keys(errors)[0]
        const errorElement = document.querySelector(`[name="${firstErrorField}"]`)
        if (errorElement) {
          errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
          errorElement.focus()
        }
        
        return
      }
      
      Object.assign(payload, {
        id_doc_tipo: idDocTipoValue,
        id_doc_numero: idDocNumeroValue,
        lic_tipo: licTipoValue,
        lic_numero: licNumeroValue,
        lic_pais: licPaisValue,
      })
    } else {
      Object.assign(payload, {
        doc_tipo: emptyToNull(getFieldValue(docTipo)),
        doc_numero: emptyToNull(getFieldValue(docNumero)),
        birthdate: emptyToNull(getFieldValue(birthdate)),
        gender: emptyToNull(getFieldValue(gender)),
      })
    }

    // Debug: mostrar qué se está enviando (puedes quitar esto después)
    console.log('Enviando payload:', JSON.stringify(payload, null, 2))
    console.log('📡 Endpoint:', endpoint)

    const { data } = await api.post(endpoint, payload)
    
    console.log('✅ Respuesta del backend:', data)
    console.log('👤 Roles recibidos:', data?.roles)

    // Mostrar mensaje de éxito
    successMessage.value = `¡Bienvenido${roleValue === 'medico' ? ', especialista' : ''}! Tu cuenta ha sido creada exitosamente. Redirigiendo...`
    
    // Scroll al inicio para que el usuario vea el mensaje
    window.scrollTo({ top: 0, behavior: 'smooth' })

    // IMPORTANTE: Limpiar TODO el estado anterior antes de guardar el nuevo
    auth.clear()
    sessionStorage.removeItem('whoami_ok')
    sessionStorage.removeItem('whoami_token')
    
    // Limpiar también tokens viejos que puedan estar en localStorage
    ;['token', 'auth_token', 'access_token', 'user_name', 'roles'].forEach(k => {
      localStorage.removeItem(k)
      sessionStorage.removeItem(k)
    })

    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`

    // Obtener el nombre y roles del usuario desde la respuesta
    const userName = data?.user?.name || ''
    const userRoles = Array.isArray(data.roles) ? data.roles : []

    // Guardar la nueva sesión en el store centralizado
    auth.persistSession({
      token: data.token,
      roles: userRoles,
      name: userName,
      remember: true,
    })
    
    // IMPORTANTE: Marcar el cache como válido INMEDIATAMENTE después del registro
    // para evitar que el router guard lo sobrescriba
    sessionStorage.setItem('whoami_ok', '1')
    sessionStorage.setItem('whoami_token', data.token)
    
    console.log('💾 Token guardado después del registro:', data.token ? 'Sí' : 'No')
    console.log('💾 Nombre guardado:', userName)
    console.log('💾 Roles guardados:', userRoles)
    console.log('💾 Cache de usuario marcado como válido')

    // Esperar 2 segundos para que el usuario vea el mensaje de éxito antes de redirigir
    await new Promise(resolve => setTimeout(resolve, 2000))

    // Verificar roles después de guardarlos
    const savedRoles = auth.roles || []
    const isMedico = Array.isArray(savedRoles) && savedRoles.includes('medico')
    
    console.log('🔐 Roles guardados:', savedRoles)
    console.log('👨‍⚕️ Es médico?', isMedico)
    
    if (isMedico) {
      await router.replace({ name: 'medico.home' })
    } else {
      await router.replace({ name: 'paciente.home' })
    }
  } catch (error) {
    successMessage.value = '' // Limpiar mensaje de éxito si hay error
    const response = error?.response?.data
    console.error('Error del backend:', response)
    console.error('Payload enviado:', payload)
    
    if (response?.errors) {
      // Mapear errores del backend a los campos del formulario
      const mappedErrors = {}
      Object.keys(response.errors).forEach(key => {
        mappedErrors[key] = Array.isArray(response.errors[key]) 
          ? response.errors[key][0] 
          : String(response.errors[key])
      })
      helpers.setErrors(mappedErrors)
    } else {
      setErrors({ email: 'No pudimos registrar la cuenta en este momento.' })
    }
    backendIssue.value = {
      variant: 'danger',
      title: 'Algo no salió como esperábamos',
      body: response?.message ?? 'Revísalo y vuelve a intentarlo. Si el problema persiste, contáctanos.',
      list: response?.errors
        ? Object.entries(response.errors).map(([field, messages]) => ({
            id: field,
            text: Array.isArray(messages) ? messages[0] : String(messages),
          }))
        : null,
    }
  }
})
</script>

<style scoped>
/* Contenedor principal - estilo Doctoralia */
.doctoralia-auth {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background: #ffffff;
}

/* Logo */
.auth-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
  cursor: pointer;
}

.auth-logo__icon {
  font-size: 1.75rem;
}

.auth-logo__text {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00f5ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Contenedor del formulario */
.auth-container {
  width: 100%;
  max-width: 480px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.auth-title {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  text-align: center;
}

/* Selector de rol (cards estilo Doctoralia) */
.auth-role-selector {
  display: grid;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.auth-role-option {
  display: block;
  cursor: pointer;
}

.auth-role-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.auth-role-card {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
  transition: all 0.2s ease;
}

.auth-role-option.is-selected .auth-role-card {
  border-color: #8338ec;
  background: rgba(131, 56, 236, 0.05);
}

.auth-role-option:hover .auth-role-card {
  border-color: #d1d5db;
}

.auth-role-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.auth-role-content h3 {
  margin: 0 0 0.25rem;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.auth-role-content p {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.5;
}

.auth-role-notice {
  margin-top: 1rem;
  padding: 0.875rem 1rem;
  background: rgba(131, 56, 236, 0.08);
  border: 1px solid rgba(131, 56, 236, 0.2);
  border-radius: 8px;
}

.auth-role-notice p {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.5;
}

.auth-step-header {
  margin-bottom: 1rem;
}

.auth-step-title {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.auth-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

/* Campos */
.auth-step {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.auth-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.auth-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  font-size: 1rem;
  color: #111827;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.auth-input::placeholder {
  color: #9ca3af;
}

.auth-input:focus {
  outline: none;
  border-color: #8338ec;
  box-shadow: 0 0 0 3px rgba(131, 56, 236, 0.1);
}

.auth-input[type="date"],
.auth-input[type="select"],
select.auth-input {
  cursor: pointer;
}

.auth-error {
  margin: 0;
  font-size: 0.875rem;
  color: #dc2626;
}

/* Mensajes de éxito */
.auth-message {
  margin: 1.5rem 0;
  padding: 1.25rem 1rem;
  border-radius: 8px;
  font-size: 0.95rem;
  text-align: center;
  animation: slideDown 0.3s ease-out;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.auth-message--success {
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.15), rgba(34, 197, 94, 0.1));
  color: #15803d;
  border: 2px solid rgba(22, 163, 74, 0.3);
}

.auth-message--success strong {
  display: block;
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #15803d;
}

.auth-message--success p {
  margin: 0;
  line-height: 1.6;
  font-weight: 500;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-hint-text {
  margin: 0 0 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.auth-checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  cursor: pointer;
}

.auth-checkbox-label input {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

/* Navegación de pasos */
.auth-steps-nav {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.auth-btn-secondary {
  flex: 1;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #111827;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.auth-btn-secondary:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

.auth-btn-primary {
  flex: 1;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  color: #ffffff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.auth-btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(131, 56, 236, 0.4);
}

/* Botón de envío */
.auth-submit {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  color: #ffffff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.auth-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(131, 56, 236, 0.4);
}

.auth-submit:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Footer */
.auth-footer {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.auth-link {
  color: #8338ec;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}

.auth-link:hover {
  color: #ff006e;
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 600px) {
  .doctoralia-auth {
    padding: 1.5rem 1rem;
  }

  .auth-logo {
    margin-bottom: 1.5rem;
  }

  .auth-title {
    font-size: 1.5rem;
  }

  .auth-role-card {
    padding: 1rem;
  }
}
</style>
