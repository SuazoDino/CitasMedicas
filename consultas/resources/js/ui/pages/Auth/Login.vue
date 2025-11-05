<template>
  <AuthLayout
    title="Inicia sesión"
    subtitle="Ingresa con tu correo electrónico para retomar tus citas y mantener al dia a tus pacientes."
    :context-message="layoutMessage"
    :footer-copy="'¿Necesitas ayuda? Escríbenos a soporte@medireserva.com'"
    :show-aside="true"
  >
    
    <template #aside>
      <section class="auth-hero auth-hero--flow">
        <div class="auth-hero__intro">
          <span class="auth-hero__badge">MediReserva ID</span>
          <h2 class="auth-hero__headline">Tu agenda conectada en minutos</h2>
          <p class="auth-hero__lead">
            Mantén a tu equipo y pacientes sincronizados desde cualquier dispositivo. Disfruta la misma experiencia fluida
            que en el resto de MediReserva, ahora enfocada en un acceso más claro.
          </p>
        </div>
         <ol class="auth-flow" aria-label="Cómo funciona el acceso a MediReserva">
          <li class="auth-flow__step">
            <span class="auth-flow__indicator" aria-hidden="true">01</span>
            <div class="auth-flow__content">
              <h3 class="auth-flow__title">Confirma tus datos</h3>
              <p class="auth-flow__copy">
                Verificamos tu correo para mantener la seguridad sin añadir fricción innecesaria.
              </p>
            </div>
          </li>
          <li class="auth-flow__step">
            <span class="auth-flow__indicator" aria-hidden="true">02</span>
            <div class="auth-flow__content">
              <h3 class="auth-flow__title">Protege tu acceso</h3>
              <p class="auth-flow__copy">
                Activa el modo guiado cuando quieras validar cada paso con recomendaciones inteligentes.
              </p>
            </div>
          </li>
          <li class="auth-flow__step">
            <span class="auth-flow__indicator" aria-hidden="true">03</span>
            <div class="auth-flow__content">
              <h3 class="auth-flow__title">Organiza sin distracciones</h3>
              <p class="auth-flow__copy">
                Accede directo a tu panel clínico, sincroniza calendarios y da seguimiento a tus pacientes.
              </p>
            </div>
          </li>
        </ol>

        <div class="auth-hero__footer auth-hero__footer--cta">
          <p class="auth-hint">
            ¿Necesitas ayuda? <a href="mailto:soporte@medireserva.com">soporte@medireserva.com</a>
          </p>
          <RouterLink class="auth-hero__cta" :to="{ name: 'register.paciente', query: { email: email.value } }">
            Crear cuenta para mi consultorio
          </RouterLink>
        </div>
        
        </section>
    </template>

    <form @submit="onSubmit" class="auth-form__stack">
      <section class="auth-fieldset auth-fieldset--options">
        <header class="auth-fieldset__header">
          <h2>Personaliza tu acceso</h2>
          <p>Elige si prefieres un flujo guiado o ingresar tus datos en un solo paso.</p>
        </header>
        <div class="auth-preferences">
          <button type="button" class="auth-preferences__toggle" @click="toggleStepMode">
            <span class="auth-preferences__state">
              {{ stepMode ? 'Guía paso a paso activa' : 'Formulario rápido' }}
            </span>
            <span class="auth-preferences__hint">
              {{ stepMode ? 'Completa tus datos en dos pasos seguros.' : 'Ingresa correo y contraseña al mismo tiempo.' }}
            </span>
          </button>
          <label class="auth-preferences__remember">
            <input type="checkbox" v-model="remember" /> Mantener mi sesión activa
          </label>
        </div>
      </section>

      <section class="auth-fieldset auth-fieldset--form">
        <header class="auth-fieldset__header">
          <h2>Datos de acceso</h2>
          <p>Ingresa la información con la que te registraste en MediReserva.</p>
        </header>

        <div class="auth-form__steps" v-if="stepMode">
          <div class="auth-steps-bar" role="presentation">
            <span v-for="(step, index) in totalSteps" :key="step" :class="{ 'is-active': index <= currentStep }"></span>
          </div>
          <span>{{ stepCopy }}</span>
        </div>

        <div v-if="!stepMode || currentStep === 0" class="auth-field">
          <label class="auth-label" for="login-email">
            Correo electrónico
          </label>
          <input
            id="login-email"
            ref="emailInput"
            v-model="email.value"
            @blur="email.handleBlur"
            type="email"
            autocomplete="email"
            class="auth-input"
            placeholder="tucorreo@dominio.com"
          />
          <p v-if="email.errorMessage" class="auth-error">{{ email.errorMessage }}</p>
        </div>

        <div v-if="!stepMode || currentStep === 1" class="auth-field auth-field--password">
          <div class="auth-field__header">
            <label class="auth-label" for="login-password">Contraseña</label>
            <RouterLink class="auth-secondary-link" :to="{ name: 'forgot-password', query: { email: email.value } }">
              ¿Olvidaste tu contraseña?
            </RouterLink>
          </div>
          <input
            id="login-password"
            v-model="password.value"
            @blur="password.handleBlur"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            class="auth-input"
            placeholder="Ingresa tu contraseña"
          />
          <p v-if="password.errorMessage" class="auth-error">{{ password.errorMessage }}</p>
          <label class="auth-password-toggle">
            <input type="checkbox" v-model="showPassword" /> Mostrar contraseña
          </label>
        </div>
      </section>

      <button class="auth-button" type="submit" :disabled="isSubmitting">
        <span v-if="isSubmitting">Accediendo…</span>
        <span v-else>Entrar a mi cuenta</span>
      </button>

      <p class="auth-hint auth-hint--center">
        ¿Primera vez aquí?
        <RouterLink class="auth-secondary-link" :to="{ name: 'register.paciente', query: { email: email.value } }">
          Crear una cuenta gratuita
        </RouterLink>
      </p>
    </form>
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