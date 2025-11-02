import axios from 'axios'
const api = axios.create({ baseURL: '/api', withCredentials: true })

const resolveToken = () =>
  localStorage.getItem('token') ||
  localStorage.getItem('auth_token') ||
  localStorage.getItem('access_token') ||
  sessionStorage.getItem('token') ||
  null

api.interceptors.request.use((config) => {
  const token = resolveToken()
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api
