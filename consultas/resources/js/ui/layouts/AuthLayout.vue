<template>
  <div class="auth-surface" :data-variant="resolvedTheme">
    <div class="auth-surface__glow" aria-hidden="true"></div>

    <div class="auth-shell">
      <header class="auth-shell__brand">
        <slot name="brand">
          <div class="auth-brand">
            <span class="auth-brand__logo">MediReserva</span>
            <p class="auth-brand__tagline">Coordina tus citas con una plataforma hecha para consultorios modernos.</p>
          </div>
        </slot>
      </header>

      <div class="auth-shell__content">
        <aside v-if="showAside" class="auth-hero">
          <slot name="aside">
            <div class="auth-hero__header">
            <span class="auth-hero__eyebrow">{{ eyebrow }}</span>
            <h2 class="auth-hero__title">Una experiencia pensada para tu consulta</h2>
          </div>

          <p class="auth-hero__copy">
            Mantén el control de tus agendas, envía recordatorios automáticos y ofrece una experiencia digital
            impecable a tus pacientes.
          </p>

          <ul class="auth-hero__highlights">
            <li v-for="item in asideBullets" :key="item.id">
              <span class="auth-hero__icon">{{ item.icon }}</span>
              <div>
                <strong>{{ item.title }}</strong>
                <p>{{ item.copy }}</p>
              </div>
            </li>
          </ul>

          <footer class="auth-hero__footer">
            <p class="auth-hint">
              ¿Necesitas ayuda?
              <a href="mailto:soporte@medireserva.com">soporte@medireserva.com</a>
            </p>
          </footer>
        </slot>
      </aside>

        <section class="auth-panel">
          <header class="auth-panel__header">
            <div>
              <p v-if="eyebrow" class="auth-panel__eyebrow">{{ eyebrow }}</p>
              <h1 class="auth-panel__title">{{ title }}</h1>
              <p v-if="subtitle" class="auth-panel__subtitle">{{ subtitle }}</p>
            </div>
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

          <div class="auth-form">
            <slot />
          </div>

          <footer v-if="$slots.footer || footerCopy" class="auth-panel__footer">
            <slot name="footer">
              <span v-if="footerCopy">{{ footerCopy }}</span>
            </slot>
          </footer>
        </section>
      </div>
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

const messageVariant = computed(() => props.contextMessage?.variant ?? 'info')
const messageTitle = computed(() => props.contextMessage?.title ?? null)
const messageBody = computed(() => props.contextMessage?.body ?? props.contextMessage?.message ?? '')
const messageList = computed(() => props.contextMessage?.list ?? null)

const messageClass = computed(() => {
  if (messageVariant.value === 'success') return 'is-success'
  if (messageVariant.value === 'danger' || messageVariant.value === 'error') return 'is-danger'
  return 'is-info'
})

const asideBullets = computed(() => [
  {
    id: 'agenda',
    icon: '📅',
    title: 'Agenda inteligente',
    copy: 'Configura horarios dinámicos y automatiza confirmaciones sin perder tu toque humano.',
  },
  {
    id: 'recordatorios',
    icon: '🔔',
    title: 'Recordatorios multicanal',
    copy: 'Envía notificaciones por correo y WhatsApp para reducir ausencias y retrasos.',
  },
  {
    id: 'insights',
    icon: '📈',
    title: 'Insights accionables',
    copy: 'Observa métricas clave de tus pacientes y actúa con datos en tiempo real.',
  },
])

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