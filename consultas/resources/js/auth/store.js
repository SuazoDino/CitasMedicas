const TOKEN_KEY = 'token'
const ROLES_KEY = 'roles'
const NAME_KEY = 'user_name'

const parseRoles = (value) => {
  if (!value) return []
  try {
    const parsed = JSON.parse(value)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
}
export const auth = {
  get token () {
    return localStorage.getItem(TOKEN_KEY)
  },
  set token (value) {
    if (value) localStorage.setItem(TOKEN_KEY, value)
    else localStorage.removeItem(TOKEN_KEY)
  },
  get roles () {
    return parseRoles(localStorage.getItem(ROLES_KEY))
  },
  set roles (value) {
    const roles = Array.isArray(value) ? value : []
    localStorage.setItem(ROLES_KEY, JSON.stringify(roles))
  },
  hasRole (role) {
    return this.roles.includes(role)
  },
  get name () {
    return localStorage.getItem(NAME_KEY) || ''
  },
  set name (value) {
    if (value) localStorage.setItem(NAME_KEY, value)
    else localStorage.removeItem(NAME_KEY)
  },
  clear () {
    this.token = null
    this.roles = []
    this.name = ''
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
