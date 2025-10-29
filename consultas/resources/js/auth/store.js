const KEY = 'auth_token'
export const auth = {
  get token(){ return localStorage.getItem(KEY) },
  set token(v){ v ? localStorage.setItem(KEY, v) : localStorage.removeItem(KEY) },
  isAuth(){ return !!localStorage.getItem(KEY) }
}
export function authHeader(){ return auth.token ? { Authorization:`Bearer ${auth.token}` } : {} }
export function authGuardIf(to,from,next){ if(!auth.isAuth()) return next('/login'); next() }
