<template>
  <AuthLayout
    :title="roleLabel === 'medico' ? 'Crea tu perfil profesional' : 'Abre tu cuenta de paciente'"
    subtitle="Completa la información para personalizar tu experiencia dentro de MediReserva."
    :context-message="layoutMessage"
    :show-aside="true"
  >
    <form @submit="onSubmit">
      <div class="auth-form__steps" v-if="stepMode">
        <div class="auth-steps-bar" role="presentation">
          <span
            v-for="(step, index) in steps"
            :key="step.id"
            :class="{ 'is-active': index <= currentStep }"
          ></span>
        </div>
        <span>{{ stepCopy }}</span>
      </div>

      <section v-if="!stepMode || currentStep === 0" class="auth-fieldset">
        <header class="auth-field">
          <label class="auth-label">¿Cómo quieres registrarte?</label>
          <div class="auth-actions-secondary" style="justify-content:flex-start; gap:1rem;">
            <label class="auth-hint" style="display:flex; align-items:center; gap:0.5rem;">
              <input type="radio" value="paciente" v-model="role.value" /> Soy paciente
            </label>
            <label class="auth-hint" style="display:flex; align-items:center; gap:0.5rem;">
              <input type="radio" value="medico" v-model="role.value" /> Soy profesional de la salud
            </label>
          </div>
          <p v-if="role.errorMessage" class="auth-error">{{ role.errorMessage }}</p>
        </header>

        <div class="auth-field">
          <label class="auth-label" for="reg-full-name">Nombre completo</label>
          <input
            id="reg-full-name"
            v-model="fullName.value"
            @blur="fullName.handleBlur"
            type="text"
            autocomplete="name"
            class="auth-input"
            placeholder="Ej. Daniela Rodríguez"
          />
          <p v-if="fullName.errorMessage" class="auth-error">{{ fullName.errorMessage }}</p>
        </div>

        <div class="auth-field">
          <label class="auth-label" for="reg-email">Correo electrónico</label>
          <input
            id="reg-email"
            v-model="email.value"
            @blur="email.handleBlur"
            type="email"
            autocomplete="email"
            class="auth-input"
            placeholder="daniela@ejemplo.com"
          />
          <p v-if="email.errorMessage" class="auth-error">{{ email.errorMessage }}</p>
        </div>
      </section>

      <section v-if="!stepMode || currentStep === 1" class="auth-fieldset">
        <div class="auth-field">
          <label class="auth-label" for="reg-phone">Teléfono de contacto (opcional)</label>
          <input
            id="reg-phone"
            v-model="phone.value"
            @blur="phone.handleBlur"
            type="tel"
            autocomplete="tel"
            class="auth-input"
            placeholder="+51 900 000 000"
          />
          <p v-if="phone.errorMessage" class="auth-error">{{ phone.errorMessage }}</p>
        </div>

        <div class="auth-field">
          <label class="auth-label" for="reg-password">Crea una contraseña</label>
          <input
            id="reg-password"
            v-model="password.value"
            @blur="password.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            class="auth-input"
            placeholder="Mínimo 6 caracteres"
          />
          <p v-if="password.errorMessage" class="auth-error">{{ password.errorMessage }}</p>
        </div>

        <div class="auth-field">
          <label class="auth-label" for="reg-password-confirm">Repite la contraseña</label>
          <input
            id="reg-password-confirm"
            v-model="passwordConfirmation.value"
            @blur="passwordConfirmation.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            class="auth-input"
            placeholder="Confirma tu contraseña"
          />
          <p v-if="passwordConfirmation.errorMessage" class="auth-error">{{ passwordConfirmation.errorMessage }}</p>
          <label class="auth-hint" style="display:flex; align-items:center; gap:0.5rem; font-weight:600;">
            <input type="checkbox" v-model="showPassword" /> Mostrar contraseñas
          </label>
        </div>
      </section>

      <section v-if="!stepMode || currentStep === 2" class="auth-fieldset">
        <template v-if="roleLabel === 'medico'">
          <p class="auth-hint">Confirma tus credenciales para validar tu cuenta profesional.</p>
          <div class="auth-field">
            <label class="auth-label" for="reg-id-doc-tipo">Tipo de documento de identidad</label>
            <input
              id="reg-id-doc-tipo"
              v-model="idDocTipo.value"
              @blur="idDocTipo.handleBlur"
              type="text"
              class="auth-input"
              placeholder="DNI / CE / Pasaporte"
            />
            <p v-if="idDocTipo.errorMessage" class="auth-error">{{ idDocTipo.errorMessage }}</p>
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-id-doc-numero">Número de documento</label>
            <input
              id="reg-id-doc-numero"
              v-model="idDocNumero.value"
              @blur="idDocNumero.handleBlur"
              type="text"
              class="auth-input"
              placeholder="Ej. 12345678"
            />
            <p v-if="idDocNumero.errorMessage" class="auth-error">{{ idDocNumero.errorMessage }}</p>
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-lic-tipo">Tipo de licencia profesional</label>
            <input
              id="reg-lic-tipo"
              v-model="licTipo.value"
              @blur="licTipo.handleBlur"
              type="text"
              class="auth-input"
              placeholder="CMP / COP / Registro"
            />
            <p v-if="licTipo.errorMessage" class="auth-error">{{ licTipo.errorMessage }}</p>
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-lic-numero">Número de licencia</label>
            <input
              id="reg-lic-numero"
              v-model="licNumero.value"
              @blur="licNumero.handleBlur"
              type="text"
              class="auth-input"
              placeholder="Ej. 12345"
            />
            <p v-if="licNumero.errorMessage" class="auth-error">{{ licNumero.errorMessage }}</p>
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-lic-pais">País de expedición</label>
            <input
              id="reg-lic-pais"
              v-model="licPais.value"
              @blur="licPais.handleBlur"
              type="text"
              maxlength="4"
              class="auth-input"
              placeholder="ISO (Ej. PE)"
            />
            <p v-if="licPais.errorMessage" class="auth-error">{{ licPais.errorMessage }}</p>
          </div>
        </template>
        <template v-else>
          <p class="auth-hint">Estos datos nos ayudan a personalizar tus citas (opcional).</p>
          <div class="auth-field">
            <label class="auth-label" for="reg-doc-tipo">Tipo de documento (opcional)</label>
            <input
              id="reg-doc-tipo"
              v-model="docTipo.value"
              @blur="docTipo.handleBlur"
              type="text"
              class="auth-input"
              placeholder="DNI / CE"
            />
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-doc-numero">Número de documento (opcional)</label>
            <input
              id="reg-doc-numero"
              v-model="docNumero.value"
              @blur="docNumero.handleBlur"
              type="text"
              class="auth-input"
              placeholder="Ingresa solo números"
            />
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-birthdate">Fecha de nacimiento (opcional)</label>
            <input
              id="reg-birthdate"
              v-model="birthdate.value"
              @blur="birthdate.handleBlur"
              type="date"
              class="auth-input"
            />
          </div>
          <div class="auth-field">
            <label class="auth-label" for="reg-gender">Género (opcional)</label>
            <select id="reg-gender" v-model="gender.value" class="auth-select">
              <option value="">Seleccionar</option>
              <option value="femenino">Femenino</option>
              <option value="masculino">Masculino</option>
              <option value="no-binario">No binario</option>
              <option value="prefiero-no-decirlo">Prefiero no decirlo</option>
            </select>
          </div>
        </template>
      </section>

      <div class="auth-actions-secondary">
        <button type="button" class="auth-secondary-link" @click="toggleStepMode">
          {{ stepMode ? 'Ver todo en un solo paso' : 'Dividir en pasos guiados' }}
        </button>
        <RouterLink class="auth-secondary-link" :to="{ name: 'login', query: { email: email.value } }">
          ¿Ya tienes cuenta? Inicia sesión
        </RouterLink>
      </div>

      <div class="auth-actions-secondary" v-if="stepMode">
        <button
          v-if="currentStep > 0"
          type="button"
          class="auth-button auth-button--ghost"
          @click="previousStep"
        >
          Volver
        </button>
        <button
          v-if="currentStep < steps.length - 1"
          type="button"
          class="auth-button"
          @click="goToNextStep"
        >
          Continuar
        </button>
      </div>

      <button v-if="!stepMode || currentStep === steps.length - 1" class="auth-button" type="submit" :disabled="isSubmitting">
        <span v-if="isSubmitting">Creando tu cuenta…</span>
        <span v-else>Crear cuenta</span>
      </button>
    </form>
  </AuthLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthLayout from '../../layouts/AuthLayout.vue'
import { useForm, useField, defineRule } from '@vee-validate/core'
import { required, email as emailRule, min, max, confirmed, one_of as oneOf } from '@vee-validate/rules'
import api from '../../../auth/api'
import { auth } from '../../../auth/store'

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

const initialRole = typeof route.query.role === 'string' ? route.query.role : props.defaultRole

const { handleSubmit, isSubmitting, setErrors } = useForm({
  initialValues: {
    full_name: typeof route.query.name === 'string' ? route.query.name : '',
    email: typeof route.query.email === 'string' ? route.query.email : '',
    phone: '',
    role: initialRole,
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

const docTipo = useField('doc_tipo')
const docNumero = useField('doc_numero')
const birthdate = useField('birthdate')
const gender = useField('gender')

const idDocTipo = useField('id_doc_tipo', (value) => (roleLabel.value === 'medico' ? required(value) : true))
const idDocNumero = useField('id_doc_numero', (value) => (roleLabel.value === 'medico' ? required(value) : true))
const licTipo = useField('lic_tipo', (value) => (roleLabel.value === 'medico' ? required(value) : true))
const licNumero = useField('lic_numero', (value) => (roleLabel.value === 'medico' ? required(value) : true))
const licPais = useField('lic_pais', (value) => (roleLabel.value === 'medico' ? required(value) : true))

const stepMode = ref(true)
const currentStep = ref(0)

const roleLabel = computed(() => role.value.value || 'paciente')

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
  if (value === 'medico') {
    docTipo.setValue('')
    docNumero.setValue('')
  } else {
    idDocTipo.setValue('')
    idDocNumero.setValue('')
    licTipo.setValue('')
    licNumero.setValue('')
    licPais.setValue('')
  }
})

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

const onSubmit = handleSubmit(async (formValues, helpers) => {
  backendIssue.value = null

  if (stepMode.value && currentStep.value < steps.value.length - 1) {
    const ok = await validateStep(currentStep.value)
    if (ok) {
      currentStep.value += 1
    }
    return
  }

  try {
    const payload = {
      full_name: formValues.full_name,
      email: formValues.email,
      phone: formValues.phone || null,
      password: formValues.password,
      password_confirmation: formValues.password_confirmation,
      role: formValues.role,
    }

    let endpoint = '/auth/register/paciente'
    if (formValues.role === 'medico') {
      endpoint = '/auth/register/medico'
      Object.assign(payload, {
        id_doc_tipo: formValues.id_doc_tipo,
        id_doc_numero: formValues.id_doc_numero,
        lic_tipo: formValues.lic_tipo,
        lic_numero: formValues.lic_numero,
        lic_pais: formValues.lic_pais,
      })
    } else {
      Object.assign(payload, {
        doc_tipo: formValues.doc_tipo || null,
        doc_numero: formValues.doc_numero || null,
        birthdate: formValues.birthdate || null,
        gender: formValues.gender || null,
      })
    }

    const { data } = await api.post(endpoint, payload)

    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`

    auth.token = data.token
    auth.roles = Array.isArray(data.roles) ? data.roles : []
    auth.name = data?.user?.name ?? ''

    localStorage.setItem('token', data.token)

    if (auth.roles.includes('medico')) {
      await router.replace({ name: 'medico.home' })
    } else {
      await router.replace({ name: 'paciente.home' })
    }
  } catch (error) {
    const response = error?.response?.data
    if (response?.errors) {
      helpers.setErrors(response.errors)
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

const layoutMessage = computed(() => {
  if (backendIssue.value) return backendIssue.value
  return {
    variant: roleLabel.value === 'medico' ? 'info' : 'success',
    title: roleLabel.value === 'medico' ? 'Expande tu consulta digital' : 'Tu salud en un mismo lugar',
    body:
      roleLabel.value === 'medico'
        ? 'Completa tus datos de colegiatura para aparecer en los listados y recibir pacientes verificados.'
        : 'Comparte algunos datos opcionales para recibir recordatorios personalizados y recomendaciones.',
  }
})

const stepCopy = computed(() => {
  if (!stepMode.value) return ''
  const labels = {
    perfil: 'Paso 1 de 3 — Cuéntanos sobre ti',
    seguridad: 'Paso 2 de 3 — Configura tu acceso seguro',
    credenciales: 'Paso 3 de 3 — Verificamos tu ejercicio profesional',
    personalizacion: 'Paso 3 de 3 — Personaliza tus recordatorios',
  }
  return labels[steps.value[currentStep]?.id] ?? ''
})
</script>