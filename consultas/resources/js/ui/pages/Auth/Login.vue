<template>
  <AuthLayout
    title=""
    subtitle=""
    :context-message="layoutMessage"
    :footer-copy="'¿Necesitas ayuda? Escríbenos a soporte@medireserva.com'"
    :show-aside="false"
    variant="light"
  >
    
    <template #brand>
      <div class="login-brand">
        <span class="login-brand__logo">MediReserva</span>
        <p class="login-brand__tagline">La plataforma que conecta tu consultorio con tus pacientes en segundos.</p>
      </div>
    </template>

    <div class="login-layout">
      <section class="login-hero">
        <span class="login-hero__badge">Suite profesional</span>
        <h2 class="login-hero__title">Gestiona tus citas con la misma experiencia de tu landing principal</h2>
        <p class="login-hero__lead">
          Controla agendas, recordatorios y la comunicación con tus pacientes sin perder el estilo vibrante de MediReserva.
        </p>

        <div class="login-hero__cards">
          <article class="login-info-card">
            <h3>Agenda inteligente</h3>
            <p>Recibe confirmaciones automáticas y reduce cancelaciones de último minuto.</p>
          </article>

          <article class="login-info-card">
            <h3>Seguimiento clínico</h3>
            <p>Accede al historial de cada paciente y comparte indicaciones con tu equipo.</p>
          </article>
          <article class="login-info-card">
            <h3>Experiencia unificada</h3>
            <p>Un inicio de sesión coherente con el resto de tu proyecto y tus colores distintivos.</p>
          </article>
        </div>

        <RouterLink class="login-hero__cta" :to="{ name: 'register.paciente', query: { email: email.value } }">
          Crear una cuenta para mi consultorio
        </RouterLink>
      </section>
      <form @submit="onSubmit" class="login-card">
        <header class="login-card__header">
          <h1>Accede a tu cuenta</h1>
          <p>Utiliza tus credenciales para continuar organizando a tus pacientes.</p>
  
        </header>
        <div class="login-card__providers">
          <button class="login-provider" type="button">
            <span class="login-provider__icon" aria-hidden="true">G</span>
            Continuar con Google
          </button>
          <button class="login-provider" type="button">
            <span class="login-provider__icon" aria-hidden="true"></span>
            Continuar con Apple
          </button>
        </div>

        <div class="login-divider" role="presentation">
          <span>o accede con tu correo electrónico</span>
        </div>

        <section class="login-preferences">
          <button type="button" class="login-toggle" @click="toggleStepMode">
            <span class="login-toggle__state">
              {{ stepMode ? 'Modo guiado activo' : 'Modo formulario rápido' }}
            </span>
            <span class="login-toggle__hint">
              {{
                stepMode
                  ? 'Completa tu correo y luego tu contraseña en dos pasos.'
                  : 'Introduce tus datos en un solo paso.'
              }}
            </span>
          </button>
          <label class="login-remember">
            <input type="checkbox" v-model="remember" /> Mantener mi sesión activa
          </label>
        </section>

      <div v-if="stepMode" class="login-stepper" role="presentation">
          <div class="login-stepper__track">
            <span
              v-for="(step, index) in totalSteps"
              :key="step"
              :class="['login-stepper__dot', { 'is-active': index <= currentStep }]"
            ></span>
          </div>
          <span class="login-stepper__copy">{{ stepCopy }}</span>
        </div>

        <div v-if="!stepMode || currentStep === 0" class="login-field">
          <label class="login-label" for="login-email">Correo electrónico</label>
          <input
            id="login-email"
            ref="emailInput"
            v-model="email.value"
            @blur="email.handleBlur"
            type="email"
            autocomplete="email"
            class="login-input"
            placeholder="tucorreo@dominio.com"
          />
          <p v-if="email.errorMessage" class="login-error">{{ email.errorMessage }}</p>
        </div>

        <div v-if="!stepMode || currentStep === 1" class="login-field">
          <div class="login-field__header">
            <label class="login-label" for="login-password">Contraseña</label>
            <RouterLink class="login-link" :to="{ name: 'forgot-password', query: { email: email.value } }">
              ¿Olvidaste tu contraseña?
            </RouterLink>
          </div>
          <input
            id="login-password"
            v-model="password.value"
            @blur="password.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            class="login-input"
            placeholder="Ingresa tu contraseña"
          />
          <p v-if="password.errorMessage" class="auth-error">{{ password.errorMessage }}</p>
          <label class="auth-password-toggle">
            <input type="login-checkbox" v-model="showPassword" /> Mostrar contraseña
          </label>
        </div>
      

        <button class="login-submit" type="submit" :disabled="isSubmitting">
          <span v-if="isSubmitting">Accediendo…</span>
          <span v-else>Iniciar sesión</span>
        </button>

      <p class="login-footer">
          ¿Aún no tienes cuenta?
          <RouterLink class="login-link" :to="{ name: 'register.paciente', query: { email: email.value } }">
            Crear una cuenta gratuita
          </RouterLink>
        </p>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AuthLayout from '../../layouts/AuthLayout.vue'
import { useForm, useField, defineRule } from '@vee-validate/core'
import { required, email as emailRule, min } from '@vee-validate/rules'
import api from '../../../auth/api'
import { auth } from '../../../auth/store'

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

const onSubmit = handleSubmit(async (values, { setErrors: assignErrors }) => {
  backendIssue.value = null
  const canContinue = await nextStep()
  if (!canContinue) return

  try {
    const { data } = await api.post('/auth/login', {
      email: values.email,
      password: values.password,
      remember: remember.value,
    })

    if (!api.defaults.headers.common) api.defaults.headers.common = {}
    api.defaults.headers.common.Authorization = `Bearer ${data.token}`
    auth.token = data.token
    auth.roles = Array.isArray(data.roles) ? data.roles : []
    auth.name = data?.user?.name ?? ''

    if (remember.value) {
      localStorage.setItem('token', data.token)
    } else {
      sessionStorage.setItem('token', data.token)
    }

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
.login-layout {
  display: grid;
  gap: 3rem;
  align-items: start;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}

.login-brand {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: flex-start;
}

.login-brand__logo {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1b103a;
}

.login-brand__tagline {
  color: #475569;
  font-size: 0.95rem;
  max-width: 28rem;
}

.login-hero {
  background: linear-gradient(135deg, rgba(131, 56, 236, 0.08), rgba(255, 0, 110, 0.1));
  border: 1px solid rgba(131, 56, 236, 0.15);
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.login-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.login-hero__title {
  font-size: 2rem;
  line-height: 1.2;
  margin: 0;
  color: #111827;
}

.login-hero__lead {
  margin: 0;
  color: #475569;
  font-size: 1rem;
  line-height: 1.6;
}

.login-hero__cards {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.login-info-card {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.login-info-card h3 {
  margin: 0 0 0.5rem;
  font-size: 1rem;
  color: #1f2937;
}

.login-info-card p {
  margin: 0;
  font-size: 0.95rem;
  color: #475569;
  line-height: 1.5;
}

.login-hero__cta {
  align-self: flex-start;
  background: #fff;
  border: 1px solid rgba(131, 56, 236, 0.4);
  padding: 0.75rem 1.5rem;
  border-radius: 999px;
  font-weight: 600;
  color: #7c3aed;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.login-hero__cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(124, 58, 237, 0.25);
}

.login-card {
  background: #ffffff;
  border-radius: 24px;
  border: 1px solid rgba(226, 232, 240, 0.9);
  padding: 2.5rem;
  box-shadow: 0 25px 45px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.login-card__header h1 {
  margin: 0;
  font-size: 1.75rem;
  color: #111827;
}

.login-card__header p {
  margin: 0.5rem 0 0;
  color: #475569;
  font-size: 0.95rem;
}

.login-card__providers {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.login-provider {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.6);
  background: #f8fafc;
  color: #0f172a;
  font-weight: 600;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  justify-content: center;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.login-provider:hover {
  border-color: #7c3aed;
  box-shadow: 0 8px 20px rgba(124, 58, 237, 0.15);
}

.login-provider__icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 1.1rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  color: #fff;
}

.login-divider {
  display: flex;
  align-items: center;
  text-align: center;
  color: #94a3b8;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
}

.login-divider::before,
.login-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(148, 163, 184, 0.4);
}

.login-divider span {
  padding: 0 1rem;
}

.login-preferences {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.login-toggle {
  text-align: left;
  background: rgba(124, 58, 237, 0.08);
  border: 1px solid rgba(124, 58, 237, 0.25);
  border-radius: 16px;
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  color: #1f2937;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.login-toggle:hover {
  border-color: rgba(124, 58, 237, 0.5);
  box-shadow: 0 12px 24px rgba(124, 58, 237, 0.18);
}

.login-toggle__state {
  font-weight: 600;
  font-size: 0.95rem;
}

.login-toggle__hint {
  font-size: 0.85rem;
  color: #475569;
}

.login-remember {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #334155;
}

.login-remember input {
  width: 16px;
  height: 16px;
}

.login-stepper {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: #475569;
  font-size: 0.9rem;
}

.login-stepper__track {
  display: flex;
  gap: 0.5rem;
}

.login-stepper__dot {
  width: 100%;
  height: 6px;
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.4);
  transition: background 0.2s ease;
}

.login-stepper__dot.is-active {
  background: linear-gradient(135deg, #ff006e, #8338ec);
}

.login-stepper__copy {
  font-weight: 500;
}

.login-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.login-field__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.login-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.login-input {
  padding: 0.85rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.65);
  background: #f8fafc;
  font-size: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.login-input:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.15);
}

.login-error {
  margin: 0;
  font-size: 0.85rem;
  color: #dc2626;
}

.login-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #475569;
}

.login-checkbox input {
  width: 16px;
  height: 16px;
}

.login-link {
  color: #7c3aed;
  font-weight: 600;
}

.login-link:hover {
  text-decoration: underline;
}

.login-submit {
  background: linear-gradient(135deg, #ff006e, #8338ec);
  border: none;
  border-radius: 16px;
  padding: 0.9rem 1.25rem;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.login-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 15px 30px rgba(131, 56, 236, 0.35);
}

.login-submit:disabled {
  cursor: not-allowed;
  filter: grayscale(0.4);
  opacity: 0.7;
  box-shadow: none;
}

.login-footer {
  text-align: center;
  color: #475569;
  font-size: 0.95rem;
}

@media (max-width: 960px) {
  .login-layout {
    gap: 2rem;
  }

  .login-hero {
    padding: 2rem;
  }

  .login-card {
    padding: 2rem;
  }
}

@media (max-width: 600px) {
  .login-card {
    padding: 1.75rem;
  }

  .login-card__providers {
    flex-direction: column;
  }
}
</style>