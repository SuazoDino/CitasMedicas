import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

function resolveStorage(type) {
  if (typeof window === 'undefined') return null
  try {
    return type === 'session' ? window.sessionStorage : window.localStorage
  } catch (err) {
    return null
  }
}

export function usePreferredDark() {
  const isDark = ref(false)

  if (typeof window === 'undefined') return isDark

  const media = window.matchMedia('(prefers-color-scheme: dark)')
  const update = () => {
    isDark.value = !!media.matches
  }

  onMounted(() => {
    update()
    media.addEventListener('change', update)
  })

  onBeforeUnmount(() => {
    media.removeEventListener('change', update)
  })

  return isDark
}

export function useToggle(target) {
  return () => {
    if (!target) return false
    target.value = !target.value
    return target.value
  }
}

export function useStorage(key, initialValue, storageType = 'local') {
  const storage = resolveStorage(storageType)
  const data = ref(initialValue)

  onMounted(() => {
    if (!storage) return
    const existing = storage.getItem(key)
    if (existing !== null) {
      try {
        data.value = JSON.parse(existing)
      } catch (err) {
        data.value = existing
      }
    } else if (initialValue !== undefined) {
      persist(initialValue)
    }
  })

  function persist(value) {
    if (!storage) return
    if (typeof value === 'string') {
      storage.setItem(key, value)
    } else {
      storage.setItem(key, JSON.stringify(value))
    }
  }

  watch(data, (value) => {
    persist(value)
  })

  return data
}

export function useDebounceFn(fn, delay = 300) {
  let timer = null
  return (...args) => {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      timer = null
      fn(...args)
    }, delay)
  }
}