<template>
  <div class="auth-view" :data-variant="resolvedTheme">
    <div class="auth-view__pattern" aria-hidden="true"></div>

    <div class="auth-view__container">
      <header class="auth-brand">
        <slot name="brand">
          <div>
            <span class="auth-brand__logo">MediReserva</span>
            <p class="auth-brand__tagline">
              Coordina tus citas con una plataforma hecha para consultorios modernos.
            </p>
          </div>
        </slot>
      </header>

      <div :class="layoutClasses">
        <section class="auth-card">
          <header class="auth-card__header">
            <p v-if="eyebrow" class="auth-card__eyebrow">{{ eyebrow }}</p>
            <h1 class="auth-card__title">{{ title }}</h1>
            <p v-if="subtitle" class="auth-card__subtitle">{{ subtitle }}</p>
          </header>

          <div v-if="messageBody" class="auth-card__notice" :class="messageClass">
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

          <footer v-if="$slots.footer || footerCopy" class="auth-card__footer">
            <slot name="footer">
              <span v-if="footerCopy">{{ footerCopy }}</span>
            </slot>
          </footer>
        </section>

        <aside v-if="showAside" class="auth-support">
          <slot name="aside">
            <div class="auth-support__panel">
              <p class="auth-support__title">¿Necesitas ayuda?</p>
              <ul class="auth-support__list">
                <li>Contacta a soporte@medireserva.com</li>
                <li>Recupera tu acceso desde “Recuperar contraseña”.</li>
                <li>Administra tus recordatorios en cuestión de minutos.</li>
              </ul>
              <a class="auth-support__cta" href="mailto:soporte@medireserva.com">Escríbenos cuando lo necesites →</a>
            </div>
          </slot>
        </aside>
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

const layoutClasses = computed(() => {
  const classes = ['auth-layout']
  if (props.showAside) {
    classes.push('auth-layout--with-aside')
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
