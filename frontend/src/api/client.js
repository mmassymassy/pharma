import axios from 'axios'

const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'

const api = axios.create({
  baseURL: `${apiUrl}/api`,
  withCredentials: true,
  withXSRFToken: true,
})

let csrfReady = false

export async function ensureCsrfCookie() {
  if (csrfReady) return
  await axios.get(`${apiUrl}/sanctum/csrf-cookie`, { withCredentials: true })
  csrfReady = true
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      csrfReady = false
    }
    return Promise.reject(error)
  },
)

export default api
