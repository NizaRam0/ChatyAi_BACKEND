// Auth service maps frontend actions to backend auth endpoints.
import { API_ROOT_URL } from '../utils/constants'
import { apiPostJson, clearToken, getToken, setToken } from './apiClient'

// Logs user in and stores returned token for authenticated API usage.
export async function loginUser({ email, password }) {
  const payload = await apiPostJson('/login', { email, password }, { baseUrl: API_ROOT_URL })

  const token = payload?.token || ''
  if (!token) {
    throw {
      status: 0,
      message: 'Login succeeded but no token was returned by backend.',
      payload,
    }
  }

  setToken(token)
  return payload
}

// Registers user and then logs in automatically to obtain API token.
export async function registerAndLogin({ name, email, password, password_confirmation }) {
  await apiPostJson(
    '/register',
    { name, email, password, password_confirmation },
    { baseUrl: API_ROOT_URL },
  )

  return loginUser({ email, password })
}

// Revokes backend token and clears local token regardless of server response.
export async function logoutUser() {
  if (!getToken()) return

  try {
    await apiPostJson('/logout', {}, { baseUrl: API_ROOT_URL })
  } finally {
    clearToken()
  }
}
