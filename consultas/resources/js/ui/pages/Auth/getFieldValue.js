import { unref } from 'vue'

export function getFieldValue (field) {
  if (!field) return ''

  const candidate = typeof field === 'object' && field !== null && 'value' in field ? field.value : field
  const resolved = unref(candidate)

  if (resolved == null) return ''

  return typeof resolved === 'string' ? resolved : String(resolved)
}