import axios from 'axios'
const api = axios.create({ baseURL: '/api' })
const t = localStorage.getItem('token') || localStorage.getItem('auth_token')
if (t) api.defaults.headers.common.Authorization = `Bearer ${t}`
export default api
