import { reactive, ref, computed, watch, provide, inject, onBeforeUnmount } from 'vue'

const FormContextSymbol = Symbol('mini-vee-validate:form')
const globalConfig = reactive({
  validateOnInput: true,
  validateOnBlur: true,
  generateMessage: (ctx) => {
    if (!ctx?.field) return 'Campo no válido'
    return `${ctx.field} no es válido`
  },
})

const definedRules = new Map()

export function configure(config) {
  if (!config) return
  Object.assign(globalConfig, config)
}

export function defineRule(name, validator) {
  if (typeof name !== 'string' || typeof validator !== 'function') return
  definedRules.set(name, validator)
}

function resolveRule(name) {
  return definedRules.get(name)
}

function normalizeRules(rules) {
  if (!rules) return []
  if (typeof rules === 'function') return [rules]
  if (Array.isArray(rules)) return rules.flatMap((rule) => normalizeRules(rule))
  if (typeof rules === 'string') {
    return rules
      .split('|')
      .map((token) => token.trim())
      .filter(Boolean)
      .map((token) => {
        const [ruleName, paramsRaw] = token.split(':')
        const params = paramsRaw ? paramsRaw.split(',') : []
        return async (value, ctx) => {
          const rule = resolveRule(ruleName)
          if (!rule) return true
          const result = await rule(value, params, ctx)
          if (result === true || result === undefined) return true
          if (typeof result === 'string') return result
          return globalConfig.generateMessage?.({ ...ctx, rule: ruleName }) ?? 'Valor inválido'
        }
      })
  }
  return []
}

function createMetaState() {
  return reactive({
    touched: false,
    dirty: false,
    valid: true,
  })
}

export function useForm(options = {}) {
  const opts = {
    validateOnInput: options.validateOnInput ?? globalConfig.validateOnInput,
    validateOnBlur: options.validateOnBlur ?? globalConfig.validateOnBlur,
    initialValues: options.initialValues ? { ...options.initialValues } : {},
  }

  const values = reactive({ ...opts.initialValues })
  const errors = reactive({})
  const fields = new Map()
  const submitCount = ref(0)
  const pending = ref(false)

  const formMeta = computed(() => {
    let dirty = false
    let touched = false
    let valid = true
    fields.forEach((field) => {
      dirty = dirty || field.meta.dirty
      touched = touched || field.meta.touched
      valid = valid && field.meta.valid
    })
    if (Object.keys(errors).length) valid = false
    return {
      dirty,
      touched,
      valid,
      pending: pending.value,
      submitCount: submitCount.value,
    }
  })

  function registerField(name, fieldState) {
    fields.set(name, fieldState)
  }

  function unregisterField(name) {
    fields.delete(name)
    delete errors[name]
  }

  async function validateField(name) {
    const field = fields.get(name)
    if (!field) return { valid: true, errors: [] }
    const ctx = { field: name, value: field.value.value, form: values, meta: field.meta }
    let message = ''
    for (const rule of field.rules) {
      const outcome = await rule(field.value.value, ctx)
      if (outcome !== true) {
        message = typeof outcome === 'string' ? outcome : globalConfig.generateMessage?.(ctx) ?? 'Valor inválido'
        break
      }
    }
    if (message) {
      errors[name] = message
      field.meta.valid = false
    } else {
      delete errors[name]
      field.meta.valid = true
    }
    return { valid: !message, errors: message ? [message] : [] }
  }

  async function validate() {
    const results = await Promise.all(
      Array.from(fields.keys()).map(async (name) => {
        const res = await validateField(name)
        return { name, ...res }
      })
    )
    const failed = results.filter((result) => !result.valid)
    return { valid: failed.length === 0, results }
  }

  function setFieldError(name, message) {
    if (message) errors[name] = message
    else delete errors[name]
    const field = fields.get(name)
    if (field) field.meta.valid = !message
  }

  function setErrors(nextErrors) {
    Object.keys(errors).forEach((key) => delete errors[key])
    if (nextErrors && typeof nextErrors === 'object') {
      Object.entries(nextErrors).forEach(([key, value]) => {
        const message = Array.isArray(value) ? value[0] : value
        if (message) errors[key] = message
        const field = fields.get(key)
        if (field) field.meta.valid = !message
      })
    }
  }

  function resetForm(nextValues = {}) {
    const base = Object.keys(nextValues).length ? nextValues : opts.initialValues
    Object.keys(values).forEach((key) => delete values[key])
    Object.entries(base).forEach(([key, value]) => {
      values[key] = value
    })
    fields.forEach((field, name) => {
      field.meta.dirty = false
      field.meta.touched = false
      field.meta.valid = true
      if (name in values) {
        field.value.value = values[name]
      } else {
        field.value.value = ''
        values[name] = ''
      }
    })
    Object.keys(errors).forEach((key) => delete errors[key])
  }

  function setFieldValue(name, value) {
    values[name] = value
  }

  function handleSubmit(fn) {
    return async (evt) => {
      if (evt?.preventDefault) evt.preventDefault()
      submitCount.value += 1
      pending.value = true
      try {
        const result = await validate()
        if (!result.valid) return
        await fn({ ...values }, { resetForm, setErrors, setFieldError })
      } finally {
        pending.value = false
      }
    }
  }

  provide(FormContextSymbol, {
    options: opts,
    values,
    errors,
    registerField,
    unregisterField,
    validateField,
    setFieldError,
    setFieldValue,
  })

  return {
    values,
    errors,
    meta: formMeta,
    isSubmitting: computed(() => pending.value),
    handleSubmit,
    validate,
    resetForm,
    setErrors,
    setFieldError,
  }
}

export function useField(name, rules, options = {}) {
  const form = inject(FormContextSymbol, null)
  const value = ref(options.initialValue ?? form?.values?.[name] ?? '')
  const meta = createMetaState()
  const ruleFns = normalizeRules(rules)

  if (form) {
    if (!(name in form.values)) form.values[name] = value.value
    form.registerField(name, { value, meta, rules: ruleFns })
  }

  const errorMessage = computed(() => (form ? form.errors[name] || '' : ''))

  watch(
    value,
    async (val, oldVal) => {
      if (val === oldVal) return
      meta.dirty = true
      if (form) form.setFieldValue(name, val)
      if ((options.validateOnInput ?? form?.options?.validateOnInput) !== false) {
        await form?.validateField?.(name)
      }
    },
    { flush: 'post' }
  )

  async function handleBlur() {
    meta.touched = true
    if ((options.validateOnBlur ?? form?.options?.validateOnBlur) !== false) {
      await form?.validateField?.(name)
    }
  }

  async function validateField() {
    if (!form) return { valid: true, errors: [] }
    return await form.validateField(name)
  }

  onBeforeUnmount(() => {
    form?.unregisterField?.(name)
  })

  return {
    value,
    errorMessage,
    meta,
    validate: validateField,
    handleBlur,
    setValue: (val) => {
      value.value = val
    },
  }
}

export function useFieldModel(field) {
  return {
    get value() {
      return field.value.value
    },
    set value(val) {
      field.value.value = val
    },
  }
}