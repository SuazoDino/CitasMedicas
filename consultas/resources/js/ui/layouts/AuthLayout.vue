<template>
  <div class="auth-stage" :data-variant="resolvedTheme">
    <div class="auth-stage__halo" aria-hidden="true"></div>

    <div :class="layoutClasses">
      <aside v-if="showAside" class="auth-stage__visual">
        <slot name="aside">
          <section class="auth-hero auth-hero--default">
            <header class="auth-hero__intro">
              <span class="auth-hero__badge">Experiencia MediReserva</span>
              <h2 class="auth-hero__headline">Organiza tu consulta en tiempo real</h2>
              <p class="auth-hero__lead">
                Conecta agendas, recordatorios y seguimiento clínico en una sola plataforma con la claridad que esperas
                en escritorio.
              </p>
            </header>

            <ul class="auth-flow" aria-label="Beneficios de MediReserva">
              <li class="auth-flow__step">
                <span class="auth-flow__indicator" aria-hidden="true">01</span>
                <div class="auth-flow__content">
                  <h3 class="auth-flow__title">Sincroniza tu equipo</h3>
                  <p class="auth-flow__copy">Comparte tu agenda con asistentes y especialistas sin perder control.</p>
                </div>
              </li>
              <li class="auth-flow__step">
                <span class="auth-flow__indicator" aria-hidden="true">02</span>
                <div class="auth-flow__content">
                  <h3 class="auth-flow__title">Recordatorios inteligentes</h3>
                  <p class="auth-flow__copy">Automatiza confirmaciones y avisos a pacientes según tus reglas.</p>
                </div>
              </li>
              <li class="auth-flow__step">
                <span class="auth-flow__indicator" aria-hidden="true">03</span>
                <div class="auth-flow__content">
                  <h3 class="auth-flow__title">Panel clínico unificado</h3>
                  <p class="auth-flow__copy">Consulta historial, notas y pagos desde una interfaz preparada para tu equipo.</p>
                </div>
              </li>
            </ul>

            <footer class="auth-hero__footer">
              <p class="auth-hint">¿Necesitas ayuda? <a href="mailto:soporte@medireserva.com">soporte@medireserva.com</a></p>
              <a class="auth-hero__cta" href="mailto:soporte@medireserva.com">Habla con nosotros</a>
            </footer>
          </section>
        </slot>
      </aside>

      <main class="auth-stage__panel">
        <header class="auth-header">
          <slot name="brand">
            <div class="auth-header__brand">
              <span class="auth-header__logo">MediReserva</span>
              <p class="auth-header__tagline">
                Coordina tus citas con una plataforma hecha para consultorios modernos.
              </p>
            </div>
          </slot>
        </header>

        <section class="auth-panel">
          <header class="auth-panel__intro">
            <p v-if="eyebrow" class="auth-panel__eyebrow">{{ eyebrow }}</p>
            <h1 class="auth-panel__title">{{ title }}</h1>
            <p v-if="subtitle" class="auth-panel__subtitle">{{ subtitle }}</p>
          </header>

          <div v-if="messageBody" class="auth-panel__notice" :class="messageClass">
            <strong v-if="messageTitle">{{ messageTitle }}</strong>
            <p>{{ messageBody }}</p>
            <ul v-if="messageList?.length" class="auth-feedback">
              <li v-for="item in messageList" :key="item.id" class="auth-feedback__item">
                <span aria-hidden="true">•</span>
                <span>{{ item.text }}</span>
              </li>
            </ul>
          </div>

          <div class="auth-panel__body">
            <slot />
          </div>

          <footer v-if="$slots.footer || footerCopy" class="auth-panel__footer">
            <slot name="footer">
              <span v-if="footerCopy">{{ footerCopy }}</span>
            </slot>
          </footer>
        </section>

      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import '../../../css/theme.css'
import '../../../css/auth.css'

const props = defineProps({
  title: { type: String, default: 'Bienvenido a MediReserva' },
  subtitle: {
    type: String,
    default: 'Gestiona tus citas, pacientes y recordatorios desde un panel intuitivo y consistente con tu marca.',
  },
  eyebrow: { type: String, default: 'MediReserva ID' },
  variant: { type: String, default: 'brand' },
  contextMessage: { type: Object, default: null },
  showAside: { type: Boolean, default: true },
  footerCopy: { type: String, default: '' },
})

const resolvedTheme = computed(() => (props.variant === 'light' ? 'light' : 'brand'))

const layoutClasses = computed(() => {
  const classes = ['auth-stage__wrap']
  if (props.showAside) {
    classes.push('auth-stage__wrap--with-aside')
  } else {
    classes.push('auth-stage__wrap--single')
  }
  return classes
})

const messageVariant = computed(() => props.contextMessage?.variant ?? 'info')
const messageTitle = computed(() => props.contextMessage?.title ?? null)
const messageBody = computed(() => props.contextMessage?.body ?? props.contextMessage?.message ?? '')
const messageList = computed(() => props.contextMessage?.list ?? null)

const messageClass = computed(() => {
  if (messageVariant.value === 'success') return 'is-success'
  if (messageVariant.value === 'danger' || messageVariant.value === 'error') return 'is-danger'
  return 'is-info'
})

defineExpose({ resolvedTheme })
</script>

<style scoped>
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}
</style>
