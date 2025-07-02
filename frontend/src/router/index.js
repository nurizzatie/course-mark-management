import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue';
import StudentDashboard from '@/views/student/StudentDashboard.vue';
import LecturerDashboard from '@/views/lecturer/LecturerDashboard.vue';
import AdvisorDashboard from '@/views/advisor/AdvisorDashboard.vue';
import AdminDashboard from '@/views/admin/AdminDashboard.vue';
import LecturerProfile from '@/views/lecturer/LecturerProfile.vue';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/student/dashboard', component: StudentDashboard },
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/advisor/dashboard', component: AdvisorDashboard },
  { path: '/admin/dashboard', component: AdminDashboard },
  { path: '/lecturer/profile', component: LecturerProfile }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
