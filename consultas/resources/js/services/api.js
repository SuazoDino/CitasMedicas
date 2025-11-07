import axios from 'axios'
import { auth } from '../auth/store'

const api = axios.create({ baseURL: '/api', withCredentials: true })

api.interceptors.request.use((config) => {
  const token = auth.token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api
