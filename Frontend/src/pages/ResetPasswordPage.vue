<script setup>
// Reset password page consumes email-link token and submits new password to backend.
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import InlineAlert from '../components/InlineAlert.vue'
import { resetPassword } from '../services/authService'

const route = useRoute()
const router = useRouter()

const form = reactive({
  email: String(route.query.email || ''),
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Reads token from URL segment: /password-reset/:token
const token = computed(() => String(route.params.token || ''))

// Submits reset form with token + email + new password.
async function submitResetPassword() {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await resetPassword({
      token: token.value,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    successMessage.value = 'Password updated successfully. Redirecting to login...'
    window.setTimeout(async () => {
      await router.push('/login')
    }, 1000)
  } catch (error) {
    errorMessage.value = error?.message || 'Password reset failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="auth-page">
    <article class="auth-card glass-card">
      <h2 class="section-title">Reset Password</h2>
      <p class="muted">Create a new password for your account.</p>

      <InlineAlert v-if="errorMessage" type="error" :message="errorMessage" />
      <InlineAlert v-if="successMessage" type="success" :message="successMessage" />

      <form class="auth-form" @submit.prevent="submitResetPassword">
        <div class="field">
          <label for="reset-email">Email</label>
          <input id="reset-email" v-model="form.email" class="input" type="email" required />
        </div>

        <div class="field">
          <label for="new-password">New Password</label>
          <input id="new-password" v-model="form.password" class="input" type="password" required />
        </div>

        <div class="field">
          <label for="confirm-password">Confirm New Password</label>
          <input
            id="confirm-password"
            v-model="form.password_confirmation"
            class="input"
            type="password"
            required
          />
        </div>

        <button class="btn btn-primary" type="submit" :disabled="loading || !token">
          {{ loading ? 'Updating password...' : 'Update Password' }}
        </button>
      </form>

      <p class="switch-link">
        Back to
        <RouterLink to="/login">Login</RouterLink>
      </p>
    </article>
  </section>
</template>

<style scoped>
.auth-page {
  display: grid;
  place-items: center;
  min-height: calc(100vh - 190px);
}

.auth-card {
  width: min(520px, 100%);
  padding: 1.2rem;
}

.auth-form {
  margin-top: 1rem;
  display: grid;
  gap: 0.8rem;
}

.field label {
  display: block;
  margin-bottom: 0.3rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.switch-link {
  margin: 0.9rem 0 0;
  color: var(--text-secondary);
}

.switch-link a {
  color: var(--accent);
  font-weight: 700;
}
</style>
