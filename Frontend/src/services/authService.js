// Auth service maps frontend actions to backend auth endpoints.
import { API_ROOT_URL } from '../utils/constants'
import { apiPostJson, clearToken, getToken, setToken } from './apiClient'

const USER_ID_STORAGE_KEY = 'chatyai_user_id'

function extractUserId(payload) {
  return payload?.user?.id || payload?.user?.data?.id || payload?.data?.id || ''
}

function persistUserId(userId) {
  if (!userId) return
  localStorage.setItem(USER_ID_STORAGE_KEY, String(userId))
}

export function getCurrentUserId() {
  return localStorage.getItem(USER_ID_STORAGE_KEY) || ''
}

function clearCurrentUserId() {
  localStorage.removeItem(USER_ID_STORAGE_KEY)
}

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
  persistUserId(extractUserId(payload))
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
  if (!getToken()) {
    clearCurrentUserId()
    return
  }

  try {
    await apiPostJson('/logout', {}, { baseUrl: API_ROOT_URL })
  } finally {
    clearCurrentUserId()
    clearToken()
  }
}

// Requests a password reset link email for the provided user email.
export async function requestPasswordReset(email) {
  return apiPostJson('/forgot-password', { email }, { baseUrl: API_ROOT_URL })
}

// Completes password reset using token from email link and new password fields.
export async function resetPassword({ token, email, password, password_confirmation }) {
  return apiPostJson(
    '/reset-password',
    { token, email, password, password_confirmation },
    { baseUrl: API_ROOT_URL },
  )
}
