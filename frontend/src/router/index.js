import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/Login.vue'

// Admin Pages (using <AppLayout.vue> inside each component)
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import ManageUsers from '@/views/admin/ManageUsers.vue'
import AssignLecturers from '@/views/admin/AssignLecturers.vue'
import Logs from '@/views/admin/Logs.vue'
import ResetPassword from '@/views/admin/ResetPassword.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },

  // ✅ Flat admin routes using AppLayout inside each page
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard
  },
  {
    path: '/admin/users',
    name: 'ManageUsers',
    component: ManageUsers
  },
  {
    path: '/admin/assign-lecturers',
    name: 'AssignLecturers',
    component: AssignLecturers
  },
  {
    path: '/admin/logs',
    name: 'SystemLogs',
    component: Logs
  },
  {
    path: '/admin/reset',
    name: 'ResetPassword',
    component: ResetPassword
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
