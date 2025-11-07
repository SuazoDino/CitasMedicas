const TOKEN_KEY = 'token'
const ROLES_KEY = 'roles'
const NAME_KEY = 'user_name'

const STORAGE_MODE_KEY = 'auth_storage_mode'

const hasWindow = typeof window !== 'undefined'

const storages = () => {
  if (!hasWindow) return []
  return [window.sessionStorage, window.localStorage]
}

const readValue = (key) => {
  if (!hasWindow) return null
  for (const storage of storages()) {
    try {
      const value = storage.getItem(key)
      if (value != null) return value
    } catch {
      // ignore storage access errors (e.g. private mode)
    }
  }
  return null
}

const setMode = (mode) => {
  if (!hasWindow) return
  const normalized = mode === 'session' ? 'session' : 'local'
  if (normalized === 'session') {
    try {
      window.sessionStorage.setItem(STORAGE_MODE_KEY, normalized)
      window.localStorage.removeItem(STORAGE_MODE_KEY)
    } catch {
      // ignore storage access errors
    }
  } else {
    try {
      window.localStorage.setItem(STORAGE_MODE_KEY, normalized)
      window.sessionStorage.removeItem(STORAGE_MODE_KEY)
    } catch {
      // ignore storage access errors
    }
  }
}

const getMode = () => {
  const stored = readValue(STORAGE_MODE_KEY)
  return stored === 'session' ? 'session' : 'local'
}

const writeValue = (key, value, mode = getMode()) => {
  if (!hasWindow) return
  const normalized = mode === 'session' ? 'session' : 'local'
  const target = normalized === 'session' ? window.sessionStorage : window.localStorage
  const other = normalized === 'session' ? window.localStorage : window.sessionStorage

  try {
    if (value == null) target.removeItem(key)
    else target.setItem(key, value)
  } catch {
    // ignore storage access errors
  }

  try {
    other.removeItem(key)
  } catch {
    // ignore storage access errors
  }
}

const parseRoles = (value) => {
  if (!value) return []
  try {
    const parsed = JSON.parse(value)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
}

const stringifyRoles = (value) => {
  try {
    return JSON.stringify(Array.isArray(value) ? value : [])
  } catch {
    return '[]'
  }
}

export const auth = {
  get token () {
    return readValue(TOKEN_KEY)
  },
  set token (value) {
    if (value) writeValue(TOKEN_KEY, value)
    else writeValue(TOKEN_KEY, null)
  },
  get roles () {
    return parseRoles(readValue(ROLES_KEY))
  },
  set roles (value) {
    writeValue(ROLES_KEY, stringifyRoles(value))
  },
  hasRole (role) {
    return this.roles.includes(role)
  },
  get name () {
    return readValue(NAME_KEY) || ''
  },
  set name (value) {
    if (value) writeValue(NAME_KEY, String(value))
    else writeValue(NAME_KEY, null)
  },
  persistSession ({ token, roles, name, remember }) {
    const mode = remember ? 'local' : 'session'
    setMode(mode)
    writeValue(TOKEN_KEY, token ?? null, mode)
    writeValue(ROLES_KEY, stringifyRoles(roles), mode)
    writeValue(NAME_KEY, name ? String(name) : null, mode)
  },
  clear () {
    writeValue(TOKEN_KEY, null, 'local')
    writeValue(TOKEN_KEY, null, 'session')
    writeValue(ROLES_KEY, null, 'local')
    writeValue(ROLES_KEY, null, 'session')
    writeValue(NAME_KEY, null, 'local')
    writeValue(NAME_KEY, null, 'session')
    setMode('local')
  },
  isAuth () {
    return !!this.token
  }
}

export function authHeader () {
  return auth.token ? { Authorization: `Bearer ${auth.token}` } : {}
}

export function authGuardIf (to, from, next) {
  if (!auth.isAuth()) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }
  next()
}
