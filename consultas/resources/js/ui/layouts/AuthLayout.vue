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
            <div class="auth-hero__intro">
              <span class="auth-hero__badge">{{ eyebrow }}</span>
              <h2 class="auth-hero__headline">Gestiona tu consulta sin complicaciones</h2>
              <p class="auth-hero__lead">
                Coordina agendas, recordatorios y seguimiento clínico dentro de una misma plataforma. Diseño y
                usabilidad alineados con la experiencia principal de MediReserva.
              </p>
            </div>

            <div class="auth-hero__grid">
              <article
                v-for="card in showcaseCards"
                :key="card.id"
                class="auth-hero-card"
                :class="{ 'is-accent': card.accent }"
              >
                <header class="auth-hero-card__header">
                  <span class="auth-hero-card__eyebrow">{{ card.eyebrow }}</span>
                  <h3 class="auth-hero-card__title">{{ card.title }}</h3>
                </header>
                <p class="auth-hero-card__copy">{{ card.copy }}</p>
                <ul class="auth-hero-card__list">
                  <li v-for="item in card.items" :key="item" class="auth-hero-card__item">
                    <span aria-hidden="true">✔</span>
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </article>
            </div>

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

const showcaseCards = computed(() => [
  {
    id: 'experience',
    eyebrow: 'MediReserva Pro',
    title: 'Una experiencia pensada para tu consulta',
    copy: 'Automatiza confirmaciones, sincroniza calendarios y mantén el control de tu agenda desde un panel intuitivo.',
    items: [
      'Agenda médica unificada con disponibilidad en tiempo real',
      'Recordatorios confirmados por correo y WhatsApp',
      'Seguimiento a pacientes y métricas clave para tu consultorio',
    ],
  },
  {
    id: 'patients',
    eyebrow: 'Pacientes',
    title: 'Tu salud en un mismo lugar',
    copy: 'Consulta historial, reprograma citas y recibe notificaciones personalizadas desde cualquier dispositivo.',
    items: [
      'Reservas con especialistas verificados en pocos pasos',
      'Expedientes compartidos con tus médicos de confianza',
      'Soporte humano cuando lo necesites',
    ],
    accent: true,
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