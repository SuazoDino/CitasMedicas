<template>
  <div class="doctoralia-login">
    <!-- Logo centrado arriba -->
    <div class="login-logo">
      <span class="login-logo__icon">⚡</span>
      <span class="login-logo__text">MediReserva</span>
    </div>

    <!-- Formulario centrado -->
    <div class="login-container">
      <form @submit="onSubmit" class="login-form">
        <h1 class="login-title">Accede a tu cuenta</h1>

        <!-- Botones sociales -->
        <div class="login-providers">
          <button class="login-provider" type="button">
            <svg class="login-provider__icon" width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
              <path d="M9 18c2.43 0 4.467-.806 5.96-2.184l-2.908-2.258c-.806.54-1.837.86-3.052.86-2.347 0-4.33-1.585-5.04-3.716H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
              <path d="M3.96 10.714c-.18-.54-.282-1.117-.282-1.714s.102-1.174.282-1.714V4.954H.957C.348 6.174 0 7.55 0 9s.348 2.826.957 4.046l3.003-2.332z" fill="#FBBC05"/>
              <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.954L3.96 7.286C4.67 5.155 6.653 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Continuar con Google
          </button>
          <button class="login-provider" type="button">
            <svg class="login-provider__icon" width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
              <path d="M13.5 0C12.12 0 10.8.48 9.75 1.35L9 2.1l-.75-.75C7.2.48 5.88 0 4.5 0 2.01 0 0 2.01 0 4.5c0 1.8.9 3.39 2.25 4.35L9 18l6.75-9.15C17.1 7.89 18 6.3 18 4.5 18 2.01 15.99 0 13.5 0z"/>
            </svg>
            Continuar con Apple
          </button>
        </div>

        <!-- Separador -->
        <div class="login-divider">
          <span>o</span>
        </div>

        <!-- Campos de formulario -->
        <div v-if="!stepMode || currentStep === 0" class="login-field">
          <input
            id="login-email"
            ref="emailInput"
            :value="getFieldValue(email)"
            @input="email.setValue($event.target.value)"
            @blur="email.handleBlur"
            type="email"
            autocomplete="email"
            class="login-input"
            placeholder="Correo electrónico"
          />
          <p v-if="email.errorMessage" class="login-error">{{ email.errorMessage }}</p>
        </div>

        <div v-if="!stepMode || currentStep === 1" class="login-field">
          <input
            id="login-password"
            :value="getFieldValue(password)"
            @input="password.setValue($event.target.value)"
            @blur="password.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            class="login-input"
            placeholder="Contraseña"
          />
          <p v-if="password.errorMessage" class="login-error">{{ password.errorMessage }}</p>
        </div>

        <!-- Stepper (oculto visualmente pero funcional) -->
        <div v-if="stepMode" class="login-stepper" role="presentation">
          <div class="login-stepper__track">
            <span
              v-for="(step, index) in totalSteps"
              :key="step"
              :class="['login-stepper__dot', { 'is-active': index <= currentStep }]"
            ></span>
          </div>
        </div>

        <!-- Botón de envío -->
        <button class="login-submit" type="submit" :disabled="isSubmitting">
          <span v-if="isSubmitting">Accediendo…</span>
          <span v-else>Iniciar sesión</span>
        </button>

        <!-- Link olvidé contraseña -->
        <RouterLink class="login-forgot" :to="{ name: 'forgot-password', query: { email: getFieldValue(email) } }">
          He olvidado mi contraseña
        </RouterLink>

        <!-- Footer con registro -->
        <p class="login-footer">
          ¿Todavía sin cuenta?
          <RouterLink class="login-link" :to="{ name: 'register.paciente', query: { email: getFieldValue(email) } }">
            Quiero registrarme
          </RouterLink>
        </p>
      </form>

      <!-- Toggle modo paso (oculto pero funcional para mantener lógica) -->
      <div class="login-hidden-controls">
        <button type="button" class="login-toggle-hidden" @click="toggleStepMode" v-if="false">
          {{ stepMode ? 'Modo guiado' : 'Modo rápido' }}
        </button>
        <label class="login-remember-hidden" v-if="false">
          <input type="checkbox" v-model="remember" /> Mantener sesión
        </label>
        <label class="login-show-password-hidden" v-if="false">
          <input type="checkbox" v-model="showPassword" /> Mostrar contraseña
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted} from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useForm, useField, defineRule } from '@vee-validate/core'
import { required, email as emailRule, min } from '@vee-validate/rules'
import api from '../../../auth/api'
import { auth } from '../../../auth/store'
import { getFieldValue } from './getFieldValue'

defineRule('required', required)
defineRule('email', emailRule)
defineRule('min', min)

const router = useRouter()
const route = useRoute()

const backendIssue = ref(null)
const showPassword = ref(false)
const remember = ref(true)
const stepMode = ref(route.query.step === '1')
const currentStep = ref(0)
const totalSteps = ref(2)

const { handleSubmit, isSubmitting, setErrors } = useForm({
  initialValues: {
    email: typeof route.query.email === 'string' ? route.query.email : '',
    password: '',
  },
  validateOnInput: true,
  validateOnBlur: true,
})

const email = useField('email', 'required|email')
const password = useField('password', 'required|min:6')



const emailInput = ref(null)

onMounted(() => {
  if (emailInput.value && !email.value.value) {
    emailInput.value.focus()
  }
})

watch(stepMode, (value) => {
  if (!value) currentStep.value = 0
})

const toggleStepMode = () => {
  stepMode.value = !stepMode.value
  const nextQuery = { ...route.query }
  if (stepMode.value) {
    nextQuery.step = '1'
  } else {
    delete nextQuery.step
  }
  router.replace({ query: nextQuery })
}

const nextStep = async () => {
  if (!stepMode.value) return true
  if (currentStep.value === 0) {
    const result = await email.validate()
    if (!result.valid) {
      return false
    }
    currentStep.value = 1
    return false
  }
  return true
}

const normalizeString = (value) => (typeof value === 'string' ? value.trim() : '')

const onSubmit = handleSubmit(async (values, { setErrors: assignErrors }) => {
  backendIssue.value = null
  const canContinue = await nextStep()
  if (!canContinue) return

  try {
    const rememberChoice = remember.value === true
    const payload = {
      email: normalizeString(values.email),
      password: values.password,
      remember: rememberChoice,
    }

    const { data } = await api.post('/auth/login', {
      ...payload,
    })

    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`
    auth.persistSession({
      token: data.token,
      roles: Array.isArray(data.roles) ? data.roles : [],
      name: data?.user?.name ?? '',
      remember: rememberChoice,
    })

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null
    if (redirect) {
      await router.replace(redirect)
      return
    }

    if (auth.roles.includes('medico')) {
      await router.replace({ name: 'medico.home' })
    } else if (auth.roles.includes('paciente')) {
      await router.replace({ name: 'paciente.home' })
    } else {
      await router.replace({ name: 'landing' })
    }
  } catch (error) {
    const response = error?.response?.data
    if (response?.errors) {
      assignErrors(response.errors)
    } else {
      setErrors({ email: 'No pudimos conectar con el servidor. Inténtalo en unos minutos.' })
    }

    backendIssue.value = {
      variant: 'danger',
      title: 'No pudimos iniciar sesión',
      body: response?.message ?? 'Revisa tus credenciales e inténtalo nuevamente.',
      list: response?.hints
        ? Object.entries(response.hints).map(([id, text]) => ({ id, text }))
        : null,
    }
  }
})

const layoutMessage = computed(() => {
  if (backendIssue.value) return backendIssue.value
  if (route.query.registered === '1') {
    return {
      variant: 'success',
      title: '¡Tu cuenta está lista!',
      body: 'Solo inicia sesión con el correo que registraste para comenzar a agendar tus citas.',
    }
  }
  return {
    variant: 'info',
    title: 'Acceso seguro',
    body: 'Usa tu correo electrónico corporativo o personal. Si tienes problemas, recupera tu contraseña desde el enlace superior.',
  }
})

const stepCopy = computed(() => {
  if (!stepMode.value) return ''
  return currentStep.value === 0
    ? 'Paso 1 de 2 — Verificamos tu correo'
    : 'Paso 2 de 2 — Protegemos tu acceso'
})
</script>

<style scoped>
/* Contenedor principal - estilo Doctoralia */
.doctoralia-login {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background: #ffffff;
}

/* Logo centrado arriba */
.login-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 3rem;
  cursor: pointer;
}

.login-logo__icon {
  font-size: 1.75rem;
}

.login-logo__text {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00f5ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Contenedor del formulario */
.login-container {
  width: 100%;
  max-width: 420px;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Título */
.login-title {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  text-align: center;
}

/* Botones de proveedores sociales */
.login-providers {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.login-provider {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #111827;
  font-size: 0.95rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.login-provider:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

.login-provider__icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

/* Separador con "o" */
.login-divider {
  display: flex;
  align-items: center;
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0.5rem 0;
}

.login-divider::before,
.login-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

.login-divider span {
  padding: 0 1rem;
  background: #ffffff;
}

/* Campos de entrada */
.login-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.login-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  font-size: 1rem;
  color: #111827;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.login-input::placeholder {
  color: #9ca3af;
}

.login-input:focus {
  outline: none;
  border-color: #8338ec;
  box-shadow: 0 0 0 3px rgba(131, 56, 236, 0.1);
}

.login-error {
  margin: 0;
  font-size: 0.875rem;
  color: #dc2626;
}

/* Stepper (oculto visualmente) */
.login-stepper {
  display: none;
}

/* Botón de envío principal */
.login-submit {
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

.login-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(131, 56, 236, 0.4);
}

.login-submit:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Link "He olvidado mi contraseña" */
.login-forgot {
  text-align: center;
  color: #8338ec;
  font-size: 0.875rem;
  text-decoration: none;
  transition: color 0.2s ease;
}

.login-forgot:hover {
  color: #ff006e;
  text-decoration: underline;
}

/* Footer con registro */
.login-footer {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.login-link {
  color: #8338ec;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}

.login-link:hover {
  color: #ff006e;
  text-decoration: underline;
}

/* Controles ocultos (mantienen la lógica) */
.login-hidden-controls {
  display: none;
}

/* Responsive */
@media (max-width: 600px) {
  .doctoralia-login {
    padding: 1.5rem 1rem;
  }

  .login-logo {
    margin-bottom: 2rem;
  }

  .login-title {
    font-size: 1.5rem;
  }
}
</style>