// Router configuration for main user flows: upload page and history page.
import { createRouter, createWebHistory } from 'vue-router'
import UploadPage from '../pages/UploadPage.vue'
import HistoryPage from '../pages/HistoryPage.vue'
import LoginPage from '../pages/LoginPage.vue'
import RegisterPage from '../pages/RegisterPage.vue'
import { getToken } from '../services/apiClient'

// Route table kept intentionally small to focus the user on image generation features.
const routes = [
  {
    path: '/',
    name: 'upload',
    component: UploadPage,
    meta: { requiresAuth: true },
  },
  {
    path: '/history',
    name: 'history',
    component: HistoryPage,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Route guard keeps upload/history private and redirects unauthenticated users to login.
router.beforeEach((to) => {
  const hasToken = Boolean(getToken())

  if (to.meta.requiresAuth && !hasToken) {
    return { name: 'login', query: { next: to.fullPath } }
  }

  if ((to.name === 'login' || to.name === 'register') && hasToken) {
    return { name: 'upload' }
  }

  return true
})

export default router
