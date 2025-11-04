function isEmpty(value) {
  if (value === null || value === undefined) return true
  if (typeof value === 'string') return value.trim().length === 0
  if (Array.isArray(value)) return value.length === 0
  return false
}

export function required(value) {
  return isEmpty(value) ? 'Este campo es obligatorio' : true
}

export function email(value) {
  if (isEmpty(value)) return true
  const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return pattern.test(String(value)) ? true : 'Introduce un correo válido'
}

export function min(value, [length = 0]) {
  if (isEmpty(value)) return true
  const limit = Number(length)
  if (Number.isNaN(limit)) return true
  return String(value).length >= limit ? true : `Debe tener al menos ${limit} caracteres`
}

export function max(value, [length = 0]) {
  if (isEmpty(value)) return true
  const limit = Number(length)
  if (Number.isNaN(limit)) return true
  return String(value).length <= limit ? true : `Debe tener máximo ${limit} caracteres`
}

export function confirmed(value, [target], ctx) {
  if (!target) return true
  const other = ctx?.form?.[target]
  return value === other ? true : 'Las contraseñas no coinciden'
}

export function one_of(value, params = []) {
  if (!params.length) return true
  return params.includes(String(value)) ? true : 'Selecciona una opción válida'
}

export function regex(value, [pattern, flags = '']) {
  if (isEmpty(value)) return true
  try {
    const exp = new RegExp(pattern, flags)
    return exp.test(String(value)) ? true : 'Formato inválido'
  } catch (err) {
    return true
  }
}