// Service layer for user profile management.
import { apiGet, buildHeaders, buildApiUrl, parseError } from './apiClient'

// Fetches current authenticated user profile data.
export async function fetchCurrentUser(userId) {
  const response = await apiGet(`/user/${userId}`)
  return response?.data || response
}

// Updates user profile (name, password, etc).
export async function updateUserProfile(userId, profileData) {
  const url = buildApiUrl(`/user/${userId}`)

  const response = await fetch(url, {
    method: 'PATCH',
    headers: buildHeaders({
      'Content-Type': 'application/json',
    }),
    body: JSON.stringify(profileData),
  })

  if (!response.ok) {
    throw await parseError(response)
  }

  const contentType = response.headers.get('content-type') || ''
  const data = contentType.includes('application/json') 
    ? await response.json().catch(() => ({}))
    : {}

  return data?.data || data
}

// Deletes user account permanently.
export async function deleteUserAccount(userId) {
  const url = buildApiUrl(`/user/${userId}`)

  const response = await fetch(url, {
    method: 'DELETE',
    headers: buildHeaders({
      'Content-Type': 'application/json',
    }),
  })

  if (!response.ok) {
    throw await parseError(response)
  }

  return { success: true }
}
